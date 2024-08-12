<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ServiceTariffExport;
use App\Models\ServiceTariff;
use App\Http\Resources\ServiceTariffCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceIndexTariffCollection;
use App\Models\Service;
use App\Models\ServiceIndexTariff;
use Illuminate\Support\Facades\Validator;

class ServiceIndexTariffController extends Controller
{
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
     * @param Request $request
     * @return ServiceTariffCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request ) {
        $service = Service::find($request->service_id);
        $service_index_tariffs = $service->serviceIndexTariffs()->filter($request)->latest();
        if (isset($request->excel) && $request->excel == 'on') {
            return (new ServiceTariffExport($service_index_tariffs))->download('service_tariffs.xlsx');
        }
        return new ServiceIndexTariffCollection( $service_index_tariffs->paginate( 20 ));
    }
    /**
     * @param mixed $id
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getPurchasePrice($id)
    {
        $service_index_tariff = ServiceIndexTariff::find($id);
        return response(number_format($service_index_tariff->purchase_price));
    }
    public function createOrUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'service_index_tariff' => 'required',
            'purchase_price' => 'required',
        ]);
        if ($validation->fails()){
            return response(['status'=>'error','messages'=>$validation->messages()->all()]);
        }
        $purchase_price = ( float )str_replace(',', '', $request->purchase_price);
        $service_index_tariff = ServiceIndexTariff::find($request->service_index_tariff);
        if($service_index_tariff)
        {
            $service_index_tariff->update(['purchase_price' => $purchase_price]);
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'ویرایش با موفقیت انجام شد .'
            ]);
        }
        else
        {
            ServiceIndexTariff::create([
                'title' => $request->service_index_tariff,
                'purchase_price' => $purchase_price,
                'agent_id' => auth()->user()->id,
                'service_id' => $request->service_id,
            ]);
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'ثبت با موفقیت انجام شد .'
            ]);
        }
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
}
