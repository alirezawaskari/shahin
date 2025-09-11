<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlansType;
use App\Models\Service;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('site.cart');
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function fetchCart()
    {
        if (session()->has('cart')) {
            return response(['data' => session()->get('cart')]);
        } else {
            return response(['data' => null]);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $id = $request->id;
        $service_id = $request->service_id;
        $financial_plans_type_id = $request->financial_plans_type_id;
        $type = $request->type;
        $representation = $request->representation;
        $callcenter = $request->callcenter;
        $factor = $request->factor;
        $task_id = $request->task_id;

        //Create First Cart Session
        $cart = [
            'items' => [],
            'total' => [
                'price' => 0,
                'count' => 0,
                'price_tax' => 0,
            ],
            'callcenter' => $callcenter,
            'representation' => $representation,
            'task_id' => $task_id,
            'discount' => 0
        ];
        if (session()->has('cart')) {
            if (session()->get('cart.representation') != $representation) {
                session()->forget('cart');
                session()->put('cart', $cart);
                session()->put('cart.representation', $representation);
                session()->put('cart.callcenter', $callcenter);
                session()->put('cart.task_id', $task_id);
            } else {
                if (session()->get('cart.callcenter') != $callcenter) {
                    session()->put('cart.callcenter', $callcenter);
                    session()->put('cart.task_id', $task_id);
                }
            }
        } else {
            session()->put('cart', $cart);
        }
//Check Item

        if (session()->has("cart.items.{$type}_{$id}")) {
            if (isset($factor['resnumber'])) {
                return response(['operator' => true, 'factor' => $factor['resnumber']]);
            }
            return response(['operator' => true]);
        } else {
            //create

            session()->put("cart.items.{$type}_{$id}", [
                'id' => $id,
                'service_id' => $service_id,
                'financial_plans_type_id' => $financial_plans_type_id,
                'type' => $type,
                'count' => 1,
                'price' => $type == 'service' ? Service::find($id)->price : FinancialPlansType::find($id)->price,
                'tax' => FinancialPlansType::find($id)->tax,
                'price_tax' => ($type == 'service' ? Service::find($id)->price : FinancialPlansType::find($id)->price) * (FinancialPlansType::find($id)->tax / 100),
            ]);
            if ($type == 'service' && !is_null(Service::find($id)->gift)) {
                foreach (Service::find($id)->gift as $item) {
                    session()->put("cart.items.service_{$item}", [
                        'id' => $item,
                        'service_id' => $service_id,
                        'financial_plans_type_id' => $financial_plans_type_id,
                        'type' => 'service',
                        'count' => 1,
                        'price' => Service::find($item)->price,
                    ]);
                }
            }
        }

        $this->calculateTotals();
        if (isset($factor['resnumber'])) {
            return response(['operator' => true, 'factor' => $factor['resnumber']]);
        }
        return response(['operator' => true]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $id = $request->id;
        $type = $request->type;

        session()->forget("cart.items.{$type}_{$id}");
        $this->calculateTotals();

        return response(['operator' => true]);
    }

    /**
     *
     */
    private function calculateTotals()
    {
        $items = collect(session()->get('cart.items'));
        session()->put('cart.total', [
            'count' => $items->sum('count'),
            'price' => $items->sum('price'),
            'price_tax' => $items->sum('price_tax'),
        ]);
    }

}
