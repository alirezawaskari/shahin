@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>لیست اقساط</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست اقساط
                        @if(isset($category))
                            <span>{{$category->title}}</span>
                        @endif
                        @if(isset($service))
                            <span>{{$service->title}}</span>
                        @endif</li>
                </ol>
            </nav>
        </div>

        <a href="{{route('installment.detail.service',['id'=>$service->id])}}"><span class="btn btn-primary">مشاهده ریز محاسبات اقساط </span></a>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                @if(isset($category))
                    <a href="/dashboard/createInstallmentCategory/{{$category->id}}" class="dropdown-item"> ایجاد </a>
                @endif
                @if(isset($service))
                    <a href="/dashboard/createInstallmentService/{{$service->id}}" class="dropdown-item"> ایجاد </a>
                @endif
                @if(isset($parent_id))
                    <a href="/dashboard/category/sub/{{$parent_id}}" class="dropdown-item"> بازگشت به دسته بندی </a>
                @endif
            </div>
        </div>
    </div>


    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>حد پایین</th>
                    <th>حد بالا</th>
                    <th>مبلغ حد پایین</th>
                    <th>مبلغ حد بالا</th>
                    <th>تعداد</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($installments as $item)
                    <tr>
                        <td>{{number_format($item->down_interval)}} </td>
                        <td>{{number_format($item->high_interval)}} </td>
                        <td>{{number_format($item->down_installment_price)}} </td>
                        <td>{{number_format($item->high_installment_price)}} </td>
                        <td>{{$item->num}} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('installment.edit',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$installments->links()}}



        </div>
        <div class="card-body">
            <form class="form-horizontal" method="post" action="{{route('finishInstallment')}}"
              enctype="multipart/form-data">{{csrf_field()}}
            @if(isset($category))
                <input type="hidden" name="category_id" value="{{$id}}" class="category_id">
            @endif
            @if(isset($service))
                <input type="hidden" name="service_id" value="{{$id}}" class="service_id">
            @endif
            <button class="btn btn-success floatLeft colorWhite">تکمیل اقساط</button>

        </form>
        </div>
    </div>


@stop
@section('js')
@stop
