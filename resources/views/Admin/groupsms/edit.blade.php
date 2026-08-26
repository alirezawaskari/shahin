@extends('Admin.master')

@section('content')

    <!-- Start content -->
    <div class="content">
        <div class="container">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">

                    <div class="form-group">
                        <a class="btn btn-primary waves-effect waves-light" href="{{ route('groupsms.index') }}"> <i
                                    class="fa fa-share"></i>
                            باز گشت</a>
                    </div>

                    <div class="col-12">
                        <div class="panel-group" id="accordion-test-2">
                            @if($groupsm->type == 'groupsService')
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion-test-2" href="#collapseOne-2"
                                               aria-expanded="false" class="collapsed">
                                                گروهی خدمات
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne-2" class="panel-collapse collapse" aria-expanded="false"
                                         style="height: 0px;">

                                        <form class="form-horizontal" role="form" method="post"
                                              action="{{route('groupsms.update' , [$groupsm->id ])}}"
                                              enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            <div class="panel panel-default">
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">عنوان
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-8">

                                                                <input type="text" value="{{ $groupsm->title }}"
                                                                       class="form-control @if($errors->has('title')) parsley-error @endif"
                                                                       name="title" required title="اجباری">

                                                                <p class="redError"> &nbsp;
                                                                    @if($errors->has('title'))
                                                                        {{ $errors->first('title') }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">متن پیام
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-8">
                                                    <textarea
                                                            class="form-control @if($errors->has('message')) parsley-error @endif"
                                                            name="message" required>{{ $groupsm->message }}</textarea>
                                                                <p class="redError"> &nbsp;
                                                                    @if($errors->has('message'))
                                                                        {{$errors->first('message')}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">وضعیت
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-4">

                                                                <select class="form-control selectpicker" name="status"
                                                                        id="status">
                                                                    <option value="0" {{ $groupsm->status == '0' ? 'selected' : '' }}>
                                                                        پیش نویس
                                                                    </option>
                                                                    <option value="1" {{ $groupsm->status == '1' ? 'selected' : '' }}>
                                                                        آماده ارسال
                                                                    </option>
                                                                    <option value="2" {{ $groupsm->status == '2' ? 'selected' : '' }}>
                                                                        ارسال شده
                                                                    </option>
                                                                </select>

                                                                <p class="redError">
                                                                    @if($errors->has('status'))
                                                                        {{$errors->first('status')}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">وضعیت پرداخت
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-4">

                                                                <select class="form-control selectpicker" required
                                                                        name="flag" id="flag">
                                                                    <option value="">
                                                                        انتخاب کنید..
                                                                    </option>
                                                                    <option value="0" {{ $groupsm->flag == 0 ? 'selected'  :'' }}>
                                                                        پرداخت ناموفق
                                                                    </option>
                                                                    <option value="1" {{ $groupsm->flag == 1 ? 'selected'  :'' }}>
                                                                        پرداخت موفق
                                                                    </option>
                                                                </select>

                                                                <p class="redError">
                                                                    @if($errors->has('flag'))
                                                                        {{ $errors->first('flag') }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">ارسال به کاربرانی که خدمات
                                                                را خریداری کردند
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-4">

                                                                <select multiple class="form-control selectpicker"
                                                                        name="service_id[]" id="service_id">
                                                                    <option value="all" {{  $groupsm->services()->count() == 0 ? 'selected' : ''}}>
                                                                        تمام کاربران
                                                                    </option>
                                                                    @foreach($services as $service)
                                                                        <option value="{{ $service->id }}" {{ in_array(trim($service->id) , $groupsm->services()->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $service->title }}</option>
                                                                        </option>
                                                                    @endforeach
                                                                </select>

                                                                <p class="redError">
                                                                    @if($errors->has('service_id'))
                                                                        {{ $errors->first('service_id') }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">تاریخ ارسال
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            {{--  <div class="col-md-4">

                                                                  <select class="form-control selectpicker" name="flag" id="flag">
                                                                      <option value="0" {{ $groupsm->flag == '0' ? 'selected' : '' }}>
                                                                          صبح
                                                                      </option>
                                                                      <option value="1" {{ $groupsm->flag == '1' ? 'selected' : '' }}>
                                                                          عصر
                                                                      </option>
                                                                  </select>

                                                                  <p class="redError">
                                                                      @if($errors->has('flag'))
                                                                          {{$errors->first('flag')}}
                                                                      @endif
                                                                  </p>
                                                              </div>--}}
                                                            <div class="col-md-4">

                                                                <input id="date" type="text" class="form-control"
                                                                       data-jdp data-jdp-type="date" data-jdp-format="jYYYY/jMM/jD"
                                                                       name="date"
                                                                       value="{{ $groupsm->send_at }}"/>

                                                                <input id="send_at" name="send_at" type="hidden"
                                                                       class="form-control"
                                                                       value="{{ $groupsm->send_at }}"/>

                                                                <p class="redError">
                                                                    @if($errors->has('send_at'))
                                                                        {{$errors->first('send_at')}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button type="submit" class="btn btn-success waves-effect waves-light">
                                           <span class="btn-label"><i class="fa fa-check"></i>
                                           </span>ثبت
                                                    </button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            @else
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion-test-2" href="#collapseTwo-2"
                                               class="collapsed" aria-expanded="false">
                                                گروهی فهرست شماره ها
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo-2" class="panel-collapse collapse" aria-expanded="false"
                                         style="height: 0px;">

                                        <form class="form-horizontal" role="form" method="post"
                                              action="{{route('groupsms.phone.update' , [$groupsm->id ])}}"
                                              enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            <div class="panel panel-default">
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">عنوان
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-8">

                                                                <input type="text" value="{{ $groupsm->title }}"
                                                                       class="form-control @if($errors->has('title')) parsley-error @endif"
                                                                       name="title" required title="اجباری">

                                                                <p class="redError"> &nbsp;
                                                                    @if($errors->has('title'))
                                                                        {{ $errors->first('title') }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">متن پیام
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-8">
                                                    <textarea
                                                            class="form-control @if($errors->has('message')) parsley-error @endif"
                                                            name="message" required>{{ $groupsm->message }}</textarea>
                                                                <p class="redError"> &nbsp;
                                                                    @if($errors->has('message'))
                                                                        {{$errors->first('message')}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">انتخاب فهرست شماره
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-4">

                                                                <select class="form-control selectpicker" required
                                                                        name="phone_list_id" id="phone_list_id">
                                                                    <option value="">
                                                                        انتخاب کنید...
                                                                    </option>
                                                                    <option value="default" {{ $groupsm->phone_list_id == null ? 'selected' :'' }}>
                                                                        شماره های مدال سایت
                                                                    </option>
                                                                    @foreach(\App\PhoneList::all() as $list)
                                                                        <option value="{{ $list->id }}" {{ $groupsm->phone_list_id == $list->id ? 'selected' :'' }}>
                                                                            {{ $list->title }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>

                                                                <p class="redError">
                                                                    @if($errors->has('service_id'))
                                                                        {{ $errors->first('service_id') }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">وضعیت
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            <div class="col-md-4">

                                                                <select class="form-control selectpicker" name="status"
                                                                        id="status">
                                                                    <option value="0" {{ $groupsm->status == '0' ? 'selected' : '' }}>
                                                                        پیش نویس
                                                                    </option>
                                                                    <option value="1" {{ $groupsm->status == '1' ? 'selected' : '' }}>
                                                                        آماده ارسال
                                                                    </option>
                                                                    <option value="2" {{ $groupsm->status == '2' ? 'selected' : '' }}>
                                                                        ارسال شده
                                                                    </option>
                                                                </select>

                                                                <p class="redError">
                                                                    @if($errors->has('status'))
                                                                        {{$errors->first('status')}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class=" form-group">
                                                            <label class="col-md-2 control-label">تاریخ ارسال
                                                                <b style="color:red;">*</b>
                                                            </label>
                                                            {{--  <div class="col-md-4">

                                                                  <select class="form-control selectpicker" name="flag" id="flag">
                                                                      <option value="0" {{ $groupsm->flag == '0' ? 'selected' : '' }}>
                                                                          صبح
                                                                      </option>
                                                                      <option value="1" {{ $groupsm->flag == '1' ? 'selected' : '' }}>
                                                                          عصر
                                                                      </option>
                                                                  </select>

                                                                  <p class="redError">
                                                                      @if($errors->has('flag'))
                                                                          {{$errors->first('flag')}}
                                                                      @endif
                                                                  </p>
                                                              </div>--}}
                                                            <div class="col-md-4">

                                                                <input id="date" type="text" class="form-control"
                                                                       data-jdp data-jdp-type="date" data-jdp-format="jYYYY/jMM/jD"
                                                                       name="date"
                                                                       value="{{ $groupsm->send_at }}"/>

                                                                <input id="send_at" name="send_at" type="hidden"
                                                                       class="form-control"
                                                                       value="{{ $groupsm->send_at }}"/>

                                                                <p class="redError">
                                                                    @if($errors->has('send_at'))
                                                                        {{$errors->first('send_at')}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button type="submit" class="btn btn-success waves-effect waves-light">
                                           <span class="btn-label"><i class="fa fa-check"></i>
                                           </span>ثبت
                                                    </button>
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <!-- container -->

        <!-- content -->
    </div>

@endsection

@section('js')
    <script>

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $("meta[name='token']").attr("content")
            }
        });

        $(document).ready(function () {
            $('#service_id').selectpicker({
                noneSelectedText: 'انتخاب کنید',
                liveSearch: true,
                liveSearchPlaceholder: 'جست و جو کنید',
                liveSearchStyle: 'contains',
                noneResultsText: 'نتیجه ای یافت نشد'
            });
        });

        // The field is built by jalaliDatePicker.js. It publishes the unix timestamp that
        // pwt-datepicker's onSelect used to hand over on the element itself, as
        // data-jdp-unix, so the hidden #send_at keeps the same value.
        $(document).on('change', '#date', function () {
            $("#send_at").val(this.dataset.jdpUnix || '');
        });

    </script>
@endsection
