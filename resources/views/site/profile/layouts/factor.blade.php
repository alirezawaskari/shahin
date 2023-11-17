@extends('site.profile.master')

@section('content2')
    <div class="col-12 col-md-9 mt-4 mt-md-0">
        <div class="tab-content" id="nav-tabContent">
            <div class="col-12">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="list-home" role="tabpanel"
                         aria-labelledby="list-home-list">

                        <div class="table-responsive">
                            <table class="table border" style="font-family: font;">
                                <thead>
                                <tr class="bg-gray">
                                    <th scope="col" class="text-center">نام خدمت</th>
                                    <th scope="col" class="text-center">نوع پرداخت</th>
                                    <th scope="col" class="text-center">مبلغ به ریال</th>
                                    <th scope="col" class="text-center">تاریخ</th>
                                    <th scope="col" class="text-center">وضعیت</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($factors as $factor)
                                    <tr>
                                        <td class="text-center">
                                            {{ $factor::setItems($factor) }}
                                        </td>
                                        <td class="text-center">
                                            {{ $factor::TYPE[$factor->type] }}
                                        </td>
                                        <td class="text-center">{{ number_format($factor->price) }}</td>
                                        <td class="text-center">{{ verta($factor->created_at)->format('%d %B ، %Y') }}</td>
                                        <td class="text-center">
                                            @if($factor->status == 0 && now() < $factor->expired_at )
                                                <form action="{{ route('factor' , [$factor->resnumber]) }}"
                                                      method="GET">
                                                    <input type="submit"
                                                           class="btn btn-primary text-white py-1 px-2 font-14"
                                                           value="پرداخت">
                                                </form>
                                            @endif
                                            @if($factor->status == 1)
                                                @if($factor->type == 'online')
                                                    <a href="{{ route('factor' , [$factor->resnumber]) }}"
                                                       class="btn btn-success py-1">مشاهده فاکتور</a>
                                                @endif

                                                <a href="{{ route('checkout' , [$factor->resnumber]) }}"
                                                   class="btn btn-primary py-1">بارگذاری مدارک</a>

                                            @endif
                                            @if($factor->status == 2)
                                                <p class="btn btn-danger py-1">عودت فاکتور</p>
                                            @endif
                                            @if($factor->status == 3 || $factor->status == 0 && now() > $factor->expired_at )
                                                <p class="btn btn-secondary py-1">منقضی شده</p>
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
        </div>
    </div>

@endsection
