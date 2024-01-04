<?php

namespace App\Http\Controllers\Admin;

use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Verta;
use App\Models\Employee;
use App\Models\Factor;
use App\Helpers\Helper;
use Carbon\Carbon;

class WalletTransactionController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $transactions = WalletTransaction::where('reason_id' , null)->where('type' , 0)->where('user_id' , '!=' , 28120)->latest()->get();
        return view('Admin.wallet.all' , compact('transactions'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show()
    {
        $transactions = WalletTransaction::where('status' , 2)->latest()->paginate(20);
        return view('Admin.wallet.all' , compact('transactions'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Employee::find($request->input('employee_id'))->user;
        $factor = new Factor();
        $factor->title = 'employee_payment';
        $factor->user_id = $user->id;
        $factor->price = (float) str_replace(',', '', Helper::numberConverter($request->input('price')));
        $factor->type = $request->input('payment_type');
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input('number_card');
        $factor->payment_code = $request->input('payment_code');
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->status = 5;
        $factor->created_at = Helper::vertaToMiladi(Helper::numberConverter($request->payment_date));
        $factor->save();

        $resNum = rand(12, 99) . substr(Carbon::now()->timestamp, 6, 11) . substr($user->phone, 7, 11);

        WalletTransaction::create([
            'user_id' => $user->id,
            'resnumber' => $resNum,
            'price' => $factor->price,
            'status' => '1',
            'type' => '0',
            'created_at' => Helper::vertaTocarbon(verta($request->month)->startMonth()),
        ]);

        $user->update(['wallet' => $user->wallet - $factor->price]);

        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image,'document')]);
        }

        return redirect()->back()->with([
            'send' => true
        ]);
    }

    /**
     * @param WalletTransaction $wallet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function wallet_update(WalletTransaction $wallet)
    {
        $wallet->user->update(['wallet' => $wallet->user->wallet +  $wallet->price ]);
        $wallet->update(['status' => 1]);
        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param WalletTransaction $wallet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(WalletTransaction $wallet)
    {
        $wallet->delete();
        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }
}
