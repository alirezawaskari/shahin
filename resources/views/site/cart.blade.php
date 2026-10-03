@extends('site.master')

@section('title')
    سبد خرید
@endsection

@section('content')

    <div class="contract">
        {{-- banner --}}
        @component('site.layouts.breadcrumb',['title'=>'سبد خرید','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{-- faq --}}
        <div class="contract-section py-5 bg-gray">
            <div class="container">

                <div class="bg-white rounded shadow row py-4 custom-box">
					<p class="p-2">توجه:</p>
					<p class="p-2"> ارائه هرگونه مدارک و وجه نقد یا پرداخت هزینه به کارکنان مستاجرین و شرکای تجاری خدماتی غیرقانونی تلقی میگردد خواهشمند است درصورت مشاهده و درخواست وجه نقد یا هزینه مازاد بر فاکتور اصلی مراتب  را به شماره تماس ذیل اعلام نمایید.</p>
					<p class="text-center p-1 font-size-14">شماره تماس: 09366342145 </p>
                    @if(session()->get('cart.total.count') > 0)
                        <div class="col-12 p-0 p-md-4">
                            <table class="table border">
                                <thead>
                                <tr>
                                    <th scope="col">عنوان خدمت</th>
                                    <th scope="col">قیمت</th>
                                    <th scope="col">حذف</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach(session()->get('cart.items') as $item)
                                    <tr>
                                        <td>{{ $item['type'] == 'service' ? \App\Models\Service::find($item['id'])->title : \App\Models\FinancialPlansType::find($item['financial_plans_type_id'])->title. '-' .\App\Models\Service::find($item['service_id'])->factor_title }}</td>
                                        <td>{{ number_format($item['price']) . ' ریال' }}
                                            {{ $item['price'] == 0 ? '(هدیه)' : '' }}</td>
                                        <td><i class="fas fa-trash-alt font-25 text-danger pointer delete_cart pointer" data-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}"></i>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>


                        <div class="col-12">
                            <div class="row px-4">
                                <div class="col-12 col-md-6 pr-0">
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="row border">
                                        <div class="col-12 d-flex justify-content-between align-items-center py-3">
                                            <span>جمع کل فاکتور:</span>
                                            <span>{{ number_format(session()->get('cart.total.price')) }} ریال</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-12 d-flex justify-content-between align-items-center flex-md-row flex-column-reverse  p-md-4">
                            <a href="/" class="btn btn-secondary px-5">بازگشت به صفحه اصلی</a>
                            <form action="{{ route('factor.create') }}" method="GET">
                                <button type="submit" class="btn btn-success px-5 my-2 my-md-0">
                                    <i class="fas fa-angle-double-left"></i>
                                    تکمیل دریافت خدمت
                                </button>
                            </form>
                        </div>

                    @else
                        <div class="col-12 p-0 p-md-4 text-center">
                            <p class="text-center font-20 text-black-50 mb-4">سبد خرید شما خالی است!</p>
                            <a href="/" class="btn btn-secondary px-5">بازگشت به صفحه اصلی</a>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection
