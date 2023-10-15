<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShortLinkCollection;
use App\Models\ShortLink;
use Illuminate\Http\Request;

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
        $baseUrl = ShortLink::getBaseUrl();
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
        $shortLink = ShortLink::generate($request->original_link);
        return response([
            'status' => 'success',
            'message' => 'لینک با موفقیت ایجاد گردید .',
            'short_link' => $shortLink->short_link,
            'original_link' => $shortLink->original_link
        ]);
    }

}
