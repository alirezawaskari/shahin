@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ثبت اطلاعات دعوت از دوستان</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('inviteCategory.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-5">
                        <label>درصد *</label>
                        <input type="text" class="form-control" placeholder="لطفا درصد را وارد کنید"
                               name="percent" value="{{old('percent')}}">
                        @if($errors->has('percent'))
                            <span class="help-block sub-error"> {{$errors->first('percent')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <input class="services_selected" type="radio"
                               name="invitecategoryable" title="خدمات"
                               value="service"><span class="lblRadioBtn font-weight-bold font16">خدمات </span>
                        <input class="categories_selected" type="radio"
                               name="invitecategoryable" title="دسته بندی"
                               value="category"><span class="lblRadioBtn font-weight-bold font16">دسته بندی </span>
                        <input class="products_selected" type="radio"
                               name="invitecategoryable" title="محصولات"
                               value="product"><span class="lblRadioBtn font-weight-bold font16">محصولات </span>
                    </div>
                    <div class="col-10 col-md-12 mb-5 services_box" style="display: none;">
                        <label>خدمات </label>
                        <select name="service_ids[]" class="selectMulti form-control" multiple="multiple">
                            @foreach($services as $service)
                                <option value="{{$service->id}}">{{$service->title}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-10 col-md-12 mb-5 categories_box" style="display: none;">
                        <label>دسته بندی </label>
                        <select name="category_ids[]" class="selectMulti form-control" multiple="multiple">
                            @foreach($categories as $category)
                                <option value="{{$category->id}}">{{$category->title}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-10 col-md-12 mb-5 products_box" style="display: none;">
                        <br/>
                        <div class="productSelected mb-5">
                            <span>لیست محصولات انتخاب شده :</span>
                        </div>

                        <div class="col-6 col-md-6 mb-5 frmSearch padZ">
                            <label>لطفا نام محصول را کامل وارد نمایید</label>
                            <input type="text" id="search-box" class="form-control" placeholder="بیش از سه حرف وارد کنید .."
                                   autocomplete="off"/>
                            <div id="suggesstion-box"></div>
                        </div>

                        <div class="resultProductLists">

                        </div>
                    </div>


                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('inviteCategory.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>


        </div>
    </div>
@stop


@section('css')

@stop

@section('js')
    <script>
        $('.products_selected').click(function () {
            $('.products_box').css({'display':'block'});
            $('.categories_box').css({'display':'none'});
            $('.services_box').css({'display':'none'});
        });
        $('.categories_selected').click(function () {
            $('.categories_box').css({'display':'block'});
            $('.products_box').css({'display':'none'});
            $('.services_box').css({'display':'none'});
        });
        $('.services_selected').click(function () {
            $('.services_box').css({'display':'block'});
            $('.products_box').css({'display':'none'});
            $('.categories_box').css({'display':'none'});
        });

        // AJAX call for auto complete
        $(document).ready(function(){
            var minlength = 3;
            $("#search-box").keyup(function(){
                var text = $(this).val();
                if (text.length >= minlength ) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/searchTitleProduct',
                        data: {text: text},
                        headers: {"Authorization": localStorage.getItem('token')},
                        success: function (result) {
                            if(result['view_result']){
                                $('.resultProductLists').html(result['view_result']);
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', '.chooseProduct', function () {
            var id = $(this).attr('id');
            var name = $(this).attr('data-title');
            var res ='<h5 id='+id+'>'+name+'</h5><input name="product_ids[]" value='+id+' class="hidden">' ;
            $('.productSelected').append(res);
            $(this).prop('disabled', true);
        });

    </script>

@stop

