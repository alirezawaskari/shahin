<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'ghorfe_settings';
    protected $guarded = [];

    /*
    |-----------------------------------
    | GLOBAL FILLABLE (اگر واقعاً لازم داری)
    |-----------------------------------
    */
    protected $fillable = [
        'key',
        'value',
        'settingable_type',
        'settingable_id',
        'number',
        'type',
        'line_type',
        'sms_panel',
        'status',
        'name',
        'slug',
        'parent_id',
    ];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            protected const VIRTUAL_COLUMNS = [
                'type',
                'line_type',
                'status',
                'number',
                'sms_panel',
                'name',
                'slug',
                'parent_id',
                'description',
                'contract_status',
                'match_type',
                'keyword',
                'response_content',
                'is_active',
            ];

            /**
             * `value` is a TEXT column holding both JSON documents and plain strings (phone, address, panel name).
             * MySQL 8 raises error 3141 when json_extract meets a plain string, MariaDB returns NULL.
             * Every JSON-path read therefore goes through this JSON_VALID-guarded expression (NULL for plain rows).
             */
            protected function jsonPathSql(string $key): string
            {
                $key = preg_replace('/[^A-Za-z0-9_]/', '', $key);
                $extract = "json_extract(`ghorfe_settings`.`value`, '$.\"{$key}\"')";

                return "(CASE WHEN json_valid(`ghorfe_settings`.`value`) THEN (CASE WHEN json_type({$extract}) <> 'NULL' THEN json_unquote({$extract}) END) END)";
            }

            /**
             * Maps a virtual column name ("status") or a "value->key" path to its key, or null when it is neither.
             */
            protected function virtualKey($column): ?string
            {
                if (!is_string($column)) {
                    return null;
                }
                $clean = str_replace('ghorfe_settings.', '', $column);
                if (in_array($clean, self::VIRTUAL_COLUMNS, true)) {
                    return $clean;
                }
                if (preg_match('/^value->([A-Za-z0-9_]+)$/', $clean, $m)) {
                    return $m[1];
                }

                return null;
            }

            protected function transformColumns(array $columns): array
            {
                $transformed = [];
                $hasVirtual = false;
                foreach ($columns as $column) {
                    $cleanCol = is_string($column) ? str_replace('ghorfe_settings.', '', $column) : $column;
                    if (is_string($cleanCol) && in_array($cleanCol, self::VIRTUAL_COLUMNS, true)) {
                        $transformed[] = \Illuminate\Support\Facades\DB::raw($this->jsonPathSql($cleanCol) . " as `{$cleanCol}`");
                        $hasVirtual = true;
                    } else {
                        $transformed[] = $column;
                    }
                }
                if ($hasVirtual && !in_array('*', $columns, true) && !in_array('value', $columns, true) && !in_array('ghorfe_settings.value', $columns, true)) {
                    $transformed[] = 'ghorfe_settings.value';
                }
                return $transformed;
            }

            public function select($columns = ['*'])
            {
                $cols = is_array($columns) ? $columns : func_get_args();
                return parent::select($this->transformColumns($cols));
            }

            public function addSelect($column)
            {
                $cols = is_array($column) ? $column : func_get_args();
                return parent::addSelect($this->transformColumns($cols));
            }

            public function get($columns = ['*'])
            {
                $cols = is_array($columns) ? $columns : func_get_args();
                if ($cols !== ['*']) {
                    $cols = $this->transformColumns($cols);
                }
                return parent::get($cols);
            }

            public function pluck($column, $key = null)
            {
                $cleanCol = is_string($column) ? str_replace('ghorfe_settings.', '', $column) : $column;
                if (is_string($cleanCol) && in_array($cleanCol, self::VIRTUAL_COLUMNS, true)) {
                    // Unquoted alias: Laravel derives the result property name from the text after " as ".
                    $column = \Illuminate\Support\Facades\DB::raw($this->jsonPathSql($cleanCol) . " as {$cleanCol}");
                }
                if ($key !== null) {
                    $cleanKey = is_string($key) ? str_replace('ghorfe_settings.', '', $key) : $key;
                    if (is_string($cleanKey) && in_array($cleanKey, self::VIRTUAL_COLUMNS, true)) {
                        $key = \Illuminate\Support\Facades\DB::raw($this->jsonPathSql($cleanKey) . " as {$cleanKey}");
                    }
                }
                return parent::pluck($column, $key);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if (is_string($column)) {
                    $cleanCol = str_replace('ghorfe_settings.', '', $column);
                    if (in_array($cleanCol, self::VIRTUAL_COLUMNS, true)) {
                        $column = \Illuminate\Support\Facades\DB::raw($this->jsonPathSql($cleanCol));
                    }
                }
                return parent::orderBy($column, $direction);
            }

            public function whereNull($columns, $boolean = 'and', $not = false)
            {
                $key = $this->virtualKey($columns);
                if ($key !== null) {
                    return $this->whereRaw($this->jsonPathSql($key) . ($not ? ' is not null' : ' is null'), [], $boolean);
                }
                return parent::whereNull($columns, $boolean, $not);
            }

            public function orWhereNull($columns)
            {
                return $this->whereNull($columns, 'or');
            }

            public function orWhereNotNull($columns)
            {
                return $this->whereNull($columns, 'or', true);
            }

            public function orWhereIn($column, $values)
            {
                return $this->whereIn($column, $values, 'or');
            }

            public function whereNotIn($column, $values, $boolean = 'and')
            {
                return $this->whereIn($column, $values, $boolean, true);
            }

            public function whereNotNull($columns, $boolean = 'and')
            {
                return $this->whereNull($columns, $boolean, true);
            }

            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column)) {
                    $cleanCol = str_replace('ghorfe_settings.', '', $column);
                    if ($cleanCol === 'status') {
                        $targetStatus = $operator !== null && $value === null ? $operator : $value;
                        if ($targetStatus === 'active') {
                            return parent::where(function ($q) {
                                $q->where('value->status', 'active')
                                  ->whereNull('value->status', 'or');
                            }, null, null, $boolean);
                        }
                    } elseif ($cleanCol === 'type') {
                        $targetType = $operator !== null && $value === null ? $operator : $value;
                        if ($targetType === 'advertising') {
                            // Legacy sender rows have no type and count as advertising. Only sender rows:
                            // phone/email/API-key settings also lack a type and must never match.
                            return parent::where(function ($q) {
                                $q->where('value->type', 'advertising')
                                  ->orWhere(function ($legacy) {
                                      $legacy->whereNull('value->type')->where('key', 'sms_sender_number');
                                  });
                            }, null, null, $boolean);
                        }
                    }

                    $key = $this->virtualKey($column);
                    if ($key !== null) {
                        [$value, $operator] = $this->query->prepareValueAndOperator($value, $operator, func_num_args() === 2);
                        if (!in_array(strtolower((string) $operator), ['=', '!=', '<>', '<', '>', '<=', '>=', 'like', 'not like'], true)) {
                            $operator = '=';
                        }
                        return $this->whereRaw($this->jsonPathSql($key) . " {$operator} ?", [$value], $boolean);
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                $key = $this->virtualKey($column);
                if ($key !== null) {
                    $values = $values instanceof \Illuminate\Contracts\Support\Arrayable ? $values->toArray() : (array) $values;
                    if ($values === []) {
                        return $this->whereRaw($not ? '1 = 1' : '1 = 0', [], $boolean);
                    }
                    $marks = implode(',', array_fill(0, count($values), '?'));
                    return $this->whereRaw($this->jsonPathSql($key) . ($not ? ' not in ' : ' in ') . "({$marks})", array_values($values), $boolean);
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Setting $setting) {
            $virtualKeys = [
                'number',
                'type',
                'line_type',
                'sms_panel',
                'status',
                'name',
                'slug',
                'parent_id',
                'match_type',
                'keyword',
                'response_content',
                'is_active',
                'description',
                'contract_status',
            ];

            $decoded = [];
            if (!empty($setting->attributes['value']) && is_string($setting->attributes['value'])) {
                $check = json_decode($setting->attributes['value'], true);
                if (is_array($check)) {
                    $decoded = $check;
                }
            }

            $hasVirtual = false;
            foreach ($virtualKeys as $vKey) {
                if (array_key_exists($vKey, $setting->attributes)) {
                    $decoded[$vKey] = $setting->attributes[$vKey];
                    unset($setting->attributes[$vKey]);
                    $hasVirtual = true;
                }
            }

            if ($hasVirtual) {
                $setting->attributes['value'] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }

            if ($setting->key === 'sms_sender_number' && ($setting->type ?? null) === 'otp') {
                $query = static::query()->where('key', 'sms_sender_number')->where('value->type', 'otp');
                if ($setting->exists) {
                    $query->where($setting->getKeyName(), '!=', $setting->getKey());
                }
                if ($query->exists()) {
                    throw new \DomainException('Only one OTP sender number is allowed across the entire system.');
                }
            }
        });
    }

    /*
    |-----------------------------------
    | MORPH RELATION
    |-----------------------------------
    |*/
    public function settingable()
    {
        return $this->morphTo();
    }

    public function getNumberAttribute($val)
    {
        if ($val !== null && $val !== '') {
            return $val;
        }
        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? ($decoded['number'] ?? $this->value) : $this->value;
    }

    public function getTypeAttribute($val)
    {
        if ($val !== null && $val !== '') {
            return $val;
        }
        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? ($decoded['type'] ?? 'advertising') : 'advertising';
    }

    public function getLineTypeAttribute($val)
    {
        if ($val !== null && $val !== '') {
            return $val;
        }
        $decoded = json_decode($this->value, true);
        // Default to 'service' for OTP lines, 'normal' for advertising lines if unspecified
        if (is_array($decoded) && !empty($decoded['line_type'])) {
            return $decoded['line_type'];
        }
        return ($this->type === 'otp') ? 'service' : 'normal';
    }

    public function isServiceLine(): bool
    {
        return $this->line_type === 'service';
    }

    public function isNormalLine(): bool
    {
        return !$this->isServiceLine();
    }

    public function getSmsPanelAttribute($val)
    {
        if ($val !== null && $val !== '') {
            return $val;
        }
        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? ($decoded['sms_panel'] ?? 'IranSms') : 'IranSms';
    }

    public function getStatusAttribute($val)
    {
        if ($val !== null && $val !== '') {
            return $val;
        }
        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? ($decoded['status'] ?? 'active') : 'active';
    }

    public function getCurrentBalanceAttribute()
    {
        return (float) ($this->settingable?->advertising_balance ?? 0);
    }

    public function business()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'settingable_id');
    }

    public function getBusinessesAttribute()
    {
        $ids = [];
        if ($this->settingable_id) {
            $ids[] = $this->settingable_id;
        }
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            if (!empty($dec['business_ids']) && is_array($dec['business_ids'])) {
                $ids = array_merge($ids, $dec['business_ids']);
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return collect();
        }
        return GhorfeOnlineList::whereIn('id', $ids)->get();
    }

    public function getDescriptionAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['description'] ?? null;
        }
        return $val;
    }

    public function setDescriptionAttribute($val)
    {
        $current = $this->attributes['value'] ?? null;
        $data = [];
        if (is_string($current) && (str_starts_with($current, '{') || str_starts_with($current, '['))) {
            $data = json_decode($current, true) ?: [];
        }
        $data['description'] = $val;
        $this->attributes['value'] = json_encode($data);
    }

    public function getContractStatusAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['contract_status'] ?? 'تایید شده';
        }
        return 'تایید شده';
    }

    public function setContractStatusAttribute($val)
    {
        $current = $this->attributes['value'] ?? null;
        $data = [];
        if (is_string($current) && (str_starts_with($current, '{') || str_starts_with($current, '['))) {
            $data = json_decode($current, true) ?: [];
        }
        $data['contract_status'] = $val;
        $this->attributes['value'] = json_encode($data);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOtp($query)
    {
        return $query->where('key', 'sms_sender_number')->where('type', 'otp');
    }

    public function scopeAdvertising($query)
    {
        return $query->where('key', 'sms_sender_number')->where('type', 'advertising');
    }

    public function isOtp(): bool
    {
        return $this->type === 'otp';
    }

    public function isAdvertising(): bool
    {
        return $this->type === 'advertising';
    }

    public function getNameAttribute()
    {
        if ($this->key === 'sms_message_group' || $this->key === 'sms_panel' || $this->key === 'sms_auto_responder') {
            $val = $this->attributes['value'] ?? null;
            if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
                $dec = json_decode($val, true);
                return $dec['name'] ?? $val;
            }
            return $val;
        }
        return $this->attributes['value'] ?? null;
    }

    public function setNameAttribute($val)
    {
        if (in_array($this->key, ['sms_message_group', 'sms_panel', 'sms_auto_responder'])) {
            $dec = json_decode($this->attributes['value'] ?? '{}', true) ?: [];
            $dec['name'] = $val;
            $this->attributes['value'] = json_encode($dec, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['name'] = $val;
        }
    }

    public function setSlugAttribute($val)
    {
        if (in_array($this->key, ['sms_message_group', 'sms_panel', 'sms_auto_responder'])) {
            $dec = json_decode($this->attributes['value'] ?? '{}', true) ?: [];
            $dec['slug'] = $val;
            $this->attributes['value'] = json_encode($dec, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['slug'] = $val;
        }
    }

    public function setParentIdAttribute($val)
    {
        if (in_array($this->key, ['sms_message_group', 'sms_panel', 'sms_auto_responder'])) {
            $dec = json_decode($this->attributes['value'] ?? '{}', true) ?: [];
            $dec['parent_id'] = $val;
            $this->attributes['value'] = json_encode($dec, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['parent_id'] = $val;
        }
    }

    public const MATCH_EXACT = 'exact';
    public const MATCH_STARTS_WITH = 'starts_with';

    public static function matchTypeLabel(?string $type): string
    {
        return match ($type) {
            self::MATCH_STARTS_WITH => 'شروع با (پیشوند)',
            default => 'تطابق دقیق',
        };
    }

    public function getTriggerKeywordAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['trigger_keyword'] ?? null;
        }
        return null;
    }

    public function getNormalizedKeywordAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['normalized_keyword'] ?? null;
        }
        return null;
    }

    public function getMatchTypeAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['match_type'] ?? $this->type ?? self::MATCH_EXACT;
        }
        return $this->type ?? self::MATCH_EXACT;
    }

    public function getReplyTextAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['reply_text'] ?? null;
        }
        return null;
    }

    public function getSenderNumberIdAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['sender_number_id'] ?? null;
        }
        return null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' || (int)$this->status === 1;
    }

    public function setIsActiveAttribute($val): void
    {
        $this->status = $val ? 'active' : 'inactive';
    }

    public function getGhorfeOnlineListIdAttribute()
    {
        return $this->settingable_id;
    }

    public function getConnectionInfoAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['connection_info'] ?? [];
        }
        return [];
    }

    public function getSendSettingsAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['send_settings'] ?? [];
        }
        return [];
    }

    public function getReceiveSettingsAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['receive_settings'] ?? [];
        }
        return [];
    }

    public function getDeliverySettingsAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['delivery_settings'] ?? [];
        }
        return [];
    }

    public function getConnectionSetting(string $key, $default = null)
    {
        return $this->connection_info[$key] ?? $default;
    }

    public function getSendSetting(string $key, $default = null)
    {
        return $this->send_settings[$key] ?? $default;
    }

    public function getReceiveSetting(string $key, $default = null)
    {
        return $this->receive_settings[$key] ?? $default;
    }

    public function getDeliverySetting(string $key, $default = null)
    {
        return $this->delivery_settings[$key] ?? $default;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || (int)$this->status === 1;
    }

    public function getSlugAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            if (!empty($dec['slug'])) {
                return $dec['slug'];
            }
        }
        return \Illuminate\Support\Str::slug($this->name ?? '');
    }

    public function getParentIdAttribute()
    {
        $val = $this->attributes['value'] ?? null;
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $dec = json_decode($val, true);
            return $dec['parent_id'] ?? null;
        }
        return null;
    }

    public function getChildrenAttribute()
    {
        return Setting::where('key', 'sms_message_group')
            ->where('settingable_id', $this->settingable_id)
            ->get()
            ->filter(fn ($s) => (int) $s->parent_id === (int) $this->id)
            ->values();
    }

    public function getTemplatesCountAttribute()
    {
        return SmsTemplate::where(function ($q) {
            $q->where('group', $this->id)
              ->orWhere('group', $this->slug);
        })->count();
    }

    public function templates()
    {
        return $this->hasMany(SmsTemplate::class, 'group', 'id');
    }

    public function messages()
    {
        return $this->hasMany(Groupsms::class, 'group', 'id');
    }

    public function isPromotional(): bool
    {
        $promotional = ['monasebati', 'foroush', 'poshtibani'];
        if ($this->parent_id) {
            $parent = Setting::find($this->parent_id);
            return $parent && in_array($parent->slug, $promotional, true);
        }
        return in_array($this->slug, $promotional, true);
    }

    public function isConfidential(): bool
    {
        $confidentialKeywords = ['confidential', 'mahrameh', 'hoghoghi', 'ekhtar', 'erja-be-hoghoghi', 'secret', 'private', 'mali', 'financial'];
        $slug = strtolower($this->slug ?? '');
        $name = $this->name ?? '';
        foreach ($confidentialKeywords as $keyword) {
            if ($slug === $keyword || str_contains($slug, $keyword)) {
                return true;
            }
        }
        foreach (['محرمانه', 'حقوقی', 'اخطار', 'ارجاع به حقوقی', 'مالی'] as $keyword) {
            if (str_contains($name, $keyword)) {
                return true;
            }
        }
        if ($this->parent_id) {
            $parent = Setting::find($this->parent_id);
            return $parent && $parent->isConfidential();
        }
        return false;
    }

    public function scopePromotional($query)
    {
        return $query->where('key', 'sms_message_group');
    }

    public static function isPromotionalId($id): bool
    {
        if ($id === null || $id === '') {
            return true;
        }
        $group = self::find($id);
        return $group ? $group->isPromotional() : true;
    }

    /*
    |-----------------------------------
    | GLOBAL SETTINGS CACHE
    |-----------------------------------
    */
    public static function getGlobalCached()
    {
        return Cache::rememberForever('settings_global', function () {
            return self::whereNull('settingable_id')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /*
    |-----------------------------------
    | COMPANY SETTINGS CACHE
    |-----------------------------------
    */
    public static function getCompanyCached($companyId)
    {
        return Cache::rememberForever("settings_company_{$companyId}", function () use ($companyId) {
            return self::where('settingable_type', \App\Models\GhorfeOnlineList::class)
                ->where('settingable_id', $companyId)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}
