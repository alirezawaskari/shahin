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
    public const TOKEN_FACTOR_ID       = '{شناسه فاکتور}';
    public const TOKEN_PAYMENT_LINK    = '{لینک پرداخت}';
    public const TOKEN_REQUEST_ID      = '{شناسه درخواست}';

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

        // {شناسه فاکتور} - Factor ID / Reference number
        $this->register(self::TOKEN_FACTOR_ID, function (array $context): string {
            $factor = $this->extractFactor($context);
            if ($factor) {
                return (string) ($factor->id ?? $factor->resnumber ?? '');
            }

            return (string) ($context['factor_id'] ?? $context['factor_number'] ?? '');
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

        // {شناسه درخواست} - Request ID / Tracking ID
        $this->register(self::TOKEN_REQUEST_ID, function (array $context): string {
            $request = $this->extractRequest($context);
            if ($request) {
                return (string) ($request->id ?? $request->card_id ?? '');
            }

            return (string) ($context['request_id'] ?? '');
        });
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
