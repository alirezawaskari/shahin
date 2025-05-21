<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $data = $request->validate([
            'center_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image',
            'status' => 'required|boolean',
            'worksampleable_type' => 'required|string',
            'worksampleable_id' => 'required|integer',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('upload/worksamples', 'public');
        }

        WorkSample::create($data);
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

    public function update(Request $request, $id)
    {
        $workSample = WorkSample::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:0,1',
            'center_id' => 'required|exists:centers,id',
            'worksampleable_type' => 'required|string',
            'worksampleable_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $data = $request->only('title', 'status', 'center_id', 'worksampleable_type', 'worksampleable_id');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('work_samples', 'public');
            $data['image'] = '/storage/' . $imagePath;
        }

        $workSample->update($data);

        return redirect()->route('workSample.index')->with('success', 'نمونه‌کار با موفقیت بروزرسانی شد.');
    }

//    public function destroy($id)
//    {
//        $workSample = WorkSample::findOrFail($id);
//
//        // حذف تصویر در صورت وجود
//        if ($workSample->image && file_exists(public_path($workSample->image))) {
//            unlink(public_path($workSample->image));
//        }
//
//        $workSample->delete();
//
//        return redirect()->route('workSample.index')->with('success', 'نمونه‌کار با موفقیت حذف شد.');
//    }

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
