<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserCreditDetailController extends Controller
{
    public function store(Request $request , User $user)
    {
        if(auth()->user()->isAbleTo('edit-user-credit'))
        {
            $this->validate(request(), [
                'price' => 'required',
                'description' => 'required|',
                'type' => 'required',
            ]);
            $price = (float) str_replace(',', '', $request->price);
            $user->creditDetails()->create([
                'price' => $price,
                'type' => $request->type,
                'description' => $request->description,
                'registrar_id' => auth()->user()->id,
            ]);
            $credit = ($request->type == 'increase' ? $user->credit + $price : $user->credit - $price);
            $user->update(['credit' => $credit]);

            return redirect()->back()->with([
                'create' => 'success'
            ]);

        }
        return abort(401);
    }
}
