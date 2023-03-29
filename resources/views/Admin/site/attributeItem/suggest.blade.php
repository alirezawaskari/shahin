@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست ویژگی درخواست داده شده</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> ویژگی</li>
                </ol>
            </nav>
        </div>

    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>شناسه</th>
                    <th>ویژگی</th>
                    <th>آیتم ویژگی</th>
                    <th>دسته بندی</th>
                    <th>وضعیت </th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($attribute_items as $item)
                    <tr>
                        <td>{{$item->id}} </td>
                        <td>{{$item->attribute_name}} </td>
                        <td>{{$item->title}} </td>
                        <td>{{ $item->category_title}}</td>
                        <td> {!! \App\Helpers\Helper::iconIsItOrNot($item->status) !!} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('attributeItem.edit',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                    <form method="post" action="{{route('attributeItem.destroy',[$item->id])}}">
                                        {{ method_field('DELETE') }}  {{csrf_field()}}
                                        <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$attribute_items->links()}}
        </div>
    </div>


@stop
@section('js')

    <script>
        $(document).ready(function() {
            $('.selectSearch').select2();
        });
    </script>
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
