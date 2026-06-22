@extends('Admin.master')

@section('css')

@stop

@section('content')
<div class="col-12">
    <div class="card"><br />
        <div class="card-body">
            <form class="form-horizontal" method="post" action="{{route('category.update',$category->id)}}"
                enctype="multipart/form-data">
                {{csrf_field()}} {{ method_field('PATCH') }}

                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5>اطلاعات کلی</h5>
                    </div>
                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان*</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید" name="title"
                            value="{{$category->title}}">
                        @if($errors->has('title'))
                        <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>

                    @if($category->depth == 1 && $parent_id != 2)
                    <div class="col-12 col-md-4 mb-5">
                        <label> عنوان دوم</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان دوم را وارد کنید"
                            name="subTitle" value="{{$category->subTitle}}">
                        @if($errors->has('subTitle'))
                        <span class="help-block sub-error  "> {{$errors->first('subTitle')}} </span>
                        @endif
                    </div>
                    @endif

                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان انگلیسی*</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان انگلیسی را وارد کنید"
                            name="en_title" value="{{$category->en_title}}">
                        @if($errors->has ('en_title'))
                        <span class="help-block sub-error  "> {{$errors->first('en_title')}} </span>
                        @endif
                    </div>

                    {{-- <div class="col-12 col-md-4 mb-5">
                        <label> حداقل تخفیف (%)</label>
                        <input type="text" class="form-control" placeholder="لطفا حداقل تخفیف (%) را وارد کنید"
                            name="minimum_discount" value="{{$category->minimum_discount}}">
                        @if($errors->has('minimum_discount'))
                        <span class="help-block sub-error  "> {{$errors->first('minimum_discount')}} </span>
                        @endif
                    </div> --}}

                    <div class="col-12 col-md-2 mb-5">
                        <label></label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="isActive"
                                id="customCheck_isActive" value="1" @if($category->isActive == 1) checked @endif>
                            <label class="custom-control-label" for="customCheck_isActive">نمایش در سایت</label>
                        </div>
                        @if($errors->has('isActive'))<span
                            class="help-block sub-error  ">{{$errors->first('isActive')}}</span>@endif
                    </div>
                    <div class="col-12 col-md-2 mb-5">
                        <label></label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="displayOrder"
                                id="customCheck_displayOrder" value="1" @if($category->displayOrder == 1) checked
                            @endif>
                            <label class="custom-control-label" for="customCheck_displayOrder">قابلیت سفارش</label>
                        </div>
                        @if($errors->has('displayOrder'))<span
                            class="help-block sub-error  ">{{$errors->first('displayOrder')}}</span>@endif
                    </div>
                </div>
                {{-- @if($category->depth == 0 || $category->depth == 1)
                <hr />
                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5> قیمت و اعتبار برای فروش اقساطی و نقدی</h5>
                    </div>
                    <div class="col-12 col-md-3 mb-5">
                        <label>قیمت </label>
                        <input type="text" name="price" id="currency-field" value="{{number_format($category->price)}}"
                            class="form-control" data-type="currency" placeholder="لطفا قیمت را وارد کنید">
                        @if($errors->has('price'))
                        <span class="help-block sub-error  "> {{$errors->first('price')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-5">
                        <label>مالیات </label>
                        <input type="text" name="tax" value="{{number_format($category->tax)}}" class="form-control"
                            placeholder="لطفا مالیات را وارد کنید">
                        @if($errors->has('tax'))
                        <span class="help-block sub-error "> {{$errors->first('tax')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-2 mb-5">
                        <label>کمترین اعتبار </label>
                        <input type="text" name="min_credit" id="currency-field"
                            value="{{number_format($category->min_credit)}}" class="form-control" data-type="currency"
                            placeholder="لطفا کمترین اعتبار را وارد کنید">
                        @if($errors->has('min_credit'))
                        <span class="help-block sub-error "> {{$errors->first('min_credit')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-2 mb-5">
                        <label>بیشترین اعتبار </label>
                        <input type="text" name="max_credit" id="currency-field"
                            value="{{number_format($category->max_credit)}}" class="form-control" data-type="currency"
                            placeholder="لطفا بیشترین اعتبار را وارد کنید">
                        @if($errors->has('max_credit'))
                        <span class="help-block sub-error "> {{$errors->first('max_credit')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-2 mb-5">
                        <label>درصد پیش پرداخت (%)</label>
                        <input id="percent_lbl" type="text" class="form-control" name="percent"
                            placeholder="لطفا درصد را وارد کنید" value="{{$category->percent}}">
                        @if($errors->has('percent'))
                        <span class="help-block sub-error "> {{$errors->first('percent')}} </span>
                        @endif
                    </div>
                    <br />
                </div>
                @endif --}}
                <hr />
                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5>تصاویر</h5>
                    </div>

                    <div class="col-12 col-md-2 mb-5">
                        <label>عکس ذخیره شده :</label>
                        <img src="{{$category->image}}" class="border-radius5" width="80px">
                    </div>

                    <div class="col-12 col-md-4 mb-5">
                        <label>تغییر عکس :</label><br />
                        <label for="image" class="custom-file-upload">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="file-upload file_upload_ajax" type="file" name="image" />
                        <div id="image_src" class="show disInline"></div>
                        <input type="hidden" id="image" class="path" name="image_path" value="">
                        @if($errors->has('image'))
                        <span class="help-block sub-error"> {{$errors->first('image')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-2 mb-5">
                        <label>آیکون ذخیره شده :</label>
                        <img src="{{$category->icon}}" class="border-radius5" width="80px">
                    </div>

                    <div class="col-12 col-md-4 mb-5">
                        <label>تغییر آیکون :</label><br />
                        <label for="icon" class="custom-file-upload"><i class="fa fa-cloud-upload"></i>
                            بارگذاری تصویر</label>
                        <input id="icon" class="file-upload file_upload_ajax" type="file" name="icon" />
                        <div id="icon_src" class="show disInline"></div>
                        <input type="hidden" id="icon" class="path" name="icon_path" value="">
                        @if($errors->has('icon'))
                        <span class="help-block sub-error"> {{$errors->first('icon')}} </span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    @if($category->depth == 0 || $category->depth == 1)
                    @if(isset($category->sliders) && count($category->sliders) > 0)
                    <div class="col-12 col-md-6 mb-5">
                        <label> عکس های اسلایدر</label>
                        <table class="table table-striped">
                            <tbody>
                                @foreach($category->sliders as $index=>$slider)
                                <tr id="slider_{{$slider->id}}" class="div_remove_btn">
                                    <td><img src="{{$slider->img}}" alt="{{$slider->title}}" class="border-radius5"
                                            width="150px"></td>
                                    <td><input type="text" class="form-control" placeholder="عنوان"
                                            name="edit_slider[{{$slider->id}}][title]" value="{{$slider->title}}"></td>
                                    <td><input type="text" class="form-control" placeholder="لینک"
                                            name="edit_slider[{{$slider->id}}][link]" value="{{$slider->link}}"
                                            required=""></td>
                                    <td><a id="{{$slider->id}}" class="btn btn-sm btn-danger remove_slider"><i
                                                class="fa fa-trash"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <div class="col-12 col-md-12 mb-5">
                        <h5>اسلایدر دسته بندی </h5>
                        <span>برای استفاده در صفحه تکی دسته بندی است</span>
                    </div>
                    <div class="col-12 col-md-12 mb-5">
                        @include('Admin.partials.multipleImages',['has_link'=>"1"])
                    </div>
                    @endif
                </div>

                @if($category->depth == 1)
                <hr />
                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5>برای قسمت دیتاماینیگ صفحه اصلی استفاده می شود</h5>
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label>دسته بندی های مشابه</label>
                        <select title="" name="similar_categories[]" class="selectMulti form-control"
                            multiple="multiple">
                            @foreach($all_categories as $all_category)
                            <option value="{{$all_category->id}}" {{ in_array(trim($all_category->id) ,
                                $similar_categories) ? 'selected' : '' }}>{{$all_category->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('similar_categories'))
                        <span class="help-block sub-error"> {{$errors->first('similar_categories')}} </span>
                        @endif
                    </div>

                        <div class="col-12 col-md-6 mb-5">
                            <label>خدمات مشابه</label>
                            <select title="" name="similar_services[]" class="selectMulti form-control" multiple="multiple">
                                @foreach($all_services as $all_service)
                                    <option value="{{$all_service->id}}" {{ in_array(trim($all_service->id) , $similar_services) ? 'selected' : '' }}>{{$all_service->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('similar_services'))
                                <span class="help-block sub-error"> {{$errors->first('similar_services')}} </span>
                            @endif
                        </div>
                    </div>
                    <hr/>
                @endif

                @if($category->depth == 0 || $category->depth == 1)
                @include('Admin.partials.editBannerHomePage',['model'=>$category])
                @endif
                <hr />
                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5>ارتباط دسته بندی ها</h5>
                        <span>تمام کالاهای این دسته در دسته انتخاب شده در این قسمت هم نمایش داده می شوند</span>
                        <span>مثلا دسته زنانه و مردانه به دسته زنانه ارتباط دارد.</span>
                    </div>

                    <div class="col-12 col-md-4 mb-5">
                        <label>مرتبط با دسته بندی </label>
                        <select title="" name="family_cat_ids_list[]" class="selectMulti form-control"
                            multiple="multiple">
                            @foreach($family_cat_ids_list as $family_cat_id)
                            <option value="{{ $family_cat_id->id }}" {{ in_array(trim($family_cat_id->id) ,
                                $related_category) ? 'selected' : '' }}>{{ $family_cat_id->title }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('family_cat_ids_list'))
                        <span class="help-block sub-error"> {{$errors->first('family_cat_ids_list')}} </span>
                        @endif
                    </div>
                </div>
                <hr />

                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5>منو</h5>
                    </div>

                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان منو(در منو سایت نمایش داده می شود)</label>
                        <input id="menu_title_lbl" type="text" class="form-control" name="menu_title"
                            value="{{\App\Models\Menu::findMenu($category->id,$category->parent_id,'App\Models\Category','title')}}"
                            placeholder="لطفا عنوان منو را وارد کنید">
                    </div>

                    <div class="col-12 col-md-4 mb-5"><br />
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="is_menu" id="customCheck_is_menu"
                                value="1" @if(\App\Models\Menu::hasCatInMenu($category->id,$category->parent_id) >= 1)
                            checked @endif>
                            <label class="custom-control-label" for="customCheck_is_menu">نمایش در منو</label>
                        </div>
                        @if($errors->has('is_menu'))<span
                            class="help-block sub-error">{{$errors->first('is_menu')}}</span>@endif
                    </div>

                    <div class="col-12 col-md-4 mb-5"><br />
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="duplicate_subset"
                                id="custom_duplicate_subset" value="1"
                                @if(\App\Models\Menu::findMenu($category->id,$category->parent_id,'App\Models\Category','duplicate_subset')
                            == 1) checked @endif>
                            <label class="custom-control-label" for="custom_duplicate_subset">نمایش با زیر
                                مجموعه</label>
                        </div>
                        @if($errors->has('duplicate_subset'))<span
                            class="help-block sub-error">{{$errors->first('duplicate_subset')}}</span>@endif
                    </div>


                    @if($category->depth > 1)
                    <div class="col-12 col-md-12 mb-2">
                        <h5>نمایش در دسته بندی دیگر</h5>
                        <span>این دسته بندی در گروه دیگری هم نمایش داده شود</span>
                    </div>
                    @if(isset($hasInOtherMenu) && count($hasInOtherMenu) > 0)
                    <br /> <br />
                    <div class="col-12 col-md-12 mb-5">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>عنوان</th>
                                    <th>گروه</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hasInOtherMenu as $item)
                                <tr id="{{$item->id}}">
                                    <td>{{$item->title}} </td>
                                    <td>{{\App\Models\Category::findCategory($item->parent_id,'title')}} </td>
                                    <td>
                                        <a id="{{$item->id}}" class="btn btn-xs btn-danger removeCatMenu">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif


                    <div class="col-12 col-md-6 mb-5">
                        <label> عنوان منو (در منو سایت نمایش داده می شود) </label>
                        <input id="other_parent_title_lbl" type="text" class="form-control" name="other_parent_title"
                            placeholder="لطفا عنوان منو را وارد کنید">

                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label>انتخاب دسته بندی</label>
                        <select title="" class="form-control" name="other_parent_id">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($cateDepthOne as $cat)
                            <option value="{{$cat->id}}">{{$cat->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <br /> <br />
                    @endif

                </div>
                <hr />
                <br />
                <div class="row">
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ویرایش">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('js')

<script src="{{ asset('assets/dashboard/js/site/category/categoryEdit.js') }}"></script>
<script>
    const themeBoxes = document.querySelectorAll('.theme-box');
    const primaryPicker = document.getElementById('primaryPicker');
    const secondaryPicker = document.getElementById('secondaryPicker');
    const primaryText = document.getElementById('primaryText');
    const secondaryText = document.getElementById('secondaryText');


    document.getElementById('serviceSelect').addEventListener('change', function() {
        let serviceId = this.value;
        let itemSelect = document.getElementById('serviceItemSelect');

        itemSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

        if (!serviceId) {
            itemSelect.innerHTML = '<option value="">ابتدا سرویس را انتخاب کنید</option>';
            return;
        }

        fetch(`/service-items/${serviceId}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    itemSelect.innerHTML = '<option value="">خالی است</option>';
                    return;
                }

                let options = '<option value="">انتخاب زیرسرویس</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}">${item.title}</option>`;
                });
                itemSelect.innerHTML = options;
            })
            .catch(() => {
                itemSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
            });
    });    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#categorySelect, #serviceSelect').change(function () {

        $.ajax({
            url: "{{ route('ghorfeOnlineList.getGallery') }}",
            type: "POST",
            data: {
                category_id: $('#categorySelect').val(),
                service_id: $('#serviceSelect').val(),
                ghorfe_id: $('#ghorfe_id').val()
            },
            success: function (res) {
                $('#gallerySection').html(res);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });

    });
    function previewImage(event, previewId) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };

        reader.readAsDataURL(file);
    }


    // حذف عکس (فقط UI - backend باید هندل کند)
    function removeImage(previewId) {
        const el = document.getElementById(previewId);
        el.src = "https://via.placeholder.com/150x100?text=Removed";
    }
    document.addEventListener('DOMContentLoaded', () => {

        initTabsNavigation();
        initMenuBuilder();
        initColorPicker();

        function initTabsNavigation() {
            const tabs = Array.from(document.querySelectorAll('.nav-link[data-toggle="pill"]'))
                .filter(t => t.offsetParent !== null);

            const getActiveIndex = () =>
                tabs.findIndex(t => t.classList.contains('active'));

            const showTab = (i) => {
                if (i < 0 || i >= tabs.length) return;
                $(tabs[i]).tab('show');
            };

            document.querySelectorAll('.btn-next').forEach(btn =>
                btn.addEventListener('click', () => showTab(getActiveIndex() + 1))
            );

            document.querySelectorAll('.btn-prev').forEach(btn =>
                btn.addEventListener('click', () => showTab(getActiveIndex() - 1))
            );
        }


        function initMenuBuilder() {
            const btn = document.getElementById('add-menu');
            const wrapper = document.getElementById('menu-wrapper');
            if (!btn || !wrapper) return;

            btn.addEventListener('click', () => {
                const items = wrapper.querySelectorAll('.menu-item');

                if (items.length >= 5) {
                    alert('حداکثر 5 منو');
                    return;
                }

                const last = items[items.length - 1];
                if (last && !isMenuValid(last)) {
                    alert('لطفا منوی قبلی را کامل کنید');
                    return;
                }

                wrapper.insertAdjacentHTML('beforeend', menuTemplate());

                if (wrapper.querySelectorAll('.menu-item').length >= 5) {
                    btn.style.display = 'none';
                }
            });

            function isMenuValid(el) {
                return [...el.querySelectorAll('input[required]')]
                    .every(i => i.value.trim() !== '');
            }

            function menuTemplate() {
                return `
                <div class="menu-item border p-2 mb-3">
                    <input type="hidden" name="ids[]" value="">
                    <div class="col-12">
                        <label>عنوان</label>
                        <input type="text" name="titles[]" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label>لینک</label>
                        <input type="text" name="links[]" class="form-control" required>
                    </div>
                </div>
            `;
            }
        }


        function initColorPicker() {
            const primary = document.getElementById('primaryPicker');
            const secondary = document.getElementById('secondaryPicker');
            const primaryText = document.getElementById('primaryText');
            const secondaryText = document.getElementById('secondaryText');

            if (!primary || !secondary) return;

            const sync = () => {
                primaryText.value = primary.value;
                secondaryText.value = secondary.value;
            };

            primary.addEventListener('input', sync);
            secondary.addEventListener('input', sync);

            sync();
        }
        const activeTab = document.querySelector('.mobile-tabs-wrapper .nav-link.active');

        if (activeTab) {
            activeTab.scrollIntoView({
                behavior: 'smooth',
                inline: 'center'
            });
        }
    });
</script>
@stop
