@extends('Admin.master')

@section('content')
    @php
        $in_wizard = (bool) request('wizard');
        $wizard_query = $in_wizard ? ['wizard' => 1] : [];
    @endphp

    <div class="page-header">
        <div>
            <h3>لیست آیتم های ویژگی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        آیتم های ویژگی
                        <span class="colGreen"> {{\App\Helpers\Helper::attribute($attribute->id,'title')}}</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                @if($in_wizard)
                    {{-- داخل ویزارد: ایجاد و بازگشت هر دو در همان مرحله ویژگی‌ها می‌مانند --}}
                    <a href="{{route('createAttributeItem',[$attribute->id,'wizard'=>1,'wizard_category'=>$attribute->category_id])}}" class="dropdown-item"> ایجاد </a>
                    <a href="{{route('productWizard.attributes',$attribute->category_id)}}" class="dropdown-item"> بازگشت به ویژگی های دسته بندی </a>
                @else
                    <a href="{{route('createAttributeItem',[$attribute->id])}}" class="dropdown-item"> ایجاد </a>
                    <a href="{{route('attribute.show',[$attribute->attribute_group_id])}}" class="dropdown-item"> بازگشت به لیست ویژگی
                        های این گروه </a>
                @endif
            </div>
        </div>

    </div>

    @if($in_wizard)
        @include('Admin.productWizard.partials.steps',['current'=>'attributes','kind'=>'product'])
    @endif

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عنوان</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($attribute_items as $item)
                    <tr>
                        <td>{{$item->title}} </td>
                        <td> {!! \App\Helpers\Helper::iconIsItOrNot($item->status) !!} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    @if(\App\Helpers\Helper::attribute($attribute->id ,'type') != 'color')
                                        <a href="{{route('attributeItem.edit',[$item->id] + $wizard_query)}}">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                    @endif
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
            {{$attribute_items->withQueryString()->links()}}

        </div>
    </div>


@stop
@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
