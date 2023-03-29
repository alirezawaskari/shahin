@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>تنظیم قرارداد</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> لیست تنظیم قرارداد</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary dropdown-toggle">اقدامات</button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="/dashboard/arrangeContract/{{$contract_id}}" class="dropdown-item"> ایجاد  </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>شماره</th>
                        <th>برای شخص </th>
                        <th>تنظیم توسط </th>
                        <th>وضعیت  </th>
                        <th>تاریخ</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($arrange_contracts as $item)
                        <tr>
                            <td>{{$item->no}}</td>
                            <td>{{\App\Helpers\Helper::user($item->owner_id,'name')}}   {{ \App\Helpers\Helper::user($item->owner_id,'family')}} </td>
                            <td>{{\App\Helpers\Helper::user($item->user_id,'name')}}   {{ \App\Helpers\Helper::user($item->user_id,'family')}} </td>
                            <td>{{$item::STATUS[$item->status]}} </td>
                            <td>{{ verta($item->created_at)->format('H:i:s Y-m-d') }}</td>
                            <td>
                                @permission('arrange-contract' || 'arrange-contract-detail')
                                @if($item->status == 0 && \App\Models\ArrangeContract::ownerArrange($item->id) == true)
                                    <a class="badge badge-info" href="/dashboard/arrangeContract/{{$item->id}}/1">ارجاع به مدیر عامل</a>
                                @endif

                                @if($item->status == 1 && \App\Models\ArrangeContract::ownerArrange($item->id) == true)
                                    <a class="badge badge-info" href="/dashboard/arrangeContract/{{$item->id}}/0">پیش نویس</a>
                                @endif
                                @endpermission

                                @permission('arrange-contract-detail')
                                @if($item->status == 1)
                                    <a class="badge badge-info" href="/dashboard/arrangeContract/{{$item->id}}/edit"> مشاهده</a>
                                @endif
                                @endpermission
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if($arrange_contracts != []){{$arrange_contracts->links()}}@endif
            </div>
        </div>
    </div>


@stop
@section('js')
@stop