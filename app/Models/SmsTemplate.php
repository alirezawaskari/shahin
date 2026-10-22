<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
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

        // System/OTP identification: classify templates that deliver passwords, confirmation codes, or system OTP notices
        $content = (string) ($this->attributes['content'] ?? '');
        $name = (string) ($this->attributes['name'] ?? '');
        $faName = (string) ($this->attributes['fa_name'] ?? '');
        $combined = $name . ' ' . $faName . ' ' . $content;

        // If template has opt-out «لغو11» or «لغو 11» (or Persian digits «لغو۱۱»), it was designed for promotional/advertising
        if (str_contains($content, 'لغو11') || str_contains($content, 'لغو 11') || str_contains($content, 'لغو۱۱') || str_contains($content, 'لغو ۱۱')) {
            return 'advertising';
        }

        // Keywords indicating OTP / verification / authentication messages
        if (preg_match('/(رمز|کد تایید|کد تأیید|otp|password|login|verification|token)/iu', $combined)) {
            return 'otp';
        }

        return 'advertising';
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
