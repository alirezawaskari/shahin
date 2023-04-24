<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Representation;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPerformance;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\WalletTransactionCollection;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class EmployeeController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->isAbleTo('employee-detail')) {
            return view('Admin.employee.all');
        }

        return abort(401);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function sellers()
    {
        $callcenter_roles= Role::where('name','like','%callcenter%')->get();
        $parents=Employee::where('status',1)->get();
            return view('Admin.employee.sellers',compact('callcenter_roles','parents'));
    }


    /**
     * @return EmployeeCollection
     */
    public function filter()
    {
        $employees = Employee::filter()->orderBy('updated_at', 'desc');

        return new EmployeeCollection($employees->paginate(20));
    }


    /**
     * @param Request $request
     * @return EmployeeCollection
     */
    public function sellerFilter(Request $request)
    {
        $employees = Employee::filter()->orderBy('updated_at', 'desc');

        $employees->whereHas('user', function ($query) use($request) {
            $query->whereHas('roles', function ($query) use($request) {
                if (isset($request->roles)){
                    $query->whereIn('id',$request->roles);
                }else{
                    $query->whereIn('name',array('callcenter','digital_superadmin-callcenter','superadmin-callcenter'));
                }
            });
        });

        return new EmployeeCollection($employees->paginate(20));
    }

    /**
     * @param Employee $employee
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Employee $employee)
    {
        $representations = Representation::whereStatus(true)->get();
        $employees=$employee::where('status',1)->get();
        return view('Admin.employee.edit', compact('employee', 'representations','employees'));
    }

    /**
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Employee $employee)
    {

        $user = $employee->user;
        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'password' => 'nullable|string|min:6|confirmed',
            'show_status' => ['required', Rule::in([0, 1])]
        ]);

        $inputs = $request->all();

        $employee_items = $request->employee;

        $employee_items['data_access_date'] = Helper::vertaToMiladi($employee_items['data_access_date']);
        $employee_items['contract_start_date'] = Helper::vertaToMiladi($employee_items['contract_start_date']);
        $employee_items['contract_expiration_date'] = Helper::vertaToMiladi($employee_items['contract_expiration_date']);
        $employee_items['parent_id'] =$request->parent_id == 'null'?null:$request->parent_id;

        $user->employee->update($employee_items);
        unset($inputs['employee']);

        $role_id = $request->input('role_id') || $user->roles->count() == 0 ? $request->input('role_id') : $user->roles[0]->id;
        $employee->update([
            'show_status' => $request->show_status
        ]);

        if ($request->urlimgshenasname) {
            Helper::deleteImage($user->imgshenasname);
            $inputs['imgshenasname'] = Helper::moveImage($request->urlimgshenasname, 'document');
        }
        if ($request->urlimgmeli) {
            Helper::deleteImage($user->imgmeli);
            $inputs['imgmeli'] = Helper::moveImage($request->urlimgmeli, 'document');
        }
        if ($request->urlimgsignature) {
            Helper::deleteImage($user->imgsignature);
            $inputs['imgsignature'] = Helper::moveImage($request->urlimgsignature, 'document');
        }
        if ($request->urlavatar) {
            Helper::deleteImage($user->avatar);
            $inputs['avatar'] = Helper::moveImage($request->urlavatar, 'document');
        }

        unset($inputs['urlimgshenasname']);
        unset($inputs['urlimgsignature']);
        unset($inputs['urlimgmeli']);
        unset($inputs['urlavatar']);
        unset($inputs['role_id']);
        $inputs['wallet'] = (float)str_replace(',', '', $request->wallet);
        if ($inputs['password'] == '') {
            unset($inputs['password']);
            unset($inputs['password_confirmation']);
            $user->update($inputs);
        } else {
            $user->update(array_merge($inputs, ['password' => Hash::make($inputs['password'])]));
        }

        if ($role_id == 0) {
            if ($user->roles->count() != 0) {
                $user->detachRole($user->roles[0]->id);
            }
        } else {
            if ($user->roles->count() != 0) {
                $user->detachRole($user->roles[0]->id);
                $user->attachRole($role_id);
            } else {
                $user->attachRole($role_id);
            }
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param Employee $employee
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function financialReport(Employee $employee)
    {
        $user = $employee->user;
        $data_access_date = $employee->data_access_date;
        $items = WalletTransaction::where('user_id', $user->id)->where('created_at', '>=', $data_access_date)->filter()->latest()->get()->each(function ($item) {
            $item->verta_created_at = verta($item->created_at)->format('Y-m');
        })->groupBy('verta_created_at')->map(function (Collection $item) {
            return [
                'year' => verta($item[0]->created_at)->format('Y'),
                'month' => verta($item[0]->created_at)->format('%B'),
                'amount_received' => WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth())])->whereType('0')->where('user_id', $item[0]->user_id)->sum('price'),
                'item_count' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->distinct('factor_item_id')->count('factor_item_id'),
                'total_price' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('price'),
                'ancillary_costs' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('ancillary_costs'),
                'user_tax' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('tax'),
                'center_income' => WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->whereType('1')->sum('price'),
                'created_at' => $item[0]->created_at->format("Y-m-d"),
            ];
        });

        //all user performance
        $walletTransactions = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->filter()->latest();
        $item = [];
        $item['total_income'] = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->whereStatus('1')->where('user_id', $user->id)->whereType('1')->sum('price');
        $item['amount_received'] = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->whereType('0')->sum('price');
        $item['credit_amount'] = $item['total_income'] - $item['amount_received'];
        $item['total_tax'] = UserPerformance::where('created_at', '>=', $data_access_date)->where('user_id', $user->id)->sum('tax');
        $item['amount_payable'] = $item['total_income'] - $item['amount_received'] - $item['total_tax'];
        $item['shaba_number'] = $user->employee->shaba_number;

        return view('Admin.employee.financialReport', compact('item', 'items', 'user', 'employee'));
    }

    /**
     * @param User $user
     * @param $key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function walletTransaction(User $user, $key)
    {
        return view('Admin.employee.walletTransaction', compact('user', 'key'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return WalletTransactionCollection
     */
    public function walletTransactionFilter(Request $request, User $user)
    {
        $data_access_date = $user->employee->data_access_date;
        //all walletTransactions user
        $wallet_transactions = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->filter()->latest();
        //filter walletTransactions user by created_at value
        if (isset($request->key) && $request->key != 'all') {
            $wallet_transactions = WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($request->key)->startMonth()), Helper::vertaTocarbon(verta($request->key)->endMonth())])->where('user_id', $user->id)->filter()->latest();
        }

        return new WalletTransactionCollection($wallet_transactions, $wallet_transactions->paginate(20));
    }

    /**
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function performanceDetail(User $user, Request $request)
    {
        $data_access_date = $user->employee->data_access_date;

        $user_performances = UserPerformance::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->groupBy('factor_item_id')->filter()->latest()->get();

        if (isset($request->key) && $request->key != 'all') {
            $user_performances = UserPerformance::where('created_at', '>=', $data_access_date)->whereBetween('date', [Helper::vertaTocarbon(verta($request->key)->startMonth()), Helper::vertaTocarbon(verta($request->key)->endMonth())])->where('user_id', $user->id)->groupBy('factor_item_id')->filter()->latest()->get();
        }

        return view('Admin.employee.performanceDetail', compact('user', 'user_performances'));
    }
}
