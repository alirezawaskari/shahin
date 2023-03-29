@extends('site.profile.master')

@section('content2')
    <div class="col-12 col-md-9 mt-4 mt-md-0">
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="list-factors" role="tabpanel"
                 aria-labelledby="list-factors-list">
                <div class="col-12">
                    <h5 class="text-orange mb-4 text-black-50">خدمت های خریداری شده</h5>
                    <div class="table-responsive">
                        <table class="table border">
                            <thead>
                            <tr class="bg-gray">
                                <th scope="col">خدمت</th>
                                <th scope="col">شماره فاکتور</th>
                                <th scope="col">مبلغ به ریال</th>
                                <th scope="col">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($factors as $factor)
                                <tr>
                                    <td>{{ $factor->service()->withTrashed()->first()->title }}</td>
                                    <td>{{!is_null($factor->resnumber) ?  $factor->resnumber : 'ثبت نشده' }}</td>
                                    <td>{{ number_format($factor->price) }} ریال</td>
                                    <td>
                                        @if(!is_null($factor->service()->withTrashed()->first()->after_fields))
                                            <a class="btn btn-primary py-1 px-2 font-14 text-white" href="{{ route('form.edit' , [$factor->resnumber]) }}">اطلاعات</a>
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
@endsection
