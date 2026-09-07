<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A folder in the contract bank (پوشه قرارداد).
 *
 * Folders are one level deep in the UI today, but `parent_id` keeps nesting open
 * without another migration.
 */
class ContractFolder extends Model
{
    use SoftDeletes;

    protected $fillable = ['parent_id', 'title', 'description', 'position', 'is_active'];

    protected $casts = [
        'parent_id' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'contract_folder_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Ordered options for the folder selects, with children indented under
     * their parent so a nested folder is readable in a flat <select>.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $folders = static::active()
            ->orderBy('position')
            ->orderBy('title')
            ->get();

        $options = [];

        foreach ($folders->whereNull('parent_id') as $root) {
            $options[$root->id] = $root->title;

            foreach ($folders->where('parent_id', $root->id) as $child) {
                $options[$child->id] = '— ' . $child->title;
            }
        }

        // Folders whose parent is inactive or soft-deleted would otherwise vanish.
        foreach ($folders as $folder) {
            if (! isset($options[$folder->id])) {
                $options[$folder->id] = $folder->title;
            }
        }

        return $options;
    }
}
