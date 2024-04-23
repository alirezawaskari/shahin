<?php

namespace App\Http\Controllers\Admin;

use App\Exports\totalFactorItemInstallmentsExport;
use App\Helpers\CalculationHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CheckResource;
use App\Http\Resources\TotalFactorItemInstallmentCollection;
use App\Models\Category;
use App\Models\FinancialPlansType;
use App\Models\Installment;
use App\Models\Service;
use App\Models\TotalFactorItemInstallment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TotalFactorItemInstallmentController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        if (auth()->user()->hasPermission('file-report-installments')) {
            return view('Admin.totalfactoriteminstallment.all');
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @return TotalFactorItemInstallmentCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        $installments = TotalFactorItemInstallment::whereDate('date', '<', Carbon::now()->toDateString())->where('status', '0')->orderBy('date', 'desc')->filter();

        if ($request->exel == 'on') {
            return (new totalFactorItemInstallmentsExport($installments))->download('totalFactorItemInstallments.xlsx');
        }
        return new TotalFactorItemInstallmentCollection($installments, $installments->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function showCalculate()
    {
        $services = Service::where('status' , true)->get();
        $product_categories = Category::where('parent_id', 1)->where('isActive', 1)->get();
        $financial_plans_types = FinancialPlansType::where('status', true)->where('payment_type', '!=', 'cash')->get();


		return view('Admin.totalfactoriteminstallment.calculate', compact('financial_plans_types','services','product_categories'));
	}
    /**
     * @param Request $request
     * @return mixed
     */
	public function calculate(Request $request)
	{
		$inputs = $request->all();
         if ($inputs['service']=='none'){
            $inputs['category'] = Category::find($inputs['product_category']);
        } else {
            $inputs['category'] = Service::find($inputs['service']);
        }
        $inputs['prepayment_percent'] = self::getPrepaymentPercent($inputs);
		if ($request->type == 'calculateByTotalPrice') {
			if ((float) str_replace(',', '', Helper::numberConverter($inputs['total_price'])) > $inputs['category']->installments->max('high_interval')) {
				$message = 'مبلغ وارد شده بیش از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
            if ((float) str_replace(',', '', Helper::numberConverter($inputs['total_price'])) < $inputs['category']->installments->min('down_interval')) {
				$message = 'مبلغ وارد شده کمتر از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
			$item = $this->calculateByTotalPrice($inputs);
		}
		elseif ($request->type == 'calculateByPrepayment') {
			if ((float) str_replace(',', '', Helper::numberConverter($inputs['prepayment'])) > $inputs['category']->installments->max('high_interval')) {
				$message = 'مبلغ وارد شده بیش از حد مجاز است.';
				return response(['message' => $message, 'success' => '']);
			}
			$item = $this->calculateByPrepayment($inputs);
		}
		elseif ($request->type == 'calculateByInstallment') {
			$item = $this->calculateByInstallment($inputs);
            if ((float) str_replace(',', '', Helper::numberConverter($item['installments_price'])) < $inputs['category']->installments->min('down_installment_price')) {
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
     * calculate prepaymet_price && installment_price && installment_num by totalPrice input
     */
	private function calculateByTotalPrice($inputs)
	{

		$total_price = (float) str_replace(',', '', Helper::numberConverter($inputs['total_price']));
 		$prepayment = $total_price * ($inputs['prepayment_percent']);
        $inputs['installments_num'] = self::getInstallmentNum($inputs , $total_price);
        $wage = ($total_price - $prepayment) * ($inputs['category']->wage_percent / 100) / 12 * $inputs['installments_num'];
        $tax = CalculationHelper::calculateTax($wage);
        $loan = CalculationHelper::calculateLoan($total_price , $prepayment , $wage , $tax);
        $installment_price = CalculationHelper::calculateInstallmentsPrice(1 , $loan , $inputs['installments_num']);
		$item = [
			'total_price' => number_format($total_price),
			'installments_price' => number_format($installment_price),
			'installments_num' => number_format($inputs['installments_num']),
			'prepayment' => number_format($prepayment),
			'loan' => number_format($loan),
			'guarantee' => number_format($loan * 1.4),
		];
		return $item;
	}
    /**
     * @param $inputs
     * @return array
     * calculate total_price && installment_price && installment_num by prepayment_price input
     */
	private function calculateByPrepayment($inputs)
	{
		$prepayment = (float) str_replace(',', '', Helper::numberConverter($inputs['prepayment']));
 		$total_price = ($prepayment * ((100 - $inputs['prepayment_percent']))) / ($inputs['prepayment_percent']) + $prepayment;
		if ($total_price > $inputs['category']->installments->max('high_interval')) {
			$total_price = $inputs['category']->installments->max('high_interval');
		}
        $inputs['installments_num'] = self::getInstallmentNum($inputs , $total_price);
		$wage = ($total_price - $prepayment) * ($inputs['category']->wage_percent / 100) / 12 * $inputs['installments_num'];
        $tax = CalculationHelper::calculateTax($wage);
        $loan = CalculationHelper::calculateLoan($total_price , $prepayment , $wage , $tax);
        $installment_price = CalculationHelper::calculateInstallmentsPrice(1 , $loan , $inputs['installments_num']);
		$item = [
			'total_price' => number_format($total_price),
			'installments_price' => number_format($installment_price),
			'installments_num' => number_format($inputs['installments_num']),
			'prepayment' => number_format($prepayment),
			'loan' => number_format($loan),
			'guarantee' => number_format($loan * 1.4),
		];
		return $item;
	}
    /**
     * @param $inputs
     * @return array
     * calculate total_price && prepayment_price by installment_price && installment_num inputs
     */
	private function calculateByInstallment($inputs)
	{
		$installments_price = (float) str_replace(',', '', Helper::numberConverter($inputs['installments_price']));
 		$installments = $inputs['category']->installments;
		$installments_num = $installments->max('high_installment_price') < $installments_price ? $installments->max('num') : 1;
		foreach ($installments as $installment) {
			if ($installment->down_installment_price <= $installments_price && $installments_price < $installment->high_installment_price) {
				$installments_num = $installment->num;
			}
		}
		if ($inputs['installments_num'] && $inputs['installments_num'] != 1 && $installments_num > $inputs['installments_num']) {
			$installments_num = $inputs['installments_num'];
		}
        $financial_plans_type = FinancialPlansType::find($inputs['financial_plans_type']);
        if ($inputs['financial_plans_type'] && $inputs['financial_plans_type'] != 'all' && $financial_plans_type->number_pay_steps) {
            $installments_num = $financial_plans_type->number_pay_steps;
        }

		$loan = $installments_price * $installments_num;
		$wage = (($inputs['category']->wage_percent / 100) / 12 * $installments_num * $loan) / ((1.09 * ($inputs['category']->wage_percent / 100) / 12 * $installments_num) + 1);
        $tax = CalculationHelper::calculateTax($wage);
		$temp = $loan - $wage - $tax;
		$prepayment = ($temp * ($inputs['prepayment_percent'])) / ((100 - ($inputs['prepayment_percent']*100)));
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
    /**
     * @param $inputs
     * @return float
     * get perepayment percent by financial_plans_type input
     */
    private function getPrepaymentPercent($inputs)
    {

        if($inputs['financial_plans_type'] && $inputs['financial_plans_type'] != 'all')
        {
            return FinancialPlansType::find($inputs['financial_plans_type'])->prepayment_percent/100;
        }
        return $inputs['category']->percent/100;
    }
    /**
     * @param $inputs
     * @param $total_price
     * @return int
     * get installment_num by financial_plans_type input if the set installment_num
     */
    private function getInstallmentNum($inputs , $total_price)
    {
        $financial_plans_type = FinancialPlansType::find($inputs['financial_plans_type']);
        if ($inputs['financial_plans_type'] && $inputs['financial_plans_type'] != 'all' && $financial_plans_type->number_pay_steps) {
            return $financial_plans_type->number_pay_steps;
        }

        return Installment::getInstallmentNum($total_price, $inputs['category']);
    }

}
