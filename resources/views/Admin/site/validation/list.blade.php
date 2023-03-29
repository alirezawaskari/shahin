@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>اعتبارسنجی محصولات</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> لیست اعتبارسنجی محصولات</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('validation.create')}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عنوان</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($validations as $item)
                    <tr>
                        <td>{{$item->title}} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('validation.edit',[$item->id])}}" >
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>

                                    <form method="post" action="{{route('validation.destroy',[$item->id])}}">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="dropdown-item pointer showConfirmDelete" id="{{ $item->id }}">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$validations->links()}}

        </div>
    </div>
@stop
@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
