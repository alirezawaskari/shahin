@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
                <div class="row">

                    <div class="col-12 col-md-4 mb-2">
                        <label>عنوان مکتبه</label>
                        <input type="text"
                               class="form-control"
                               name="title" value="{{ $ticket->title }}">
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>واحد مربوطه</label>
                        <select class="form-control" name="card_id">
                            @foreach($cards as $card)
                                <option value="{{ $card->id }}" {{ $ticket->card_id == $card->id ? 'selected' : ''}}>{{ $card->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>وضعیت</label>
                        <p class="form-control">
                        @if(!$ticket->status)
                               منتظر پاسخ
                            @else
                               بایگانی شده
                            @endif
                       </p>
                    </div>


                    <div class="col-12 mb-3">
                        <label>متن درخواست</label>
                        <textarea type="text"
                                  class="form-control"
                                  name="content">{{ $ticket->content }}</textarea>
                    </div>

                    <div class="col-12 col-md-6 mb-2">
                        @if($ticket->file !='')
                            <span>یک فایل آپلود شده است</span>
                            <a href="{{ $ticket->file }}" class="btn btn-success py-1 px-2 mr-2" target="_blank">مشاهده</a>
                        @endif
                    </div>

                </div>
        </div>
    </div>
@stop

