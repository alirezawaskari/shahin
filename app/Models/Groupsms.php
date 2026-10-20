<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupsms extends Model
{
    /**
     *types of group sms
     */
    const TYPE = [
      'single' => 'ارسال تکی',
      'groupsService' => 'گروهی خدمات',
      'groupsPhone' => 'گروهی فهرست شماره ها',
    ];

    /**
     * @var array
     */
    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            protected const IGNORED_COLUMNS = [
                'ghorfe_online_list_id',
                'direction',
                'sender_number_id',
                'template_id',
                'request_id',
                'created_by',
                'group',
                'cost',
                'tracking_id',
                'filters',
                'scheduled_at',
                'sent_at',
                'completed_at',
                'service_type',
                'otp_category',
                'sms_message_group_id',
                'classification',
                'is_shared',
                'callcenter_id',
                'sender_role',
                'channel',
                'business_id',
                'user_id',
                'delivery_status',
            ];

            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column)) {
                    $cleanCol = str_replace('groupsms.', '', $column);
                    if ($cleanCol === 'is_bulk') {
                        $target = $operator !== null && $value === null ? $operator : $value;
                        if ($target) {
                            return parent::where('type', '!=', 'single', $boolean);
                        } else {
                            return parent::where('type', '=', 'single', $boolean);
                        }
                    } elseif ($cleanCol === 'content') {
                        $column = 'message';
                    } elseif ($cleanCol === 'recipient_phone') {
                        $column = 'phones';
                    } elseif ($cleanCol === 'service_type') {
                        return $this;
                    } elseif ($cleanCol === 'status') {
                        $target = $operator !== null && $value === null ? $operator : $value;
                        $map = [
                            'delivered' => 1,
                            'sent' => 1,
                            'success' => 1,
                            'failed' => 2,
                            'pending' => 0,
                            'queued' => 0,
                            'pending_queue' => 0,
                        ];
                        if (is_string($target) && isset($map[strtolower($target)])) {
                            if ($value === null) {
                                $operator = $map[strtolower($target)];
                            } else {
                                $value = $map[strtolower($target)];
                            }
                        }
                    } elseif (in_array($cleanCol, self::IGNORED_COLUMNS, true)) {
                        return $this;
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereNull($columns, $boolean = 'and', $not = false)
            {
                foreach ((array) $columns as $col) {
                    $cleanCol = is_string($col) ? str_replace('groupsms.', '', $col) : $col;
                    if ($cleanCol === 'is_bulk') {
                        return $not
                            ? parent::where('type', '!=', 'single', $boolean)
                            : parent::where('type', '=', 'single', $boolean);
                    }
                    if (is_string($cleanCol) && in_array($cleanCol, self::IGNORED_COLUMNS, true)) {
                        return $not ? $this->whereRaw('1 = 0') : $this;
                    }
                }
                return parent::whereNull($columns, $boolean, $not);
            }

            public function select($columns = ['*'])
            {
                $cols = is_array($columns) ? $columns : func_get_args();
                $actualColumns = [
                    'id', 'title', 'message', 'status', 'flag', 'send_at',
                    'type', 'phone_list_id', 'phones', 'created_at', 'updated_at',
                ];
                $modified = [];
                foreach ($cols as $c) {
                    if (is_string($c) && !str_contains($c, '*') && !str_contains($c, '(') && !str_contains($c, ' as ')) {
                        $clean = str_replace('groupsms.', '', trim($c, '` '));
                        if (!in_array($clean, $actualColumns, true)) {
                            $modified[] = \Illuminate\Support\Facades\DB::raw("NULL as `{$clean}`");
                            continue;
                        }
                    }
                    $modified[] = $c;
                }
                return parent::select($modified);
            }

            public function sum($column)
            {
                $cleanCol = is_string($column) ? str_replace('groupsms.', '', trim($column, '` ')) : $column;
                if ($cleanCol === 'total_recipients') {
                    return (int) (clone $this)->count();
                }
                if ($cleanCol === 'cost') {
                    return (float) ((clone $this)->count() * 1200);
                }
                $actualColumns = [
                    'id', 'title', 'message', 'status', 'flag', 'send_at',
                    'type', 'phone_list_id', 'phones', 'created_at', 'updated_at',
                ];
                if (is_string($cleanCol) && !in_array($cleanCol, $actualColumns, true)) {
                    return 0;
                }
                return parent::__call('sum', [$column]);
            }

            public function aggregate($function, $columns = ['*'])
            {
                $col = is_array($columns) ? ($columns[0] ?? '*') : $columns;
                $cleanCol = is_string($col) ? str_replace('groupsms.', '', trim($col, '` ')) : $col;

                $actualColumns = [
                    'id', 'title', 'message', 'status', 'flag', 'send_at',
                    'type', 'phone_list_id', 'phones', 'created_at', 'updated_at',
                ];

                if (is_string($cleanCol) && $cleanCol !== '*' && !in_array($cleanCol, $actualColumns, true)) {
                    if ($function === 'sum') {
                        if ($cleanCol === 'total_recipients') {
                            return (int) (clone $this)->count();
                        }
                        if ($cleanCol === 'cost') {
                            return (float) ((clone $this)->count() * 1200);
                        }
                    }
                    return 0;
                }

                return parent::aggregate($function, $columns);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if (is_string($column)) {
                    $cleanCol = str_replace('groupsms.', '', $column);
                    if ($cleanCol === 'status') {
                        $map = [
                            'delivered' => 1,
                            'sent' => 1,
                            'success' => 1,
                            'failed' => 2,
                            'pending' => 0,
                            'queued' => 0,
                            'pending_queue' => 0,
                        ];
                        $mapped = [];
                        foreach ($values as $v) {
                            if (is_string($v) && isset($map[strtolower($v)])) {
                                $mapped[] = $map[strtolower($v)];
                            } else {
                                $mapped[] = $v;
                            }
                        }
                        return parent::whereIn($column, array_unique($mapped), $boolean, $not);
                    }
                    if (in_array($cleanCol, self::IGNORED_COLUMNS, true)) {
                        return $this;
                    }
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }
        };
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\SmsMessageObserver::class);

        static::saving(function (Groupsms $model) {
            $allowed = [
                'id',
                'title',
                'message',
                'status',
                'flag',
                'send_at',
                'type',
                'phone_list_id',
                'phones',
                'created_at',
                'updated_at',
            ];

            // Ensure title and message are filled
            if (empty($model->attributes['title'])) {
                $model->attributes['title'] = 'پیامک ' . ($model->attributes['type'] ?? 'ارسال');
            }
            if (empty($model->attributes['message']) && !empty($model->attributes['content'])) {
                $model->attributes['message'] = $model->attributes['content'];
            }
            if (empty($model->attributes['phones']) && !empty($model->attributes['recipient_phone'])) {
                $model->attributes['phones'] = $model->attributes['recipient_phone'];
            }
            if (empty($model->attributes['type'])) {
                $model->attributes['type'] = !empty($model->attributes['is_bulk']) ? 'groupsPhone' : 'single';
            }
            if (!isset($model->attributes['flag'])) {
                $model->attributes['flag'] = 1;
            }

            // Strip any non-existent columns so insert/update does not crash MySQL
            foreach (array_keys($model->attributes) as $attrKey) {
                if (!in_array($attrKey, $allowed, true)) {
                    unset($model->attributes[$attrKey]);
                }
            }
        });
    }

    protected $casts = [
        'filters' => 'array',
        'is_bulk' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getPhoneAttribute($val)
    {
        return $val ?? $this->recipient_phone;
    }

    public function setPhoneAttribute($val)
    {
        $this->recipient_phone = $val;
    }

    public function getSmsCampaignIdAttribute()
    {
        return $this->attributes['request_id'] ?? null;
    }

    public function setSmsCampaignIdAttribute($val)
    {
        $this->attributes['request_id'] = $val;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phone_list()
    {
        return $this->belongsTo(PhoneList::class);
    }

    public function business()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'template_id');
    }

    public function callcenter()
    {
        return $this->belongsTo(Callcenter::class, 'callcenter_id');
    }

    public function group()
    {
        return $this->belongsTo(Setting::class, 'id')->whereRaw('1 = 0');
    }

    public function getGroupAttribute($val)
    {
        if ($this->relationLoaded('group')) {
            return $this->getRelation('group');
        }
        if (is_numeric($val)) {
            $setting = Setting::find($val);
            if ($setting) {
                return $setting;
            }
        }
        if (is_string($val) && $val !== '') {
            $isConf = in_array(strtolower($val), ['mali', 'hoghoghi']);
            return (object) [
                'name' => $val,
                'slug' => $val,
                'is_confidential' => $isConf,
                'isConfidential' => fn() => $isConf,
            ];
        }
        return $val;
    }

    const STATUS_PENDING_QUEUE = 'pending_queue';

    public const PROMOTIONAL_ROOT_SLUGS = [
        'monasebati',
        'foroush',
        'poshtibani',
    ];

    public function recipients()
    {
        return $this->hasMany(Groupsms::class, 'request_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Groupsms::class, 'request_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function senderNumber()
    {
        return $this->belongsTo(Setting::class, 'sender_number_id');
    }

    public function scopePendingQueue($query)
    {
        return $query->where('status', self::STATUS_PENDING_QUEUE);
    }

    public function scopeCampaigns($query)
    {
        return $query->where('is_bulk', true);
    }

    public function scopeMessages($query)
    {
        return $query->where('is_bulk', false);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function getContentAttribute($val)
    {
        return $val ?? $this->attributes['message'] ?? null;
    }

    public function setContentAttribute($val)
    {
        $this->attributes['content'] = $val;
        if (!isset($this->attributes['message'])) {
            $this->attributes['message'] = $val;
        }
    }

    public function getBodyAttribute($val)
    {
        return $val ?? $this->attributes['content'] ?? $this->attributes['message'] ?? null;
    }

    public function setBodyAttribute($val)
    {
        $this->attributes['message'] = $val;
        $this->attributes['content'] = $val;
    }

    public function getMessageAttribute($val)
    {
        return $val ?? $this->attributes['content'] ?? null;
    }

    public function setMessageAttribute($val)
    {
        $this->attributes['message'] = $val;
        if (!isset($this->attributes['content'])) {
            $this->attributes['content'] = $val;
        }
    }

    public function getRecipientPhoneAttribute($val)
    {
        return $val ?? $this->attributes['phones'] ?? null;
    }

    public function setRecipientPhoneAttribute($val)
    {
        $this->attributes['recipient_phone'] = $val;
        if (!isset($this->attributes['phones'])) {
            $this->attributes['phones'] = $val;
        }
    }

    public function getSenderPhoneAttribute($val)
    {
        return $val ?? $this->attributes['sender_number'] ?? null;
    }

    public function setSenderPhoneAttribute($val)
    {
        $this->attributes['sender_phone'] = $val;
        if (!isset($this->attributes['sender_number'])) {
            $this->attributes['sender_number'] = $val;
        }
    }

    public function getSmsMessageTemplateIdAttribute()
    {
        return $this->attributes['template_id'] ?? null;
    }

    public function setSmsMessageTemplateIdAttribute($val)
    {
        $this->attributes['template_id'] = $val;
    }

    public function getSmsMessageGroupIdAttribute()
    {
        return $this->attributes['group'] ?? null;
    }

    public function setSmsMessageGroupIdAttribute($val)
    {
        $this->attributes['group'] = $val;
    }

    public static function isPromotionalGroup(?string $group): bool
    {
        if ($group === null) {
            return true;
        }
        $nonPromotional = ['mali', 'variz-pish-pardakht', 'hoghoghi', 'ekhtar', 'ejraei', 'nobat-dehi'];
        return !in_array(strtolower($group), $nonPromotional, true);
    }

    public function isConfidential(): bool
    {
        if (in_array($this->classification, ['confidential', 'classified'])) {
            return true;
        }

        if (is_object($this->group)) {
            if (method_exists($this->group, 'isConfidential')) {
                return (bool) $this->group->isConfidential();
            }
            if (is_callable($this->group->isConfidential ?? null)) {
                return (bool) ($this->group->isConfidential)();
            }
            if (!empty($this->group->is_confidential)) {
                return true;
            }
            $slug = strtolower($this->group->slug ?? '');
            if (in_array($slug, ['mali', 'hoghoghi'])) {
                return true;
            }
        }

        $groupName = is_object($this->group) ? ($this->group->name ?? '') : ($this->group ?? '');
        return in_array(strtolower($groupName), ['mali', 'hoghoghi']);
    }

    public function smsMessage()
    {
        return $this->hasOne(Groupsms::class, 'id', 'id');
    }
}
