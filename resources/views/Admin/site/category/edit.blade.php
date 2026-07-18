@extends('Admin.master')

@section('css')

@stop

@section('content')
<div class="col-12">
    <div class="card"><br />
        <div class="card-body">
            <form class="form-horizontal" id="category-edit-form" method="post"
                action="{{route('category.update',$category->id)}}" enctype="multipart/form-data">
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
                <hr />
                <div class="row">
                    <div class="col-12 col-md-12 mb-2">
                        <h5>تصاویر</h5>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 text-center">
                                            <label class="form-label d-block fw-bold">تصویر فعلی</label>
                                            @php $imgMedia = $category->getFirstMedia('image'); @endphp
                                            <div id="image_current" data-box-h="120">
                                                @if($imgMedia)
                                                <div class="div_remove_btn d-inline-block mb-2"
                                                    id="media_{{ $imgMedia->id }}">
                                                    <img src="{{ $imgMedia->getFullUrl() }}" class="rounded border"
                                                        style="height:120px;width:100%;object-fit:contain;background:#f8f9fa;"
                                                        alt="تصویر دسته‌بندی">
                                                    <a data-id="{{ $imgMedia->id }}" title="حذف عکس"
                                                        class="btn btn-sm remove_media_btn"><i
                                                            class="fa fa-close"></i></a>
                                                </div>
                                                @else
                                                <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
                                                    style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">آپلود تصویر جدید</label>

                                            <div class="input-group mb-2">
                                                <input id="image"
                                                    class="d-none form-control file-upload file_upload_ajax" type="file"
                                                    name="image" accept="image/*" />
                                                <label for="image"
                                                    class="btn btn-primary custom-file-upload border border-1">
                                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                                </label>
                                            </div>

                                            <input type="hidden" id="image_path" class="path" name="image_path" value="">
                                            @if($errors->has('image'))
                                            <span class="text-danger small">{{ $errors->first('image') }}</span>
                                            @endif

                                            <small class="text-muted d-block mt-1">
                                                فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 220*190
                                            </small>
                                        </div>
                                        <div id="image_src" class="show disInline col-md-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 text-center">
                                            <label class="form-label d-block fw-bold">تصویر فعلی</label>
                                            @php $iconMedia = $category->getFirstMedia('icon'); @endphp
                                            <div id="icon_current" data-box-h="120">
                                                @if($iconMedia)
                                                <div class="div_remove_btn d-inline-block mb-2"
                                                    id="media_{{ $iconMedia->id }}">
                                                    <img src="{{ $iconMedia->getFullUrl() }}" class="rounded border"
                                                        style="height:120px;width:100%;object-fit:contain;background:#f8f9fa;"
                                                        alt="آیکن دسته‌بندی">
                                                    <a data-id="{{ $iconMedia->id }}" title="حذف عکس"
                                                        class="btn btn-sm remove_media_btn"><i
                                                            class="fa fa-close"></i></a>
                                                </div>
                                                @else
                                                <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
                                                    style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">آپلود آیکن جدید</label>

                                            <div class="input-group mb-2">
                                                <input id="icon"
                                                    class="d-none form-control file-upload file_upload_ajax" type="file"
                                                    name="icon" accept="image/*" />
                                                <label for="icon"
                                                    class="btn btn-primary custom-file-upload border border-1">
                                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                                </label>
                                            </div>

                                            <input type="hidden" id="icon_path" class="path" name="icon_path" value="">
                                            @if($errors->has('icon'))
                                            <span class="text-danger small">{{ $errors->first('icon') }}</span>
                                            @endif

                                            <small class="text-muted d-block mt-1">
                                                فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 40*40
                                            </small>
                                        </div>
                                        <div id="icon_src" class="show disInline col-md-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <option value="{{$all_service->id}}" {{ in_array(trim($all_service->id) , $similar_services)
                                ? 'selected' : '' }}>{{$all_service->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('similar_services'))
                        <span class="help-block sub-error"> {{$errors->first('similar_services')}} </span>
                        @endif
                    </div>

                </div>
                <hr />
                @endif

                @if($category->depth == 0 || $category->depth == 1)
                <div id="gallerySection">
                    <x-gallery-form
                            :galleries="$category->galleries"
                            :ghorfeTemplet="$category->parent->en_title ?? $category->en_title"
                    />

                </div> @endif

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
                        <span class="btn btn-success border-0" id="category-update-submit">ویرایش</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('js')
<script
    src="{{ asset('assets/dashboard/js/site/category/categoryEdit.js') }}?v={{ filemtime(public_path('assets/dashboard/js/site/category/categoryEdit.js')) }}"></script>
@stop
