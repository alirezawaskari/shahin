<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkSampleRequest;
use App\Http\Requests\UpdateWorkSampleRequest;
use App\Models\Center;
use App\Models\Service;
use App\Models\WorkSample;
use Illuminate\Http\Request;

class WorkSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workSamples = WorkSample::with(['worksampleable', 'center'])->orderBy('id','desc')->paginate(15);
        return view('admin.workSample.index',compact('workSamples'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $worksampleableTypes = [
            'service' => ['class' => Service::class, 'label' => 'خدمت'],
        ];

        return view('Admin.workSample.create', compact('worksampleableTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkSampleRequest $request)
    {
        $data = $request->validated();
        // legacy `image` column is never written — Spatie media is the source of truth
        // and WorkSample::getImageAttribute() falls back to the column for old rows.
        unset($data['image_path'], $data['image']);

        $workSample = WorkSample::create($data);
        MediaLibraryHelper::attachTo($workSample, $request->input('image_path'), 'image');

        alert()->success("موفق",'نمونه کار با موفقیت ذخیره شد');
        return redirect()->route('workSample.index');
    }

//    public function show($id)
//    {
//        $workSample = WorkSample::with('worksampleable', 'center')->findOrFail($id);
//        return view('Admin.workSample.show', compact('workSample'));
//    }

    public function edit($id)
    {
        $workSample = WorkSample::findOrFail($id);
        $worksampleableTypes = [
            ['class' => \App\Models\Service::class, 'label' => 'خدمت'],
        ];

        return view('Admin.workSample.edit', compact('workSample', 'worksampleableTypes'));
    }

    public function update(UpdateWorkSampleRequest $request, $id)
    {
        $workSample = WorkSample::findOrFail($id);

        $data = $request->validated();
        unset($data['image_path'], $data['image']); // see store()

        // update() first: it fires `saved`, which adopts a pending legacy image. Doing it
        // after attachTo would leave the collection non-empty, and adoptLegacyMedia()
        // skips a non-empty collection — the legacy file would never migrate.
        $workSample->update($data);
        MediaLibraryHelper::attachTo($workSample, $request->input('image_path'), 'image');

        return redirect()->route('workSample.index')->with('success', 'نمونه‌کار با موفقیت بروزرسانی شد.');
    }

    /**
     * The index page has always rendered a حذف form for every row, but this action was
     * commented out and the route excluded — so simply opening the list threw
     * RouteNotFoundException. Implemented rather than removing the button.
     *
     * WorkSample uses SoftDeletes, so the image is deliberately KEPT: the old commented
     * version unlinked the file, which would have made a "soft" delete unrecoverable.
     */
    public function destroy($id)
    {
        $workSample = WorkSample::findOrFail($id);
        $workSample->delete();

        return redirect()->route('workSample.index')->with('success', 'نمونه‌کار با موفقیت حذف شد.');
    }

    public function getItemsByType(Request $request)
    {
        $type = $request->input('type');
        $centerId = $request->input('center_id');

        $allowedClasses = [
            Service::class,
        ];

        if (!in_array($type, $allowedClasses)) {
            return response()->json([], 400);
        }

        if ($type === Service::class && $centerId) {
            $center = Center::find($centerId);
            if (!$center || !$center->centerable instanceof Service) {
                return response()->json([]);
            }

            $service = $center->centerable;
            return response()->json([
                ['id' => $service->id, 'title' => $service->title]
            ]);
        }

        $items = $type::select('id', 'title')->get();

        return response()->json($items);
    }

}
