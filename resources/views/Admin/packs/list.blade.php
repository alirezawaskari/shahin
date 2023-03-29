@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>پک</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> لیست پک</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('pack.create')}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>عکس</th>
                    <th>عنوان</th>
                    <th>قیمت</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($packs as $ind=>$item)
                    <tr>
                        <td>{{++$ind}}</td>
                        <td>
                            @if(File::exists(public_path() .$item->image))
                                <img src="{{$item->image}}" class="border-radius5" style="width: 80px;">
                            @endif
                        </td>
                        <td>{{$item->title}}</td>
                        <td>{{number_format($item->price)}}</td>
                        <td>
                            @include('Admin.modal.packProducts',['product_id'=>$item->id,'products'=>$item->products ])
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('pack.edit',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                    @if(isset($item->products))
                                        <a>
                                            <button type="button" class="dropdown-item pointer" data-toggle="modal"
                                                    data-target="#packProducts{{$item->id}}">محصولات
                                            </button>
                                        </a>
                                    @endif
                                    <form method="post" action="{{route('pack.destroy',[$item->id])}}">
                                        @method('DELETE')
                                        @csrf
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

        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop