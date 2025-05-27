@extends('Admin.master')

@section('css')
    <style>
        #app{
            padding-top: 5px !important;
        }
        .chat-container {
            height: calc(85vh - 180px);
            overflow-y: auto;
            padding:0px 20px;
        }

        .card-body {
            background: url('/assets/dashboard/images/Chat.jpg') no-repeat center center;
            background-size: cover;
        }

        .chat-message {
            max-width: 70%;
            padding: 10px 15px;
            margin-bottom: 10px;
            word-break: break-word;
            line-height: 1.6;
            border-radius: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .chat-message.user {
            background-color: rgba(209, 236, 241, 0.75);
            backdrop-filter: blur(6.9px);
            margin-left: auto;
            text-align: right;
        }

        .chat-message.ai {
            background-color: rgba(248, 215, 218, 0.82);
            margin-right: auto;
            backdrop-filter: blur(6.9px);
            text-align: left;
        }

        .chat-timestamp {
            display: block;
            margin-top: 5px;
            font-size: 0.75rem;
            color: #666;
        }
    </style>

@stop

@section('content')
    <div class="card">
        <div class="card-header row justify-content-between mx-2">
            <span class="d-flex row justify-content-center align-items-center">
                <img src="{{$chat->user->avatar ?? '/img/user.png'}}" width="40px" height="40px" alt="avatar" class="mx-2">
            {{ $chat->user->name ?? 'مهمان' }}
            </span>
            <a href="{{ route('chat_messages.index') }}" class="btn btn-secondary">بازگشت به لیست</a>
        </div>

        <div class="card-body">
            <div class="chat-container">
                @foreach($chatMessages as $msg)
                    <div class="chat-message {{ $msg->is_user_message ? 'user' : 'ai' }}">
                        {!! nl2br(e($msg->message)) !!}
                        <span class="chat-timestamp">
                        {{ verta($msg->created_at)->format('Y/m/d H:i:s') }}
                    </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop
