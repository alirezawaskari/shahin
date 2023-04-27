<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ServiceTariffExport;
use App\Models\ServiceTariff;
use App\Http\Resources\ServiceTariffCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\FactorItem;
use App\Models\Tariff;

class ServiceTariffController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $service_tariffs = ServiceTariff::all();
        //$service_tariffs->where('type' , '!=' , 2)->delete();
        if(auth()->user()->center)
        {
            $service_tariffs = ServiceTariff::where('center_id' , auth()->user()->center->id)->get();
        }
		return view('Admin.servicetariff.all' , compact('service_tariffs'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $centers = Center::where('status' , true)->get();
        $tariffs = Tariff::all();
        if(auth()->user()->center)
        {
            $centers = Center::where('id' , auth()->user()->center_id)->get();
        }
        return view('Admin.servicetariff.create' , compact(['centers' , 'tariffs']));
    }

    /**
     *
     */
    public function show()
    {
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required',
            'price' => 'required',
        ]);

        $inputs = $request->all();

        for($i = 0 ; $i < count( $inputs['name'] ) ; $i++)
        {
            $inputs['price'][$i] = (float) str_replace(',', '', $inputs['price'][$i]);
            $service_tariff = ServiceTariff::create([
                'name' => $inputs['name'][$i],
                'price' => $inputs['price'][$i],
                'session_number' => $inputs['session_number'][$i],
                'treatment_duration' => $inputs['treatment_duration'][$i],
                'tariff_id' => $inputs[ 'tariff' ][ $i ],
            ]);
            $service_tariff->centers()->attach($request->center);
        }

        return redirect(url('dashboard/center/'.$request->center.'/serviceTariff'))->with([
            'cereate' => 'success',
        ]);
    }

    /**
     * @param ServiceTariff $serviceTariff
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(ServiceTariff $serviceTariff ) {
        $service = $serviceTariff->centers->first()->centerable;
        $tariffs = $service->tariffs;
        return view( 'Admin.servicetariff.edit', compact( 'serviceTariff' , 'tariffs') );
    }

    /**
     * @param Request $request
     * @return ServiceTariffCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request ) {
        $service_tariffs = ServiceTariff::filter($request)->latest();
        if (isset($request->excel) && $request->excel == 'on') {
            return (new ServiceTariffExport($service_tariffs))->download('service_tariffs.xlsx');
        }
        return new ServiceTariffCollection( $service_tariffs->paginate( 20 ));
    }

    /**
     * @param Request $request
     * @param ServiceTariff $serviceTariff
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ServiceTariff $serviceTariff)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'price' => 'required',
        ]);

        $inputs = $request->all();
        $inputs['price'] = (float) str_replace(',', '', $request->input('price'));

        $serviceTariff->update($inputs);

        return redirect()->route('center.serviceTariff' , [$serviceTariff->centers[0]->id])->with([
            'update' => 'success',
        ]);
    }

    /**
     * @param ServiceTariff $serviceTariff
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ServiceTariff $serviceTariff)
    {
        $serviceTariff->delete();
        return redirect()->back()->with([
            'slider_delete' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function selectByTariff(Request $request)
    {
       if ($request->ajax()) {
           $id = $request->input('id');
           $factor_item = FactorItem::find($request->factor_item_id);
           if (ServiceTariff::where('tariff_id', $id)->get()->count() > 0) {
               $service_tariffs = $factor_item->center ? $factor_item->center->serviceTariffs()->where('tariff_id' , $id)->get() : [];
               $service_tariffs =  new ServiceTariffCollection($service_tariffs);

               return response()->json(['count' => 'available', 'service_tariffs' => $service_tariffs]);
           }
       }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getSessionNumber(Request $request)
    {
       if ($request->ajax()) {
           $id = $request->input('id');
            if (ServiceTariff::find($id)) {
                $service_tariff = ServiceTariff::find($id);
                return response()->json(['count' => 'available', 'service_tariff' => $service_tariff]);
            }
       }
    }
}
