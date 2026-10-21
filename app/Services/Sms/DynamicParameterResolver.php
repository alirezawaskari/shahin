<?php

namespace App\Services\Sms;

use App\Models\Factor;
use App\Models\Request;
use App\Models\User;
use Closure;
use Hekmatinasser\Verta\Verta;

/**
 * Resolves named dynamic placeholders inside SMS templates, replacing tokens
 * such as {نام کاربر}, {نام خانوادگی}, {تاریخ}, {ساعت}, {شناسه فاکتور}, {لینک پرداخت},
 * and {شناسه درخواست} with contextual values from User, Factor, Request, etc.
 *
 * Designed with a pluggable registry allowing Wave 2 workers to register additional
 * custom parameter types at runtime or application boot.
 */
class DynamicParameterResolver
{
    /**
     * Standard seeded placeholders supported out-of-the-box.
     */
    public const TOKEN_USER_FIRST_NAME = '{نام کاربر}';
    public const TOKEN_USER_LAST_NAME  = '{نام خانوادگی}';
    public const TOKEN_DATE            = '{تاریخ}';
    public const TOKEN_TIME            = '{ساعت}';
    /** Canonical invoice token. */
    public const TOKEN_FACTOR_NUMBER   = '{شماره فاکتور}';
    /** @deprecated Old name, still resolved so saved templates keep working. Use TOKEN_FACTOR_NUMBER. */
    public const TOKEN_FACTOR_ID       = '{شناسه فاکتور}';
    public const TOKEN_PAYMENT_LINK    = '{لینک پرداخت}';
    /** @deprecated Merged into {شماره فاکتور}, still resolved so saved templates keep working. */
    public const TOKEN_REQUEST_ID      = '{شناسه درخواست}';
    public const TOKEN_SITE_LINK       = '{لینک سایت}';
    public const TOKEN_REQUEST_SUBJECT = '{موضوع درخواست}';
    public const TOKEN_BIRTH_DATE      = '{تاریخ تولد}';
    public const TOKEN_RECIPIENT_PHONE = '{شماره مخاطب}';
    public const TOKEN_BUSINESS_NAME   = '{نام فروشگاه}';

    /**
     * Pluggable registry of parameter resolvers: [placeholder => callable].
     *
     * @var array<string, callable>
     */
    protected array $resolvers = [];

    /**
     * Global/static resolvers registered across instances.
     *
     * @var array<string, callable>
     */
    protected static array $globalResolvers = [];

    public function __construct()
    {
        $this->registerDefaultResolvers();
    }

    /**
     * Register a new parameter resolver globally for all instances.
     */
    public static function registerGlobal(string $placeholder, callable $resolver): void
    {
        $normalized = self::normalizeToken($placeholder);
        self::$globalResolvers[$normalized] = $resolver;
    }

    /**
     * Register a parameter resolver on this instance.
     */
    public function register(string $placeholder, callable $resolver): self
    {
        $normalized = self::normalizeToken($placeholder);
        $this->resolvers[$normalized] = $resolver;

        return $this;
    }

    /**
     * Replace all matching named placeholders in the template content using the given context.
     *
     * @param string $content Template text containing {placeholder} tokens
     * @param array<string, mixed> $context Associative array with keys like 'user', 'factor', 'request', etc.
     */
    public function applyTo(string $content, array $context = []): string
    {
        if (!str_contains($content, '{')) {
            return $content;
        }

        $allResolvers = array_merge($this->resolvers, self::$globalResolvers);

        foreach ($allResolvers as $token => $resolver) {
            if (!str_contains($content, $token)) {
                continue;
            }

            $resolvedValue = (string) $resolver($context);
            $content = str_replace($token, $resolvedValue, $content);
        }

        return $content;
    }

    /**
     * Resolve a single placeholder against a given context.
     */
    public function resolve(string $placeholder, array $context = []): ?string
    {
        $token = self::normalizeToken($placeholder);
        $allResolvers = array_merge($this->resolvers, self::$globalResolvers);

        if (!isset($allResolvers[$token])) {
            return null;
        }

        return (string) $allResolvers[$token]($context);
    }

    /**
     * Return list of all currently supported placeholder tokens.
     *
     * @return array<string>
     */
    public function getSupportedPlaceholders(): array
    {
        return array_keys(array_merge($this->resolvers, self::$globalResolvers));
    }

    /**
     * Register the 7 foundational seeded parameter resolvers.
     */
    protected function registerDefaultResolvers(): void
    {
        // {نام کاربر} - User first name
        $this->register(self::TOKEN_USER_FIRST_NAME, function (array $context): string {
            $user = $this->extractUser($context);
            if ($user) {
                return (string) ($user->name ?? '');
            }

            return (string) ($context['first_name'] ?? $context['name'] ?? '');
        });

        // {نام خانوادگی} - User last name / family
        $this->register(self::TOKEN_USER_LAST_NAME, function (array $context): string {
            $user = $this->extractUser($context);
            if ($user) {
                return (string) ($user->family ?? '');
            }

            return (string) ($context['family'] ?? $context['last_name'] ?? '');
        });

        // {تاریخ} - Current / contextual Jalali (Solar) date
        $this->register(self::TOKEN_DATE, function (array $context): string {
            if (!empty($context['date'])) {
                return (string) $context['date'];
            }

            if (class_exists(Verta::class)) {
                return Verta::now()->format('Y/m/d');
            }

            return date('Y/m/d');
        });

        // {ساعت} - Current / contextual time
        $this->register(self::TOKEN_TIME, function (array $context): string {
            if (!empty($context['time'])) {
                return (string) $context['time'];
            }

            if (class_exists(Verta::class)) {
                return Verta::now()->format('H:i');
            }

            return date('H:i');
        });

        // {شماره فاکتور} - Invoice number. {شناسه فاکتور} and {شناسه درخواست} are old names of the same value.
        $factorNumber = function (array $context): string {
            $factor = $this->extractFactor($context);
            if ($factor) {
                return (string) ($factor->id ?? $factor->resnumber ?? '');
            }

            if (!empty($context['factor_id'] ?? $context['factor_number'] ?? null)) {
                return (string) ($context['factor_id'] ?? $context['factor_number']);
            }

            $request = $this->extractRequest($context);
            if ($request) {
                return (string) ($request->id ?? $request->card_id ?? '');
            }

            return (string) ($context['request_id'] ?? '');
        };
        $this->register(self::TOKEN_FACTOR_NUMBER, $factorNumber);
        $this->register(self::TOKEN_FACTOR_ID, $factorNumber);
        $this->register(self::TOKEN_REQUEST_ID, $factorNumber);

        // {لینک سایت} - Storefront link of the sending business
        $this->register(self::TOKEN_SITE_LINK, function (array $context): string {
            if (!empty($context['site_link'])) {
                return (string) $context['site_link'];
            }

            $business = function_exists('ghorfe') ? ghorfe() : null;

            return $business ? url($business->path()) : url('/');
        });

        // {موضوع درخواست} - Subject of the request the message is about
        $this->register(self::TOKEN_REQUEST_SUBJECT, function (array $context): string {
            if (!empty($context['request_subject'])) {
                return (string) $context['request_subject'];
            }

            $request = $this->extractRequest($context);

            return $request ? \Illuminate\Support\Str::limit((string) ($request->description ?? ''), 40, '') : '';
        });

        // {لینک پرداخت} - Payment link for factor
        $this->register(self::TOKEN_PAYMENT_LINK, function (array $context): string {
            if (!empty($context['payment_link'])) {
                return (string) $context['payment_link'];
            }

            $factor = $this->extractFactor($context);
            if ($factor) {
                $ref = $factor->resnumber ?: $factor->id;
                if ($ref) {
                    return 'lalecard.ir/pf/' . $ref;
                }
            }

            return '';
        });

        // {تاریخ تولد} - Recipient birth date
        $this->register(self::TOKEN_BIRTH_DATE, function (array $context): string {
            if (!empty($context['birth_date'])) {
                return (string) $context['birth_date'];
            }

            $user = $this->extractUser($context);
            if ($user) {
                if (!empty($user->birth_year) && !empty($user->birth_month) && !empty($user->birth_day)) {
                    return sprintf('%04d/%02d/%02d', (int) $user->birth_year, (int) $user->birth_month, (int) $user->birth_day);
                }
                if (!empty($user->birth_month) && !empty($user->birth_day)) {
                    return sprintf('%02d/%02d', (int) $user->birth_month, (int) $user->birth_day);
                }
            }

            return '';
        });

        // {روز تولد} & {ماه تولد}
        $this->register('{روز تولد}', function (array $context): string {
            $user = $this->extractUser($context);
            return (string) ($context['birth_day'] ?? $user?->birth_day ?? '');
        });
        $this->register('{ماه تولد}', function (array $context): string {
            $user = $this->extractUser($context);
            return (string) ($context['birth_month'] ?? $user?->birth_month ?? '');
        });

        // {شماره مخاطب} - Explicit recipient phone (never sender/manager)
        $this->register(self::TOKEN_RECIPIENT_PHONE, function (array $context): string {
            $user = $this->extractUser($context);
            return (string) ($context['recipient_phone'] ?? $context['phone'] ?? $user?->phone ?? '');
        });

        // {نام فروشگاه} & {نام کسب‌وکار}
        $this->register(self::TOKEN_BUSINESS_NAME, fn($ctx) => (string) ($ctx['company_name'] ?? $ctx['business_name'] ?? ghorfe()?->title ?? ''));
        $this->register('{نام کسب‌وکار}', fn($ctx) => (string) ($ctx['company_name'] ?? $ctx['business_name'] ?? ghorfe()?->title ?? ''));

        // Common token aliases for maximum usability
        $this->register('{نام}', fn($ctx) => (string) ($this->extractUser($ctx)?->name ?? $ctx['first_name'] ?? $ctx['name'] ?? ''));
        $this->register('{first_name}', fn($ctx) => (string) ($this->extractUser($ctx)?->name ?? $ctx['first_name'] ?? $ctx['name'] ?? ''));
        $this->register('{last_name}', fn($ctx) => (string) ($this->extractUser($ctx)?->family ?? $ctx['family'] ?? $ctx['last_name'] ?? ''));
        $this->register('{موبایل مخاطب}', fn($ctx) => (string) ($context['recipient_phone'] ?? $this->extractUser($ctx)?->phone ?? $ctx['phone'] ?? ''));
        $this->register('{company_name}', fn($ctx) => (string) ($ctx['company_name'] ?? ghorfe()?->title ?? ''));
        $this->register('{نام شرکت}', fn($ctx) => (string) ($ctx['company_name'] ?? ghorfe()?->title ?? ''));
        $this->register('{کد}', fn($ctx) => (string) ($ctx['code'] ?? $ctx['otp'] ?? ''));
        $this->register('{code}', fn($ctx) => (string) ($ctx['code'] ?? $ctx['otp'] ?? ''));
    }

    /**
     * Extract User model from context.
     */
    protected function extractUser(array $context): ?User
    {
        if (isset($context['user']) && $context['user'] instanceof User) {
            return $context['user'];
        }

        return null;
    }

    /**
     * Extract Factor model from context.
     */
    protected function extractFactor(array $context): ?Factor
    {
        if (isset($context['factor']) && $context['factor'] instanceof Factor) {
            return $context['factor'];
        }

        return null;
    }

    /**
     * Extract Request model from context.
     */
    protected function extractRequest(array $context): ?Request
    {
        if (isset($context['request']) && $context['request'] instanceof Request) {
            return $context['request'];
        }

        return null;
    }

    /**
     * Ensure a token format is wrapped in curly braces: {name}
     */
    protected static function normalizeToken(string $token): string
    {
        $trimmed = trim($token);
        if (!str_starts_with($trimmed, '{')) {
            $trimmed = '{' . $trimmed;
        }
        if (!str_ends_with($trimmed, '}')) {
            $trimmed = $trimmed . '}';
        }

        return $trimmed;
    }
}
