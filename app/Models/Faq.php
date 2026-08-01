<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Owner of the question. Null `ghorfe_online_list_id` = the main lalecard site, which
     * is what the site /faq page and the main-site "درباره ما" row both read.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ghorfeOnline()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    /**
     * Set instead of ghorfe_online_list_id when the question belongs to one branch's own
     * "سوالات متداول" block rather than to the business as a whole.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id');
    }
}
