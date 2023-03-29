@extends('site.profile.master')

@section('content2')

    <div class="col-12 col-md-9 mt-4 mt-md-0">
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="list-wallet" role="tabpanel" aria-labelledby="list-wallet-list">
                <div class="col-12 d-flex justify-content-center flex-column align-items-center">
                    <h5 class="text-center text-black-50 mb-3">موجودی کیف پول شما</h5>
                    <span class="btn btn-orange text-white text-center py-2 x-5 font-18 w-md-25 rounded-20px">{{ number_format($user->wallet) }} ریال</span>
                    <form action="{{ route('wallet-payment') }}" method="post">
                        @csrf
                        <input class="form-control my-3" type="number" name="price" placeholder="مبلغ افزایش موجودی به ریال">
                        <input class="btn btn-success py-2 px-5 text-white text-center" type="submit" value="پرداخت وافزایش موجودی">
                    </form>
                </div>
            </div>
            <div class="mt-5" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
                <div class="table-responsive">
                    <table class="table border">
                        <thead>
                        <tr class="bg-gray">
                            <th scope="col">شناسه</th>
                            <th scope="col">نوع</th>
                            <th scope="col">مبلغ به ریال</th>
                            <th scope="col">وضعیت</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($wallets as $wallet)
                            <tr>
                                <td>{{!is_null($wallet->resnumber) ?  $wallet->resnumber : 'ثبت نشده' }}</td>
                                <td>
                                    شارژ کیف پول
                                </td>
                                <td>{{ number_format($wallet->price) }}</td>
                                <td>
                                    @if($wallet->status == false)
                                        <span class="btn btn-danger py-1 px-2 font-14">ناموفق</span>
                                    @else
                                        <span class="btn btn-success py-1 px-2 font-14">موفق</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(session()->get('wallet-payment')=='success')
        <span id="wallet_success"></span>
    @elseif(session()->get('wallet-payment')=='error')
        <span id="wallet_error"></span>
    @elseif(session()->get('wallet-payment')=='payment')
        <span id="wallet_payment"></span>
    @endif


@endsection
