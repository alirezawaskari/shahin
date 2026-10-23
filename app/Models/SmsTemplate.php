<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    public const TYPE_OTP = 'otp';

    public const TYPE_ADVERTISING = 'advertising';

    /**
     * sms_templates has no service_type / otp_category columns: the type is derived from the text.
     * A template whose content carries one of the advertising opt-out markers is advertising, otherwise
     * a template that carries one of the OTP keywords (name, Persian name or content) is OTP.
     * classify(), the accessors and the scopes below all read these two lists, so they cannot drift apart.
     */
    public const OTP_KEYWORDS = ['رمز', 'کد تایید', 'کد تأیید', 'otp', 'password', 'login', 'verification', 'token'];

    public const ADVERTISING_MARKERS = ['لغو11', 'لغو 11', 'لغو۱۱', 'لغو ۱۱'];

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ghorfe_online_list_id',
        'name',
        'title',
        'fa_name',
        'content',
        'service_type',
        'otp_category',
        'group',
        'status',
        'created_by',
        'updated_by',
    ];
    protected $guarded = [];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            protected const IGNORED_COLUMNS = [
                'service_type',
                'otp_category',
                'group',
                'is_shared',
                'category',
                'business_id',
            ];

            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column)) {
                    $cleanCol = str_replace('sms_templates.', '', $column);
                    if ($cleanCol === 'title') {
                        $column = 'name';
                    } elseif ($cleanCol === 'is_active') {
                        $column = 'status';
                    } elseif ($cleanCol === 'ghorfe_online_list_id') {
                        $val = $operator !== null && $value === null ? $operator : $value;
                        if ($val) {
                            $bizUser = \App\Models\GhorfeOnlineList::whereKey($val)->value('user_id');
                            if ($bizUser) {
                                return parent::where(function ($q) use ($bizUser) {
                                    $q->where('user_id', $bizUser)->orWhereNull('user_id');
                                }, null, null, $boolean);
                            }
                        }
                        return $this;
                    } elseif (in_array($cleanCol, self::IGNORED_COLUMNS, true)) {
                        return $this;
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if (is_string($column)) {
                    $cleanCol = str_replace('sms_templates.', '', $column);
                    if ($cleanCol === 'title') {
                        $column = 'name';
                    } elseif ($cleanCol === 'is_active') {
                        $column = 'status';
                    } elseif ($cleanCol === 'ghorfe_online_list_id') {
                        return $this;
                    } elseif (in_array($cleanCol, self::IGNORED_COLUMNS, true)) {
                        return $this;
                    }
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            /**
             * sms_templates has no service_type / otp_category / group / ghorfe_online_list_id columns,
             * so null checks on them are skipped, like where() and whereIn() above.
             */
            public function whereNull($columns, $boolean = 'and', $not = false)
            {
                if (is_string($columns)) {
                    $cleanCol = str_replace('sms_templates.', '', $columns);
                    if ($cleanCol === 'ghorfe_online_list_id' || in_array($cleanCol, self::IGNORED_COLUMNS, true)) {
                        return $this;
                    }
                }
                return parent::whereNull($columns, $boolean, $not);
            }

            public function whereNotNull($columns, $boolean = 'and')
            {
                return $this->whereNull($columns, $boolean, true);
            }

            public function orWhereNull($columns)
            {
                return $this->whereNull($columns, 'or');
            }

            public function orWhereNotNull($columns)
            {
                return $this->whereNull($columns, 'or', true);
            }
        };
    }

    protected static function booted(): void
    {
        static::saving(function (SmsTemplate $model) {
            $allowed = [
                'id',
                'user_id',
                'name',
                'fa_name',
                'content',
                'status',
                'created_at',
                'updated_at',
            ];

            if (empty($model->attributes['name']) && !empty($model->attributes['title'])) {
                $model->attributes['name'] = $model->attributes['title'];
            }
            if (empty($model->attributes['fa_name'])) {
                $model->attributes['fa_name'] = $model->attributes['name'] ?? 'الگو';
            }
            if (!isset($model->attributes['status'])) {
                $model->attributes['status'] = 1;
            } elseif ($model->attributes['status'] === 'active') {
                $model->attributes['status'] = 1;
            } elseif ($model->attributes['status'] === 'inactive') {
                $model->attributes['status'] = 0;
            }

            foreach (array_keys($model->attributes) as $attrKey) {
                if (!in_array($attrKey, $allowed, true)) {
                    unset($model->attributes[$attrKey]);
                }
            }
        });
    }

    public function getServiceTypeAttribute($val)
    {
        if (!empty($val)) {
            return $val;
        }

        return self::classify(
            $this->attributes['name'] ?? null,
            $this->attributes['fa_name'] ?? null,
            $this->attributes['content'] ?? null,
        );
    }

    /**
     * The one PHP rule for a template's type. Used by the accessor and by every caller that has the text.
     */
    public static function classify(?string $name, ?string $faName, ?string $content): string
    {
        $content = (string) $content;

        foreach (self::ADVERTISING_MARKERS as $marker) {
            if (str_contains($content, $marker)) {
                return self::TYPE_ADVERTISING;
            }
        }

        $alternatives = implode('|', array_map(fn (string $k) => preg_quote($k, '/'), self::OTP_KEYWORDS));
        $combined = $name . ' ' . $faName . ' ' . $content;

        return preg_match('/(' . $alternatives . ')/iu', $combined) === 1 ? self::TYPE_OTP : self::TYPE_ADVERTISING;
    }

    /**
     * The type of a stored template, read from the whole row. Loading a single column instead
     * (value('service_type')) would leave the text empty and always classify the template as advertising.
     */
    public static function typeOf(?int $id): ?string
    {
        return $id ? static::query()->find($id)?->service_type : null;
    }

    /** The OTP category of a stored template (whole row, see typeOf()). */
    public static function categoryOf(?int $id): ?string
    {
        return $id ? static::query()->find($id)?->otp_category : null;
    }

    /**
     * Whether this template may be used by the business: shared templates (no owner) or templates of
     * the business owner, the same rule as where('ghorfe_online_list_id', ...). A business without an
     * owner is not filtered (the where() fallback is unchanged), so it is treated as visible here too.
     */
    public function isVisibleTo(?GhorfeOnlineList $business): bool
    {
        if (!$business || $business->user_id === null || $this->user_id === null) {
            return true;
        }

        return (int) $this->user_id === (int) $business->user_id;
    }

    /** OTP templates, computed in SQL with the same rule as classify(). */
    public function scopeOtp(Builder $query): Builder
    {
        [$sql, $bindings] = self::otpCondition();

        return $query->whereRaw('(' . $sql . ')', $bindings);
    }

    /** Non-OTP (advertising) templates: the complement of scopeOtp(). */
    public function scopeNotOtp(Builder $query): Builder
    {
        [$sql, $bindings] = self::otpCondition();

        return $query->whereRaw('NOT (' . $sql . ')', $bindings);
    }

    /**
     * @return array{0: string, 1: array<int, string>} SQL condition and bindings for "is an OTP template"
     */
    protected static function otpCondition(): array
    {
        $bindings = [];

        $markers = [];
        foreach (self::ADVERTISING_MARKERS as $marker) {
            $markers[] = "COALESCE(content, '') LIKE ?";
            $bindings[] = '%' . $marker . '%';
        }

        $keywords = [];
        foreach (['name', 'fa_name', 'content'] as $column) {
            foreach (self::OTP_KEYWORDS as $keyword) {
                $keywords[] = "COALESCE({$column}, '') LIKE ?";
                $bindings[] = '%' . $keyword . '%';
            }
        }

        $sql = '(NOT (' . implode(' OR ', $markers) . ') AND (' . implode(' OR ', $keywords) . '))';

        return [$sql, $bindings];
    }

    public function getOtpCategoryAttribute($val)
    {
        if (!empty($val)) {
            return $val;
        }

        if ($this->service_type !== 'otp') {
            return null;
        }

        $combined = ($this->attributes['name'] ?? '') . ' ' . ($this->attributes['fa_name'] ?? '') . ' ' . ($this->attributes['content'] ?? '');

        if (preg_match('/(حقوقی|اجرا|ضمانت|سند)/iu', $combined)) {
            return \App\Enums\SmsOtpCategory::Legal->value;
        }
        if (preg_match('/(فاکتور|مالی|قسط|اقساط|واریز|بانک|چک|شارژ)/iu', $combined)) {
            return \App\Enums\SmsOtpCategory::Finance->value;
        }
        if (preg_match('/(فروش|پیش فاکتور|خدمات)/iu', $combined)) {
            return \App\Enums\SmsOtpCategory::Sales->value;
        }

        return \App\Enums\SmsOtpCategory::Auth->value;
    }

    public function getTitleAttribute($val)
    {
        return $this->attributes['name'] ?? null;
    }

    public function setTitleAttribute($val)
    {
        $this->attributes['name'] = $val;
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function business()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSmsMessageGroupIdAttribute()
    {
        return $this->attributes['group'] ?? null;
    }

    public function setSmsMessageGroupIdAttribute($val)
    {
        $this->attributes['group'] = $val;
    }

    public function getStatusAttribute($val)
    {
        if ($val === 1 || $val === '1' || $val === 'active') {
            return 'active';
        }
        if ($val === 0 || $val === '0' || $val === 'inactive') {
            return 'inactive';
        }
        return $val ?? 'active';
    }

    public function setStatusAttribute($val)
    {
        if ($val === 'active' || $val === 1 || $val === '1') {
            $this->attributes['status'] = 1;
        } elseif ($val === 'inactive' || $val === 0 || $val === '0') {
            $this->attributes['status'] = 0;
        } else {
            $this->attributes['status'] = $val;
        }
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [1, '1', 'active']);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $status = request('status');

        if (isset($name) && trim($name) != '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if (isset($status) && trim($status) != 'all') {
            $query->where('status', $status);
        }

        return $query;
    }
}
