@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>لیست قرارداد ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">قرارداد ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary dropdown-toggle">اقدامات</button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="/dashboard/contract/create" class="dropdown-item"> ایجاد  </a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="get" action="/dashboard/contract">
                <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <select title="status" name="status" class="custom-select">
                        <option @if(isset($queries['status'])) @if($queries['status'] == 'all') selected @endif @endif value="all">وضعیت</option>
                        @foreach($statuses as $ind=>$status)
                            <option @if(isset($queries['status'])) @if($queries['status'] == $ind) selected @endif @endif value="{{$ind}}">{{$status}}  </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="type" class="custom-select" title="">
                        <option @if(isset($queries['type'])) @if($queries['type'] == 'all') selected @endif @endif value="all">نوع </option>
                        @foreach($type as $ind=>$ty)
                            <option @if(isset($queries['type'])) @if($queries['type'] == $ind) selected @endif @endif value="{{$ind}}">{{$ty}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>نوع</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contracts as $item)
                        <tr>
                            <td>{{ $item->title }} </td>
                            <td>{{ $item::TYPE[$item->type] }} </td>
                            <td>{{isset($item->status)?$item::STATUS[$item->status]:''}}</td>
                            <td>{{ verta($item->created_at)->format('Y-m-d') }}</td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" target="_blank" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="/dashboard/contract/{{$item->id}}/edit" target="_blank">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                        <a href="/dashboard/contract/{{$item->id}}" target="_blank">
                                            <button type="button" class="dropdown-item pointer">تاریخچه</button>
                                        </a>
                                        <a href="/dashboard/arrangeContractList/{{$item->id}}" target="_blank">
                                            <button type="button" class="dropdown-item pointer">تنظیم شده ها</button>
                                        </a>
                                        <a href="/dashboard/arrangeContract/{{$item->id}}"  target="_blank">
                                            <button type="button" class="dropdown-item pointer">تنظیم</button>
                                        </a>

                                        <form method="post" action="{{route('contract.destroy',[$item->id])}}">
                                            {{ method_field('DELETE') }}
                                            {{csrf_field()}}
                                            <button type="submit" class="dropdown-item pointer"
                                                    onclick="return confirm('آیا مطمئن هستید؟')">حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            {{$contracts->links()}}
        </div>
    </div>

@stop
@section('js')
@stop
