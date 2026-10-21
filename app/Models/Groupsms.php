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
    protected $guarded = [];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            /**
             * Names the SMS module uses for columns that are stored under another name.
             */
            protected const COLUMN_ALIASES = [
                'content' => 'message',
                'business_id' => 'ghorfe_online_list_id',
                'sms_message_template_id' => 'template_id',
                'sms_message_group_id' => 'group',
            ];

            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column)) {
                    $prefix = str_starts_with($column, 'groupsms.') ? 'groupsms.' : '';
                    $bare = substr($column, strlen($prefix));
                    if (isset(self::COLUMN_ALIASES[$bare])) {
                        $column = $prefix . self::COLUMN_ALIASES[$bare];
                    }
                }

                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\SmsMessageObserver::class);

        static::saving(function (Groupsms $model) {
            // The legacy table requires these; fill them when the caller (new SMS code) does not.
            if (empty($model->attributes['message']) && !empty($model->attributes['content'])) {
                $model->attributes['message'] = $model->attributes['content'];
            }
            if (!isset($model->attributes['message'])) {
                $model->attributes['message'] = '';
            }
            if (empty($model->attributes['phones']) && !empty($model->attributes['recipient_phone'])) {
                $model->attributes['phones'] = $model->attributes['recipient_phone'];
            }
            if (empty($model->attributes['type'])) {
                $model->attributes['type'] = !empty($model->attributes['is_bulk']) ? 'groupsPhone' : 'single';
            }
            if (empty($model->attributes['title'])) {
                $model->attributes['title'] = 'پیامک ' . ($model->attributes['type'] ?? 'ارسال');
            }
            if (!isset($model->attributes['flag'])) {
                $model->attributes['flag'] = 1;
            }

            // `content` / `body` are only other names for `message`; they are not columns.
            unset($model->attributes['content'], $model->attributes['body']);
        });
    }

    protected $casts = [
        'filters' => 'array',
        'is_bulk' => 'boolean',
        'is_shared' => 'boolean',
        'cost' => 'float',
        'cost_estimate' => 'float',
        'character_count' => 'integer',
        'page_count' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
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
