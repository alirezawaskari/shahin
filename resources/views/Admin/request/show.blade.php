@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>مشاهده درخواست</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('request.index') }}">درخواست ها</a></li>
                    <li class="breadcrumb-item active" aria-current="page">مشاهده درخواست</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <span>وضعیت درخواست :</span>
                <span>{{$request_rec::STATUS[$request_rec->status]}} </span>

                <table class="table table-bordered table-striped mt-4">
                    <thead>
                    <tr>
                        <th>کالا</th>
                        <th>تعداد</th>
						<th>موجودی</th>
						<th> برند</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($request_rec->requestItems as $item)
                        <tr>
                            <td>{{$item->name}}
                                @if($request_rec->type == 1)-
                                    @if($item->pivot->product_attribute_id != null)
                                        {{\App\Models\ProductAttribute::findStoreRoomName($item->pivot->product_attribute_id)}}
                                        - <span>ویژگی :</span>
                                        {{\App\Models\ProductAttribute::find($item->pivot->product_attribute_id)->value}}
                                    @endif
                                @endif
                            </td>
                            <td>@if(isset($item->pivot)) {{$item->pivot->number}}@endif</td>
                            <td> {{ $item->stock }} </td>
						    <td>{{ $item->brand }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
            @if($request_rec->status == 1)
                <p>همکار گرامی : ارجاع به انباردار به منظور تایید و ثبت درخواست از جانب شما می باشد.</p>
                <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/2">
                    <button type="button" class="btn btn-success">ارجاع به انباردار</button>
                </a>
            @endif
            @if($request_rec->status == 2)
                <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/1">
                    <button type="button" class="btn btn-info">پیش نویس</button>
                </a>
            @endif
            @if($request_rec->status == 2)
                @role('superadmin')
                <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/3" title="تایید"
                   class="btn btn-success curpon">تایید</a>

                <a title="رد" class="btn btn-danger curpon colorWhite"
                   data-toggle="modal" data-target="#rejectedStatusRequest{{$request_rec->id}}">رد</a>

                @endrole
                @if($request_rec->storeRoom->warehouse_keeper->id == auth()->user()->id)
                    <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/3" title="تایید"
                       class="badge badge-success">تایید</a>

                    <a title="رد" class="btn btn-danger curpon colorWhite"
                       data-toggle="modal" data-target="#rejectedStatusRequest{{$request_rec->id}}">رد</a>
                @endif
                <!-- rejectedStatusRequest -->
                <div id="rejectedStatusRequest{{$request_rec->id}}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"> پیام برای رد درخواست</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <label>پیام *</label>
                                <textarea id="{{$request_rec->id}}" class="form-control rejectMessage"
                                          name="message" rows="8" cols="5"
                                          title="لطفا پیام را وارد کنید " required>{{old('message')}}</textarea>
                                <br/>
                                <button id="{{$request_rec->id}}" type="submit"
                                        class="btn btn-sm btn-success rejectedStatusRequest">ارسال
                                </button>
                            </div>
                        </div>


                    </div>
                </div>

            @endif

            @if($request_rec->status == 3)
                <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/5" title="ارسال "
                   class="btn btn-success">ارسال </a>
            @endif


        </div>
    </div>
@stop
@section('js')


@stop
