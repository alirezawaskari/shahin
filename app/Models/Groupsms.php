<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Groupsms extends Model
{
    /**
     * types of group sms
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

    /**
     * @return BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @return BelongsTo
     */
    public function phone_list()
    {
        return $this->belongsTo(PhoneList::class);
    }

    /**
     * SMS Folder / Category
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SmsMessageGroup::class, 'sms_message_group_id');
    }

    /**
     * Associated business / ghorfe
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    /**
     * Associated unified SMS Message record
     */
    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class, 'sms_message_id');
    }

    /**
     * Check if message is classified / confidential (e.g. classification='classified' or in financial/legal folders)
     */
    public function isConfidential(): bool
    {
        if (($this->classification ?? '') === 'classified') {
            return true;
        }

        if ($this->group && method_exists($this->group, 'isConfidential') && $this->group->isConfidential()) {
            return true;
        }

        if ($this->smsMessage && method_exists($this->smsMessage, 'isConfidential') && $this->smsMessage->isConfidential()) {
            return true;
        }

        return false;
    }
}
