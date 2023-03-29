@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست گروه های ویژگی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> گروه های ویژگی</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                @if(isset($queries['category_id']))
                    <a href="{{route('attributeGroup.createByCategory',[$queries['category_id']])}}" class="dropdown-item"> ایجاد </a>
                @else
                    <a href="{{route('attributeGroup.create')}}" class="dropdown-item"> ایجاد </a>
                @endif
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" action="{{route('attributeGroup.index')}}">

                <div class="col-12 col-md-3 mb-3 disInline">
                    <select name="category_id" class="custom-select selectSearch" title="selectSearch">
                        <option value="all">دسته بندی</option>
                        @foreach($last_categories as $last_category)
                            <option value="{{$last_category->id}}"
                                    @if(isset($queries['category_id'])) @if($queries['category_id'] == $last_category->id) selected @endif @endif> {{$last_category->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-3 disInline">
                    <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>شناسه</th>
                    <th>عنوان</th>
                    <th>دسته بندی</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($attribute_groups as $item)
                    <tr>
                        <td>{{$item->id}} </td>
                        <td>{{$item->title}} </td>
                        <td>{{ App\Models\Category::findCategory($item->category_id,'title') }}</td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('attribute.show',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویژگی</button>
                                    </a>
                                    <a href="{{route('attributeGroup.edit',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                    <form method="post" action="{{route('attributeGroup.destroy',[$item->id])}}">
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
            {{$attribute_groups->links()}}
        </div>
    </div>


@stop
@section('js')
@stop