<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShortLinkCollection;
use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $short_links = ShortLink::get();
        return view('Admin.shortLink.index', compact('short_links'));
    }

    /**
     * redirect to original link
     */
    public function redirect($short_link)
    {
        $baseUrl = $this->getBaseUrl();
        $link = ShortLink::where('short_link', $baseUrl . '/l/' . $short_link)->first();
        if ($link) {
            return redirect()->to('http://' . $link->original_link);
        } else {
            abort(404);
        }
    }

    /**
     *filter
     */
    public function filter(Request $request)
    {
        $short_links = ShortLink::latest();
        if (isset($request->short_link) && $request->short_link != '') {
            $short_links->where('short_link', 'LIKE', "%{$request->short_link}%");
        }
        if (isset($request->original_link) && $request->original_link != '') {
            $short_links->where('original_link', 'LIKE', "%{$request->original_link}%");
        }
        return new ShortLinkCollection($short_links->paginate(15));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $baseUrl = $this->getBaseUrl();
        do {
            $random = Str::random(4);
            $short_url = "{$baseUrl}/l/{$random}";
            $existing = ShortLink::where('short_link', $short_url)->first();
        } while ($existing);

        $original_link = $this->getOriginalLink($request);
        $shortLink = ShortLink::where('original_link', $original_link)->first();
        if (is_null($shortLink)) {
            $shortLink = new ShortLink;
            $shortLink->original_link = $original_link;
            $shortLink->short_link = $short_url;
            $shortLink->save();
        }
        return response([
            'status' => 'success',
            'message' => 'لینک با موفقیت ایجاد گردید .',
            'short_link' => $shortLink->short_link,
            'original_link' => $shortLink->original_link
        ]);
    }


    /**
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Foundation\Application|string|string[]
     */
    public function getBaseUrl(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string|array|\Illuminate\Foundation\Application
    {
        $url = url('/');
        $baseUrl = preg_replace('#^https?://#i', '', $url);
        return $baseUrl;
    }

    /**
     * remove http:// and https:// from original link
     * @param Request $request
     * @return array|string|string[]|null
     */
    public function getOriginalLink(Request $request): string|array|null
    {
        $original_link = $request->input('original_link');
        $original_link = preg_replace('#^https?://#i', '', $original_link);

        return $original_link;
    }
}
