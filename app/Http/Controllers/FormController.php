<?php

namespace App\Http\Controllers;

use App\Models\Factor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FormController extends Controller
{

    /**
     * @param $file
     * @return string
     */
    public function upload($file)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $filepath = "/upload/user/{$year}/{$month}/";

        $rand = rand(1, 999);
        $filename = "{$rand}_" . $file->getClientOriginalName();
        $file->move(public_path($filepath), $filename);
        return $filepath . $filename;

    }

    /**
     * @param Factor $factor
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|void
     */
    public function form(Factor $factor)
    {
        if ($factor->status) {
            $factor->load('user', 'service');
            return view('site.form', compact('factor' , 'values'));
        }
        abort(404);
    }


    /**
     * @param Factor $factor
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|void
     */
    public function form_edit(Factor $factor)
    {
        if ($factor->status) {
            $factor->load('user', 'service');
            $values = null;
            if (!is_null($factor->after_values)){
                $values = $factor->after_values;
            }
            return view('site.form_edit', compact('factor' , 'values'));
        }
        abort(404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $factor = Factor::findOrFail($request->input('factor_id'));

        $validate_array = [];
        $values = [];
        foreach ($factor->service->after_fields as $field) {
            if (array_key_exists('validation' , $field)) {
                $validate_array = Arr::add($validate_array, $field['name'], $field['validation']);
            }
            if (Str::contains($field['name'] , 'file-')){
                $file = $request->file('file-pic');
                if ($file){
                    $url = $this->upload($file);
                    $values = Arr::add($values, $field['name'], [
                        'name' => $field['name'],
                        'type' => $field['type'],
                        'value' => $url,
                        'title' => $field['title'],
                    ]);
                }
            }
            $values = Arr::add($values, $field['name'], [
                'name' => $field['name'],
                'type' => $field['type'],
                'value' => $request->input($field['name']),
                'title' => $field['title'],
            ]);
        }
        $this->validate($request, $validate_array);

        $factor->update([
           'after_values' => $values,
        ]);

        return redirect(route('index'))->with([
            'factor' => 'success'
        ]);
    }
}
