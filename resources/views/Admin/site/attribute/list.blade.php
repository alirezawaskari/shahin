@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>ویژگی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <span class="green"> {{$attribute_group->title}}</span>
                        مربوط به گروه
                        <span class="green"> {{\App\Models\Category::findCategory($attribute_group->category_id,'title')}}</span>
                    </li>
                </ol>
            </nav>
        </div>

        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('createAttribute',[$attribute_group->id])}}" class="dropdown-item"> ایجاد ویژگی </a>
                <a href="{{route('attributeGroup.index')}}" class="dropdown-item"> بازگشت به گروه های ویژگی </a>
            </div>
        </div>

    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عنوان</th>
                    <th>نوع</th>
                    <th>قابلیت فیلتر</th>
                    <th>قابلیت قیمت گذاری</th>
                    <th>قابلیت نمایش در صفحه محصول تکی</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($attributes as $item)
                    <tr>
                        <td>{{$item->title}} </td>
                        <td>{{$item->type}} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->isFilter) !!} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->is_price) !!} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->is_show) !!} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" 
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    @if($item->type == 'select' || $item->type == 'color')
                                        <a href="{{route('attribute.edit',[$item->id])}}" >
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                    @endif
                                    <a href="{{route('attributeItem.show',[$item->id])}}" >
                                        <button type="button" class="dropdown-item pointer">آیتم های ویژگی</button>
                                    </a>
                                    <form method="post" action="{{route('attribute.destroy',[$item->id])}}">
                                        {{ method_field('DELETE') }}  {{csrf_field()}}
                                        <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                {{$attributes->links()}}
                </tbody>
            </table>
        </div>
    </div>


@stop
@section('js')
@stop