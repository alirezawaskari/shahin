@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش یک رویداد تقویم کاری
                    @if(isset($calendar->fullName)) {{$calendar->fullName}} @endif
                    <span>{{$calendar->center_name}}</span>
                </h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('fullCalendar.update',$calendar->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="row">
                    <div class="col-md-6 col-xs-6">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">فواصل زمانی<span
                                        class="required">*</span> :</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <select class="form-control time_intervals" title="time_intervals"
                                        name="time_intervals">
                                    <option value="15" @if($calendar->time_intervals == 15) selected @endif>15
                                        دقیقه
                                    </option>
                                    <option value="20" @if($calendar->time_intervals == 20) selected @endif>20
                                        دقیقه
                                    </option>
                                    <option value="30" @if($calendar->time_intervals == 30) selected @endif>30
                                        دقیقه
                                    </option>
                                    <option value="60" @if($calendar->time_intervals == 60) selected @endif>60
                                        دقیقه
                                    </option>
                                    <option value="120" @if($calendar->time_intervals == 120) selected @endif>
                                        120
                                        دقیقه
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <br/> <br/>
                <div class="row">
                    <div class="col-md-6 col-xs-6">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"> شروع<span
                                        class="required">*</span> :
                            </label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <input title="start_time" class="form-control start_time"
                                       value="{{$calendar->start_time}}"
                                       placeholder="لطفا ساعت شروع را وارد کنید" type="text" name="start_time"
                                       id="start_time"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"> پایان<span
                                        class="required">*</span> :
                            </label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <input title="end_time" class="form-control end_time"
                                       value="{{$calendar->end_time}}"
                                       placeholder="لطفا ساعت پایان را وارد کنید" type="text" name="end_time"
                                       id="end_time"/>
                            </div>
                        </div>
                    </div>
                    <br/><br/>
                </div>
                <br/>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop


@section('css')


@stop

@section('js')
    <script type="text/javascript">

        // .start_time / .end_time are built by jalaliDatePicker.js.


    </script>
@stop

