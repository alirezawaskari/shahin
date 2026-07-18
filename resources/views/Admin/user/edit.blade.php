@extends('Admin.master')

@section('content')
<div class="page-header">
    <a href="{{ url()->previous() }}" class="btn btn-primary">بازگشت</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{route('user.update' , [$user->id])}}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @if(auth()->user()->admin_organization_id)
            <div class="row">
                <div class="col-12">
                    @include('Admin.layouts.errors')
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نام</label>
                    <input value="{{ $user->name }}" type="text" name="name" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نام خانوادگی</label>
                    <input value="{{ $user->family }}" type="text" name="family" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>موبایل</label>
                    <input value="{{ $user->phone }}" type="text" class="form-control" disabled="disabled">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کد ملی</label>
                    <input value="{{ $user->melicode }}" type="text" name="melicode" class="form-control">
                </div>
                @role('superadmin')
                <div class="col-12 col-md-4 mb-3">
                    <label>کیف پول کاربر</label>
                    <input value="{{ $user->wallet }}" type="text" name="wallet" class="form-control">
                </div>
                @endrole
                <div class="col-12 col-md-4 mb-3">
                    <label>رمز عبور جدید</label>
                    <input type="password" class="form-control password" name="password" >
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تکرار رمز عبور جدید</label>
                    <input type="password" class="form-control password" name="password_confirmation">
                </div>
                @permission('user-edit')
                <div class="col-12 col-md-4 mb-3">
                    <button type="submit" class="btn btn-success">ثبت</button>
                </div>
                @endpermission
            </div>
            @else
            <div class="row">
                <div class="col-12">
                    @include('Admin.layouts.errors')
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نام</label>
                    <input value="{{ $user->name }}" type="text" name="name" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نام خانوادگی</label>
                    <input value="{{ $user->family }}" type="text" name="family" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>موبایل</label>
                    <input value="{{ $user->phone }}" type="text" class="form-control" disabled="disabled">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کد ملی</label>
                    <input value="{{ $user->melicode }}" type="text" name="melicode" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کد شناسنامه</label>
                    <input value="{{ $user->shenasnamecode }}" type="text" name="shenasnamecode" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>محل صدور</label>
                    <input value="{{ $user->issuing }}" type="text" name="issuing" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کد پستی</label>
                    <input value="{{ $user->postalcode }}" type="text" name="postalcode" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>ایمیل</label>
                    <input value="{{ $user->email }}" type="text" name="email" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>شماره ثابت</label>
                    <input value="{{ $user->call }}" type="text" name="address" class="form-control">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>آدرس</label>
                    <input value="{{ $user->address }}" type="text" name="address" class="form-control">
                </div>
                @role('superadmin')
                <div class="col-12 col-md-4 mb-3">
                    <label>کیف پول کاربر</label>
                    <input value="{{ number_format($user->wallet) }}" type="text" name="wallet" class="form-control" oninput="separatePrices(this)">
                </div>
                @endrole
                <div class="col-12 col-md-4 mb-3">
                    <label>استان</label>
                    <select class="form-control province_id" name="province_id" id="province_id">
                        @foreach(\App\Models\Province::get() as $pro)
                        <option value="{{$pro->id}}" {{ $pro->id == $user->province_id ? 'selected' : '' }}>{{ $pro->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>شهر</label>
                    <select class="form-control city_id" name="city_id" id="city_id">
                        @foreach(\App\Models\City::get() as $pro)
                        <option value="{{$pro->id}}" {{ $pro->id == $user->city_id ? 'selected' : '' }}>{{ $pro->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>جنسیت کاربر</label>
                    <select class="form-control" name="sex">
                        <option value="0" {{ $user->sex == 0 ? 'selected' : '' }}>
                            مرد
                        </option>
                        <option value="1" {{ $user->sex == 1 ? 'selected' : '' }}>
                            زن
                        </option>
                    </select>
                </div>
                @permission('user_change_level')
                <div class="col-12 col-md-4 mb-3">
                    <label>نوع کاربر</label>
                    @include('Admin.user.layouts.levelSelect')
                </div>
                @endpermission
                <div class="col-12 col-md-4 mb-3">
                    <label>نمایندگی</label>
                    <select class="form-control" name="representation_id">
                        <option value="">
                            بدون نمایندگی (دفتر تهران)
                        </option>
                        @foreach($representations as $representation)
                        <option value="{{ $representation->id }}" {{ $user->representation_id == $representation->id ? 'selected' : '' }}>{{ $representation->fa_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>رمز عبور جدید</label>
                    <input type="password" class="form-control password" name="password">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تکرار رمز عبور جدید</label>
                    <input type="password" class="form-control password" name="password_confirmation">
                </div>
                @permission('change_user_role')
                <div class="col-12 col-md-4 mb-3">
                    <label>نقش کاربر</label>
                    <select class="form-control" name="role_id">
                        <option value="0" {{ $user->roles->count() == 0 ? 'selected'  : ''}}>
                            بدون نقش
                        </option>
                        @foreach(\App\Models\Role::get() as $role)
                        <option value="{{ $role->id }}" {{ $user->roles->count() != 0 && $user->roles[0]->id == $role->id ?  'selected' : '' }}>{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <a href="{{ route('role.create') }}">
                        <div class="btn btn-success" id="new-role">ایجاد نقش جدید +</div>
                    </a>
                </div>
                @endpermission
                <div class="col-12 col-md-4 mb-3">
                    <label>ویژگی های سلامتی</label>
                    <div class="col-12 col-md-6 mb-2">
                        <select name="diseases[]" class="custom-select" id="diseases" multiple="multiple">
                            @foreach( \App\Models\Disease::all() as $disease_item)
                                <option {{in_array($disease_item->id,$user->diseases->pluck('id')->toArray())?'selected':''}} value="{{$disease_item->id}}">{{$disease_item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کاربر ثروتمند/توانمند :</label>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="vipCheck" name="vip" {{ $user->vip && $user->vip->type == 'VIP' ? 'checked' : ''}}>
                            <label class="custom-control-label" for="vipCheck">این کاربر را به عنوان کاربر ثروتمند/توانمند  ثبت میکنم.</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کاربر فقیر/کم توان :</label>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sosCheck" name="sos" {{ $user->vip && $user->vip->type == 'SOS' ? 'checked' : ''}}>
                            <label class="custom-control-label" for="sosCheck">این کاربر را به عنوان کاربر کاربر فقیر/کم توان  ثبت میکنم.</label>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card-group">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                @if($user->imgmeli)
                                <div id="imgmeli_src" class="show">
                                <a href="{{ $user->imgmeli }}" target="_blank">
                                    <img src="{{ $user->imgmeli }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                </a>
                                @if($user->mediaIsLegacy('imgmeli'))
                                <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                @endif
                                </div>
                                @else
                                <div id="imgmeli_src" class="show">
                                <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                </div>
                                @endif
                                <div class="card-body">
                                    <label>کارت ملی</label>
                                    <br>
                                    <label for="imgmeli" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                    <input name="imgmeli"  type="file" id="imgmeli" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card">
                                @if($user->imgshenasname)
                                <div id="imgshenasname_src" class="show">
                                <a href="{{ $user->imgshenasname }}" target="_blank">
                                    <img src="{{ $user->imgshenasname }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                </a>
                                @if($user->mediaIsLegacy('imgshenasname'))
                                <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                @endif
                                </div>
                                @else
                                <div id="imgshenasname_src" class="show">
                                <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                </div>
                                @endif
                                <div class="card-body">
                                    <label>شناسنامه</label>
                                    <br>
                                    <label for="imgshenasname" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                    <input name="imgshenasname"  type="file" id="imgshenasname" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="imgshenasname_path" class="path" name="urlimgshenasname">
                                </div>

                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card">
                                @if($user->avatar)
                                <div id="avatar_src" class="show">
                                <a href="{{ $user->avatar }}" target="_blank">
                                    <img src="{{ $user->avatar }}"
                                        class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                </a>
                                @if($user->mediaIsLegacy('avatar'))
                                <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                @endif
                                </div>
                                @else
                                <div id="avatar_src" class="show">
                                <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                </div>
                                @endif
                                <div class="card-body">
                                    <label>تصویر پرسنلی</label>
                                    <br>
                                    <label for="avatar" id="up-label" class="btn badge-secondary"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                    <input name="avatar" type="file" id="avatar"
                                        class="filestyle file-upload file_upload_ajax file-up"
                                        data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="avatar_path" class="path" name="urlavatar">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card">
                                @if($user->imgturnover)
                                <div id="imgturnover_src" class="show">
                                <a href="{{ $user->imgturnover }}" target="_blank">
                                    <img src="{{ $user->imgturnover }}"
                                        class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                </a>
                                @if($user->mediaIsLegacy('imgturnover'))
                                <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                @endif
                                </div>
                                @else
                                <div id="imgturnover_src" class="show">
                                <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                </div>
                                @endif
                                <div class="card-body">
                                    <label>تصویر ثنا</label>
                                    <br>
                                    <label for="imgturnover" id="up-label" class="btn badge-secondary"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                    <input name="imgturnover" type="file" id="imgturnover"
                                        class="filestyle file-upload file_upload_ajax file-up"
                                        data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="imgturnover_path" class="path" name="urlimgturnover">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                @permission('user-edit')
                <div class="col-12 col-md-4 mb-3">
                    <button type="submit" class="btn btn-success">ثبت</button>
                </div>
                @endpermission
            </div>
            @endif
        </form>
    </div>
</div>
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
    <script>
        $(document).ready(function () {
            $('.password').val('')
            $('#diseases').select2({
                placeholder: "انتخاب وضعیت بیماری"
            });
        });
    </script>

@endsection
