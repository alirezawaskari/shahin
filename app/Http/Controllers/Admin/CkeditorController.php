<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Ckeditor;
use Illuminate\Http\Request;

class CkeditorController extends Controller
{
    /**
     * CKEditor image upload. Each upload becomes one Ckeditor row that owns the
     * (optimized) file in its Spatie 'file' collection — stored under
     * storage/app/public/ckeditor/... and served publicly via the storage symlink.
     *
     * Works for BOTH CKEditor 4 upload transports:
     *  - filebrowserUploadMethod = 'form'  -> hidden-iframe POST, expects a
     *    <script>window.parent.CKEDITOR.tools.callFunction(...)</script> callback.
     *  - filebrowserUploadMethod = 'xhr' / uploadimage plugin -> expects JSON
     *    {uploaded:1, fileName, url} (or {uploaded:0, error:{message}}).
     *
     * The transport is detected from the X-Requested-With header so a single
     * endpoint serves every editor regardless of its configured method, and every
     * failure returns a visible message instead of a silent empty response.
     */
    public function upload(Request $request)
    {
        $wantsJson = $request->ajax() || $request->wantsJson()
            || strtolower($request->header('X-Requested-With', '')) === 'xmlhttprequest';

        $funcNum = $request->input('CKEditorFuncNum');

        if (!$request->hasFile('upload')) {
            return $this->ckResponse($wantsJson, $funcNum, '', 'فایلی برای آپلود ارسال نشد.', false);
        }

        $file = $request->file('upload');
        if (!$file->isValid()) {
            // Usually PHP dropped the file because it exceeded upload_max_filesize / post_max_size.
            return $this->ckResponse($wantsJson, $funcNum, '', 'آپلود فایل ناموفق بود (احتمالاً حجم فایل بیش از حد مجاز است).', false);
        }

        $ckeditor = Ckeditor::create();
        $media = MediaLibraryHelper::attachUpload($ckeditor, $file, 'file');

        if (!$media) {
            $ckeditor->delete();
            return $this->ckResponse($wantsJson, $funcNum, '', 'ذخیره فایل ناموفق بود.', false);
        }

        return $this->ckResponse($wantsJson, $funcNum, $media->getUrl(), 'آپلود با موفقیت انجام شد', true);
    }

    /**
     * Build the transport-appropriate CKEditor upload response.
     */
    private function ckResponse(bool $wantsJson, $funcNum, string $url, string $message, bool $ok)
    {
        if ($wantsJson) {
            if ($ok) {
                return response()->json([
                    'uploaded' => 1,
                    'fileName' => basename(parse_url($url, PHP_URL_PATH) ?: $url),
                    'url'      => $url,
                ]);
            }

            return response()->json([
                'uploaded' => 0,
                'error'    => ['message' => $message],
            ]);
        }

        // Classic hidden-iframe (form) transport: run the CKEditor callback in the parent window.
        $u = addslashes($url);
        $m = addslashes($message);

        return response(
            "<script>window.parent.CKEDITOR.tools.callFunction($funcNum, '$u', '$m');</script>"
        )->header('Content-Type', 'text/html; charset=utf-8');
    }
}
