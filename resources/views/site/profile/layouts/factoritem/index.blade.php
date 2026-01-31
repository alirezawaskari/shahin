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
                                    <th scope="col" class="text-center">تاریخ</th>
                                    <th scope="col" class="text-center">وضعیت</th>
                                    <th scope="col" class="text-center">اقدام</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($factor_items as $factor_item)
                                    <tr>
                                                <td class="text-center">
                                                        {{ $factor_item->factoritemable ? $factor_item->factoritemable->title : 'ثبت نشده'}}
                                                        <br>
                                                </td>
                                                <td class="text-center">
                                                    {{ $factor_item->type ? 'اقساط' : 'نقد'}}
                                                </td>
                                                <td class="text-center">{{ verta($factor_item->created_at)->format('%d %B ، %Y') }}</td>
                                                <td class="text-center">
                                                    {{ $factor_item::statusFactor($factor_item->status_factor) }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="/factorItem/{{$factor_item->resnumber}}" class="btn btn-success py-1">مشاهده</a>
                                                    <a href="/checkout/{{$factor_item->factor->resnumber}}" class="btn btn-primary py-1">بارگذاری مدارک </a>
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
