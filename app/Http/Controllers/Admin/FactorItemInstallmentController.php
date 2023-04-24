<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FactorItemInstallment;
use App\Models\Installment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FactorItemInstallmentController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
	{
		// $total = 0;
        // $installments = FactorItemInstallment::whereBetween('date', [Helper::vertaTocarbon(verta()->startMonth()), Helper::vertaTocarbon(verta()->endMonth()->addDays(1))])->get();
        // foreach($installments as $installment)
        // {
        //     if($installment->factoritem->status_mali != 0)
        //     {
        //         $total = $total + $installment->price;
        //     }
        // }
        // return $total;

		if (auth()->user()->admin_representation) {
			$representation = auth()->user()->admin_representation_id;
			$installments = FactorItemInstallment::whereDate('date', '<', Carbon::now()->toDateString())->where('status', '!=', '1')
				->orderBy('date', 'desc')
				->whereHas('factoritem', function ($query) use ($representation) {
					$query->whereHas('factor', function ($query) use ($representation) {
						$query->where('representation_id', $representation);
					});
				});
		} else {
			$installments = FactorItemInstallment::whereDate('date', '<', Carbon::now()->toDateString())->where('status', '!=', '1')->orderBy('date', 'desc');
		}

		$total_price = $installments->sum('price');
		$installments = $installments->get();

		if (auth()->user()->center_id) {
			$installments = $installments->where('center_id', auth()->user()->center_id);
		}

		return view('Admin.factoriteminstallment.all', compact('installments', 'total_price'));
	}

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function showCalculate()
	{
		$services = Service::all();
		return view('Admin.factoriteminstallment.calculate', compact('services'));
	}

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function calculate(Request $request)
	{
		$inputs = $request->all();
		$service = Service::find($inputs['service']);
		if ($request->type == 'calculateByTotalPrice') {
			if ((float) str_replace(',', '', $inputs['total_price']) > $service->installments->max('high_interval')) {
				$message = 'مبلغ وارد شده بیش از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
            if ((float) str_replace(',', '', $inputs['total_price']) < $service->installments->min('down_interval')) {
				$message = 'مبلغ وارد شده کمتر از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
			$item = $this->calculateByTotalPrice($inputs);
		}
		if ($request->type == 'calculateByPrepayment') {
			if ((float) str_replace(',', '', $inputs['prepayment']) > $service->installments->max('high_interval')) {
				$message = 'مبلغ وارد شده بیش از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
            if ((float) str_replace(',', '', $inputs['prepayment']) < $service->installments->max('down_interval')) {
				$message = 'مبلغ وارد شده کمتر از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
			$item = $this->calculateByPrepayment($inputs);
		}
		if ($request->type == 'calculateByInstallment') {
			$item = $this->calculateByInstallment($inputs);
			if ((float) str_replace(',', '', $item['total_price']) > $service->installments->max('high_interval')) {
				$message = 'مبلغ وارد شده بیش از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
            if ((float) str_replace(',', '', $item['total_price']) < $service->installments->max('down_interval')) {
				$message = 'مبلغ وارد شده کمتر از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
		}
		$message = 'اطلاعات با موفقیت دریافت شد.';

		return response(['message' => $message, 'success' => $item]);
	}

    /**
     * @param $inputs
     * @return array
     */
    private function calculateByTotalPrice($inputs)
	{
		$total_price = (float) str_replace(',', '', $inputs['total_price']);
		$service = Service::find($inputs['service']);
		$prepayment = $total_price * ($service->percent / 100);
		$installments_num = Installment::getInstallmentNum($total_price, $service);
		$wage = ($total_price - $prepayment) * ($service->wage_percent / 100) / 12 * $installments_num;
		$tax = $wage * 0.09;
		$loan = $total_price - $prepayment + $wage + $tax;
		$installments_price = $loan / $installments_num;
		$item = [
			'total_price' => number_format($total_price),
			'installments_price' => number_format($installments_price),
			'installments_num' => number_format($installments_num),
			'prepayment' => number_format($prepayment),
			'loan' => number_format($loan),
			'guarantee' => number_format($loan * 1.4),
		];
		return $item;
	}

    /**
     * @param $inputs
     * @return array
     */
    private function calculateByPrepayment($inputs)
	{
		$prepayment = (float) str_replace(',', '', $inputs['prepayment']);
		$service = Service::find($inputs['service']);
		$total_price = ($prepayment * ((100 - $service->percent) / 100)) / ($service->percent / 100) + $prepayment;
		if ($total_price > $service->installments->max('high_interval')) {
			$total_price = $service->installments->max('high_interval');
		}
		$installments_num = Installment::getInstallmentNum($total_price, $service);
		$wage = ($total_price - $prepayment) * ($service->wage_percent / 100) / 12 * $installments_num;
		$tax = $wage * 0.09;
		$loan = $total_price - $prepayment + $wage + $tax;
		$installments_price = $loan / $installments_num;
		$item = [
			'total_price' => number_format($total_price),
			'installments_price' => number_format($installments_price),
			'installments_num' => number_format($installments_num),
			'prepayment' => number_format($prepayment),
			'loan' => number_format($loan),
			'guarantee' => number_format($loan * 1.4),
		];
		return $item;
	}

    /**
     * @param $inputs
     * @return array
     */
    private function calculateByInstallment($inputs)
	{
		$installments_price = (float) str_replace(',', '', $inputs['installments_price']);
		$service_id = $inputs['service'];
		$service = Service::find($service_id);
		$installments = $service->installments;
		$installments_num = $installments->max('high_installment_price') < $installments_price ? $installments->max('num') : 1;
		foreach ($installments as $installment) {
			if ($installment->down_installment_price <= $installments_price && $installments_price < $installment->high_installment_price) {
				$installments_num = $installment->num;
			}
		}
		if ($inputs['installments_num'] && $inputs['installments_num'] != 1 && $installments_num > $inputs['installments_num']) {
			$installments_num = $inputs['installments_num'];
		}
		$loan = $installments_price * $installments_num;
		$wage = (($service->wage_percent / 100) / 12 * $installments_num * $loan) / ((1.09 * ($service->wage_percent / 100) / 12 * $installments_num) + 1);
		$tax = $wage * 0.09;
		$temp = $loan - $wage - $tax;
		$prepayment = ($temp * ($service->percent / 100)) / ((100 - $service->percent) / 100);
		$total_price = $prepayment + $temp;
		$item = [
			'total_price' => number_format($total_price),
			'installments_price' => number_format($installments_price),
			'installments_num' => number_format($installments_num),
			'prepayment' => number_format($prepayment),
			'loan' => number_format($loan),
			'guarantee' => number_format($loan * 1.4),
		];
		return $item;
	}
}
