<?php

namespace App\Models;

use App\Helpers\MediaLibraryHelper;
use App\Models\Concerns\AdoptsLegacyMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contact extends Model implements HasMedia
{
    use InteractsWithMedia;
    use AdoptsLegacyMedia;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'files' => 'array',
    ];

    /**
     * Attachments the visitor uploaded with the message. New uploads go here only —
     * the legacy `files` column is never written.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')->useDisk('public');
    }

    /** Old `files` paths are copied into Spatie on save; the column stays untouched. */
    protected function legacyArrayMediaMap(): array
    {
        return ['files' => 'files'];
    }

    /**
     * Attachment urls to render: Spatie media if any, else whatever the legacy `files`
     * column still holds.
     *
     * Deliberately NOT named `files`: shadowing that column would feed adoption its own
     * '/storage/...' output instead of the legacy paths, and adoptLegacyPathsIn() skips
     * those — migration would silently never run.
     */
    public function getFileUrlsAttribute(): array
    {
        $media = MediaLibraryHelper::existingMedia($this, 'files');
        if ($media->isNotEmpty()) {
            return $media->map(fn ($m) => $m->getUrl())->all();
        }

        return is_array($this->files) ? $this->files : [];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responder()
   {
      return $this->belongsTo(User::class, 'responder_id');
   }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $text = request('text');
        $card = request('card');
        $status = request('status');
        if (auth()->user()->admin_representation) {
            $query->where('representation_id', auth()->user()->admin_representation->id);
        }
        if(isset($name) && $name != '')
        {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if(isset($text) && $text != '')
        {
            $query->where('text', 'LIKE', '%' . $text . '%');
        }

        if(isset($card) && $card != 'all')
        {
            $query->where('card_id', $card);
        }
        if(isset($status) && $status != 'all')
        {
            $query->where('status', $status);
        }

        return $query;
    }
}
