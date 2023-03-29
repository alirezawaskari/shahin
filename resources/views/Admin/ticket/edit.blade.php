@extends('Admin.master')
@section('title')مشاهده تیکت@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 mb-3">
                <label>عنوان تیکت</label>
                <p class="form-control">{{ $ticket->title }}</p>
            </div>

            <div class="col-12 mb-3">
                <label>متن درخواست</label>
                <p class="text">{{ $ticket->content }}</p>
            </div>

            <div class="col-12 col-md-6 mb-3">
                <label>ارسال کننده</label>
                <p>{{ $ticket->user->name.' '.$ticket->user->family }}</p>
            </div>

            <div class="col-12 col-md-6 mb-3">
                <label>وضعیت</label>
                @if(!$ticket->status)
                <p>منتظر پاسخ</p>
                @else
                <p>پاسخ داده شده</p>
                @endif
            </div>

            <div class="col-12 mb-2">
                @if($ticket->file !='')
                <span>فایل های آپلود شده</span>
                <br>
                @foreach($ticket->file as $item)
                <a href="{{ $item }}" class="btn btn-success py-1 px-2 mr-2 mt-3" target="_blank">مشاهده</a>
                @endforeach
                @endif
            </div>

            <p>{{ verta($ticket->created_at)->format('%d %B ، %Y - H:i:s') }}</p>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link show active" id="factor-tab" data-toggle="tab" href="#answer" role="tab" aria-controls="answer" aria-selected="false">پاسخ تیکت</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#refer" role="tab" aria-controls="refer" aria-selected="false">ارجاع تیکت</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="answer" role="tabpanel" aria-labelledby="factor-tab">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal" role="form" method="post" action="{{route('ticketAnswer.store')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                @include('Admin.layouts.errors')
                                <input type="hidden" name="ticket_id" value="{{ $ticket->id}}">
                                <div class="col-12 mb-3">
                                    <label>متن پاسخ</label>
                                    <textarea type="text" class="form-control" name="content"></textarea>
                                </div>

                                <div class="col-12">
                                    <div id="addnew" class='file file--upload'>
                                        <label for='file'>
                                            <i class="fa fa-cloud-upload"></i>بارگذاری فایل
                                        </label>
                                        <input name="file[]" type="file" id="file"
                                            class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white">
                                    </div>
                                    <div id="file_newlink" class="col-12 row">
                                        <div>
                                        </div>
                                    </div>
                                    <div id="file_newlinktpl">
                                        <div id="file_src" class="show"></div>
                                        <input type="hidden" id="file" class="path" name="urlfile[]">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت">
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="refer" role="tabpanel" aria-labelledby="contact-tab">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal" role="form" method="post" action="{{route('ticket.refer')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id}}">
                            <div class="row">
                                @include('Admin.layouts.errors')

                                <div class="col-12 mb-3">
                                    <label>توضیحات مربوطه</label>
                                    <textarea type="text" class="form-control" name="content"></textarea>
                                </div>

                                <div class="col-12 col-md-3 mb-2">
                                    <label>واحد مربوطه</label>
                                    <select class="form-control" name="card_id">
                                        <option value="all">انتخاب کنید</option>
                                        @foreach($cards as $card)
                                        <option value="{{ $card->id }}">{{ $card->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @permission('representation-ticket')
                                <div class="col-12 col-md-3 mb-2">
                                    <label>عامل مربوطه</label>
                                    <select class="form-control select2" name="representation_id[]" multiple>
                                        @foreach($representations as $representation)
                                        <option value="{{ $representation->id }}">{{ $representation->fa_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endpermission

                                @permission('presentore-ticket')
                                <div class="col-12 col-md-3 mb-2">
                                    <label>بازاریاب دیجیتال</label>
                                    <select class="form-control select2" name="presentore_id[]" multiple>
                                        @foreach($presentores as $presentore)
                                        <option value="{{ $presentore->id }}">{{ $presentore->name.' '.$presentore->family }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endpermission

                                @permission('center-ticket')
                                <div class="col-12 col-md-3 mb-2">
                                    <label>مرکز مربوطه</label>
                                    <select class="form-control select2" name="center_id[]" multiple>
                                        @foreach($centers as $center)
                                        <option value="{{ $center->id }}">{{ $center->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endpermission

                                <div class="col-12 mt-3">
                                    <input type="submit" class="btn btn-success border-0" value="ارجاع تیکت">
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@foreach($answers as $answer)
<div class="card">
    <div class="card-body">
        <div class="col-12 mb-3">
            <label>متن پاسخ</label>
            <p>{{ $answer->content }}</p>
        </div>

        <div class="col-12 mb-2">
            @if($answer->file !='')
            <span>فایل های آپلود شده</span>
            <br>
            @foreach($answer->file as $item)
            <a href="{{ $item }}" class="btn btn-success py-1 px-2 mr-2 mt-3" target="_blank">مشاهده</a>
            @endforeach
            @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
            <label>ارسال کننده</label>
            <p>{{ $answer->user->name.' '.$answer->user->family }}</p>
            <p>{{ verta($answer->created_at)->format('%d %B ، %Y - H:i:s') }}</p>
        </div>
    </div>
</div>
</div>
@endforeach

@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
