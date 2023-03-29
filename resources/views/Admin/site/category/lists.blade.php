@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>لیست دسته بندی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست دسته بندی</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عنوان</th>
                    <th>عنوان انگلیسی</th>
                    <th>نمایش در سایت</th>
                    <th>قابلیت سفارش</th>
                    <th>قابلیت نمایش در منو</th>
                    <th>عکس</th>
                    <th>آیکون</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($categories as $item)
                    <tr>
                        <td>{{$item->title}} </td>
                        <td>{{$item->en_title}} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->isActive) !!}</td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->displayOrder) !!}</td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot(\App\Models\Menu::hasCatInMenu($item->id,0)) !!} </td>
                        <td>
                            @if($item->image != '' && File::exists($item->image))
                                <img src="{{$item->image}}" width="80px" class="border-radius5">
                            @endif</td>
                        <td>@if($item->icon != '')<img src="{{$item->icon}}" width="80px" class="border-radius5">@endif
                        </td>

                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('category.edit',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                    <a href="{{route('category.subCategory',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">لیست زیر مجموعه</button>
                                    </a>
                                </div>
                            </div>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$categories->links()}}
        </div>
    </div>
@stop

@section('js')
@endsection