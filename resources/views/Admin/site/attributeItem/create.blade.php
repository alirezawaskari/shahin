@extends('Admin.master')

@section('css')

@stop

@section('content')
    @if(!empty($wizard_category))
        @include('Admin.productWizard.partials.steps',['current'=>'attributes','kind'=>'product'])
    @endif
    <div class="card">
        <div class="card-body">
            <div class="x_panel">
                <div class="x_title">
                    <h4>ثبت آیتم های ویژگی:
                        <span class="colGreen"> {{\App\Helpers\Helper::attribute($attribute_id,'title')}}</span>
                    </h4>
                </div>
                <div class="col-md-3">
                    @include('layout.errors')
                </div>
                <form class="form-horizontal" method="post"
                      action="{{route('attributeItem.store')}}" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <input type="hidden" name="attribute_id" value="{{$attribute_id}}">
                    @if(!empty($wizard_category))
                        <input type="hidden" name="wizard" value="1">
                        <input type="hidden" name="wizard_category" value="{{$wizard_category}}">
                    @endif
                    <input type="hidden" name="attribute_type"
                           value="{{\App\Helpers\Helper::attribute($attribute_id,'type')}}">
                    <div class="row">
                        @if(\App\Helpers\Helper::attribute($attribute_id,'type') == 'color')
                            <div class="col-12 col-md-12 mb-3">
                                <label for="name_lbl">رنگ <span class="required">*</span> :</label>
                                <select class="selectMulti form-control" name="color_id[]" multiple="multiple">
                                    @foreach($colors as $color)
                                        <option value="{{$color->id}}">{{$color->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if(\App\Helpers\Helper::attribute($attribute_id,'type') != 'color')
                                <br>
                                <br>
                            <div class="col-12 col-md-12 mb-3">
                                <table class="table" id="attributeTitle">
                                    <tr>
                                        <th>عنوان</th>
                                        <th>اقدام</th>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="item[0][title]"
                                                   placeholder="لطفا عنوان را وارد کنید" class="form-control"/>
                                        </td>
                                        <td>
                                            <button type="button" name="add" id="add_attribute_title"
                                                    class="btn btn-success"><i
                                                        class="fa fa-plus"></i></button>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        @endif
                    </div>


                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            @if(!empty($wizard_category))
                                <a href="{{route('productWizard.attributes',$wizard_category)}}" class="btn btn-warning">بازگشت</a>
                            @endif
                            <button type="submit" class="btn btn-success">ارسال</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

@stop
@section('js')
    <script>
        var k = 0;
        $("#add_attribute_title").click(function () {
            ++k;
            $("#attributeTitle").append('<tr><td><input type="text" name="item[' + k + '][title]" placeholder="لطفا عنوان را وارد کنید" class="form-control" /></td><td><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-remove"></i></button></td></tr>');
        });
    </script>
@stop
