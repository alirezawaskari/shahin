<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ServiceTariffExport;
use App\Helpers\CalculationHelper;
use App\Models\ServiceTariff;
use App\Http\Resources\ServiceTariffCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AllocationEmployeeShareCenter;
use App\Models\Center;
use App\Models\FactorItem;
use App\Models\ServiceIndexTariff;
use App\Models\Tariff;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ServiceTariffController extends Controller {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function index() {
        $service_tariffs = ServiceTariff::all();
        //$service_tariffs->where( 'type', '!=', 2 )->delete();
        if ( auth()->user()->center ) {
            $service_tariffs = ServiceTariff::where( 'center_id', auth()->user()->center->id )->get();
        }
        return view( 'Admin.servicetariff.all', compact( 'service_tariffs' ) );
    }

    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function create() {
        $centers = Center::where( 'status', true )->get();
        $tariffs = Tariff::all();
        if ( auth()->user()->center ) {
            $centers = Center::where( 'id', auth()->user()->center_id )->get();
        }
        return view( 'Admin.servicetariff.create', compact( [ 'centers', 'tariffs' ] ) );
    }

    /**
    *
    */

    public function show() {
    }
    /**
     * Summary of store
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'name' => 'required',
            'tariff' => 'required',
            'purchase_price' => 'required',
            'multi_stage' => [ 'required', Rule::in( array( 1, 2 ) ) ], ] );

            if ( $validate->fails() ) {
                return response( [
                    'status'=>'error',
                    'messages'=>$validate->errors()->all()
                ] );
            }
            $service_index_tariff = ServiceIndexTariff::find( $request->name );
            $inputs = $request->all();
            $purchase_price = ( float ) str_replace( ',', '', $request->purchase_price );
            $selling_price = $request->selling_price ? ( float ) str_replace( ',', '', $request->selling_price ) : 0;
            $center = Center::find( $request->center );
            if ( $service_index_tariff ) {
                $count_selected_service_tariff = $center->serviceTariffs->where( 'service_index_tariff_id', $service_index_tariff->id )->where('tariff_id', $request->tariff)->count();
                if ($count_selected_service_tariff>0) {
                    return response( [
                        'operator' => true,
                        'status'=>'error',
                        'message'=>'شما قبلا این تعرفه را ثبت کرده اید لطفا از قسمت ویرایش اقدام کنید.'
                    ] );
                }
                $service_tariff = ServiceTariff::create( [
                    'name' => $service_index_tariff->title,
                    'session_number' => $request->session_number,
                    'treatment_duration' => $request->treatment_duration,
                    'tariff_id' => $request->tariff,
                    'purchase_price' => $purchase_price,
                    'cash_price' => CalculationHelper::getServiceTariffSellingPrice($selling_price , $purchase_price),
                    'price' => $selling_price,
                    'service_id' => $service_index_tariff->service_id,
                    'service_index_tariff_id' => $service_index_tariff->id,
                    'multi_stage' => $request->multi_stage,
                ] );
                $service_tariff->centers()->attach( $request->center );
                //allocation of center employees shares
                AllocationEmployeeShareCenter::store($inputs,$service_tariff);
                return response( [
                    'operator' => true,
                    'status'=>'success',
                    'message'=>'ثبت با موفقیت انجام شد .'
                ] );
            }
        }

        /**
        * @param ServiceTariff $serviceTariff
        * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
        */

        public function edit( ServiceTariff $serviceTariff ) {
            $tariffs = $serviceTariff->service->tariffs;
            $center = $serviceTariff->centers()->first();
            $employees = $center->users->where( 'level', 'admin' );
            $allocation_employee_share_centers = $serviceTariff->allocationEmployeeShareCenters;
            return view( 'Admin.servicetariff.edit', compact( 'allocation_employee_share_centers', 'center', 'employees', 'serviceTariff', 'tariffs' ) );
        }

        /**
        * @param Request $request
        * @return ServiceTariffCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
        */

        public function filter( Request $request ) {
            $center = Center::find( $request->center_id );
            $last_tariff_id = $center->serviceTariffs()->count()>0 ? $center->serviceTariffs()->where( 'service_id', $request->service_id )->latest()->first()->tariff_id : '';
            $request['last_tariff_id'] = $last_tariff_id;
            $service_tariffs = $center->serviceTariffs()->where( 'service_id', $request->service_id )->filter( $request )->latest();
            $total_service_tariffs = $center->serviceTariffs()->where( 'service_id', $request->service_id )->filter( $request )->latest();
            if ( isset( $request->excel ) && $request->excel == 'on' ) {
                return ( new ServiceTariffExport( $service_tariffs ) )->download( 'service_tariffs.xlsx' );
            }
            return new ServiceTariffCollection($total_service_tariffs , $service_tariffs->paginate( 20 ) );
        }
        /**
         * Summary of update
         * @param Request $request
         * @param ServiceTariff $serviceTariff
         * @return \Illuminate\Http\Response
         */
        public function update( Request $request, ServiceTariff $serviceTariff ) {
            $validate = Validator::make( $request->all(), [
                'multi_stage' => [ 'required', Rule::in( array( 1, 2 ) ) ],
            ] );
            if ( $validate->fails() ) {
                return response( [
                    'status'=>'error',
                    'messages'=>$validate->errors()->all()
                ] );
            }
            $purchase_price = ( float ) str_replace( ',', '', $request->purchase_price );
            $selling_price = $request->selling_price ? ( float ) str_replace( ',', '', $request->selling_price ) : $serviceTariff->consumer_price;
            $inputs = $request->all();
            $serviceTariff->update( [
                'session_number' => $request->session_number,
                'treatment_duration' => $request->treatment_duration,
                'purchase_price' => $purchase_price,
                'price' => $selling_price,
                'cash_price' => CalculationHelper::getServiceTariffSellingPrice($selling_price , $purchase_price),
                'multi_stage' => $request->multi_stage,
            ] );
            //allocation of center employees shares
            AllocationEmployeeShareCenter::store($inputs,$serviceTariff);
            return response( [
                'operator' => true,
                'status'=>'success',
                'message'=>'ویرایش با موفقیت انجام شد .'
            ] );

        }

        /**
        * @param ServiceTariff $serviceTariff
        * @return \Illuminate\Http\RedirectResponse
        */

        public function destroy( ServiceTariff $serviceTariff ) {
            $serviceTariff->delete();
            return redirect()->back()->with( [
                'slider_delete' => 'success'
            ] );
        }

        /**
        * @param Request $request
        * @return \Illuminate\Http\JsonResponse|void
        */

        public function selectByTariff( Request $request ) {
            if ( $request->ajax() ) {
                $id = $request->input( 'id' );
                $factor_item = FactorItem::find( $request->factor_item_id );
                if ( ServiceTariff::where( 'tariff_id', $id )->get()->count() > 0 ) {
                    $service_tariffs = $factor_item->center->serviceTariffs()->where( 'tariff_id', $id )->where('service_id' , $factor_item->factoritemable_id)->get();
                    $total_service_tariffs = $factor_item->center->serviceTariffs()->where( 'tariff_id', $id )->where('service_id' , $factor_item->factoritemable_id)->filter();
                    $service_tariffs =  new ServiceTariffCollection($total_service_tariffs , $service_tariffs );

                    return response()->json( [ 'count' => 'available', 'service_tariffs' => $service_tariffs ] );
                }
            }
        }

        /**
        * @param Request $request
        * @return \Illuminate\Http\JsonResponse|void
        */

        public function getSessionNumber( Request $request ) {
            if ( $request->ajax() ) {
                $id = $request->input( 'id' );
                if ( ServiceTariff::find( $id ) ) {
                    $service_tariff = ServiceTariff::find( $id );
                    $service_tariff->price = number_format($service_tariff->price);
                    return response()->json([
                        'count' => 'available',
                        'service_tariff' => $service_tariff
                    ]);
                }
            }
        }
    }
