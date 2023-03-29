@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h4>تقویم کاری
                                @if(isset($user)) {{$user->name}}  {{ $user->family }}@endif
                                <span>{{$center->title}}</span>
                            </h4>
                            <div class="clearfix"></div>
                        </div>

                        <div class="col-md-12 col-xs-12">
                            <div class="form-group">
                                <label>فواصل زمانی<span
                                            class="required">*</span> :</label>
                                <div class="col-md-3 col-sm-5 col-xs-10">
                                    <select class="form-control time_intervals" title="time_intervals"
                                            name="time_intervals">
                                        <option value="15">15 دقیقه</option>
                                        <option value="20">20 دقیقه</option>
                                        <option value="30">30 دقیقه</option>
                                        <option value="60">60 دقیقه</option>
                                        <option value="120">120 دقیقه</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                        <input type="hidden" value="{{$center->id}}" class="center_id">

                        @if(isset($user))
                            <input type="hidden" value="{{$user->id}}" class="user_id">
                        @endif
                        <br/><br/>
                        <div id='calendar'></div>
                        <br/><br/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span> ثبت ساعت رویداد</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body ">
                    <div class="form-group" style="display: inline-block">
                        <label class="control-label col-md-5 col-sm-12 col-xs-12"> شروع<span class="required"> * </span> :</label>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input title="start_time" class="form-control start_time" id="start_time"
                                   placeholder="لطفا ساعت شروع را وارد کنید" type="text" name="start_time"/>
                        </div>
                    </div>
                    <div class="form-group" style="display: inline-block">
                        <label class="control-label col-md-5 col-sm-12 col-xs-12"> پایان<span class="required"> * </span> :
                        </label>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input title="end_time" class="form-control end_time" id="end_time"
                                   placeholder="لطفا ساعت پایان را وارد کنید" type="text" name="end_time"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="calendarUnSelect" class="btn btn-default" data-dismiss="modal">بستن</button>
                    <button type="button" class="btn btn-primary" id="save-event">ذخیره</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

@stop


@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/calender/lib/main.css')}}">
    <style>
        #calendar {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 10px;
        }

        .modal-dialog {
            margin-top: 140px;
        }
    </style>

@stop

@section('js')
    <script src="{{ asset('assets/dashboard/js/site/moment.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/calender/lib/main.js') }}"></script>
    <script src="{{ asset('assets/dashboard/calender/lib/locales-all.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/calendar/calendarList.js') }}"></script>

@stop

