@extends('Admin.master')

@section('css')
@stop
@section('content')

<div class="page-header">
    <div>
        <h3>لیست آیتم ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">لیست آیتم های مربوط به خدمت
                    <span class="green"> {{ App\Models\Service::serviceById($service->id,'title')}}</span>
                </li>
            </ol>
        </nav>
    </div>
    <div role="group" class="btn-group">
        <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="btn btn-primary dropdown-toggle">اقدامات</button>
        <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
            <a href="{{route('service.serviceItem.create',[$service->slug])}}" class="dropdown-item"> ایجاد </a>
        </div>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>خدمت</th>
                    <th>وضعیت</th>
                    <th>نمایش در سایت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($service_items as $key => $item)
                <tr>
                    <td>
                        {{ $item->title }}
                    </td>
                    <td>
                        {{ $item->service->title }}
                    </td>
                    <td>
                        {!! \App\Helpers\Helper::iconIsItOrNot($item->status) !!}
                    </td>
                    <td>
                        {!! \App\Helpers\Helper::iconIsItOrNot($item->isShow) !!}
                    </td>
                    <td>
                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('serviceItem.edit' , [$item->slug]) }}"
                                    class="dropdown-item pointer">ویرایش
                                </a>
                                <form class="delete_form" method="post"
                                    action="{{ route('serviceItem.destroy',[$item->slug]) }}">
                                    @method('DELETE')
                                    @csrf

                                    <input type="button" class="dropdown-item pointer delete_submit" id="{{ $item->id }}"
                                        value="حذف" />
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @include('Admin.layouts.pagination')
    </div>
</div>

@stop
@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
@stop
