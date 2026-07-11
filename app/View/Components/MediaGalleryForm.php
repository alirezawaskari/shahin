<?php

namespace App\View\Components;

use Illuminate\View\Component;

/**
 * Reusable, Spatie-native slot gallery editor (4 small + 1 big, desktop & mobile).
 *
 * Works for ANY HasMedia model that exposes a `gallerySlot(string $collection, int $index)`
 * method (e.g. Category, Service, GhorfeOnlineList). Everything is parameterised so the
 * same component can drive different models / collections / request keys:
 *
 *   <x-media-gallery-form :owner="$category"
 *                         desktop-collection="gallery_desktop"
 *                         mobile-collection="gallery_mobile"
 *                         desktop-input="deskImage"
 *                         mobile-input="mobileImage" />
 *
 * A null owner (create form) simply renders empty slots.
 */
class MediaGalleryForm extends Component
{
    public $owner;
    public array $sections;
    public int $smallCount;

    public function __construct(
        $owner = null,
        string $desktopCollection = 'gallery_desktop',
        string $mobileCollection = 'gallery_mobile',
        string $desktopInput = 'deskImage',
        string $mobileInput = 'mobileImage',
        int $smallCount = 4
    ) {
        $this->owner = $owner;
        $this->smallCount = $smallCount;
        $this->sections = [
            ['label' => 'عکس های دسکتاپ', 'collection' => $desktopCollection, 'input' => $desktopInput],
            ['label' => 'عکس های موبایل', 'collection' => $mobileCollection, 'input' => $mobileInput],
        ];
    }

    /** The media occupying a given collection slot, or null. */
    public function mediaAt(string $collection, int $index)
    {
        if (!$this->owner || !method_exists($this->owner, 'gallerySlot')) {
            return null;
        }
        return $this->owner->gallerySlot($collection, $index);
    }

    public function render()
    {
        return view('components.media-gallery-form');
    }
}
