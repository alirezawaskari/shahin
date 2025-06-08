@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop

@section('content')

    <div class="page-header">
        <div>
            <h3>لیست چت‌ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">چت‌ها</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body row justify-content-between mx-2">
            <form method="get" action="{{ route('chat_messages.index') }}" class="col-9">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <input type="text" name="user_name" class="form-control" placeholder="نام کاربر"
                               value="{{ request('user_name') }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <input type="text" name="session_id" class="form-control" placeholder="Session ID"
                               value="{{ request('session_id') }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        <a href="{{ route('chat_messages.index') }}" class="btn btn-danger">حذف فیلتر</a>
                    </div>
                </div>
            </form>
            <a href="{{ route('ai_settings.setting') }}" class="btn btn-warning">
                تنظیمات هوش مصنوعی <i class="fa fa-gear font-18"></i>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if(count($messages) > 0)
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>کد</th>
                        <th>کاربر / Session</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($messages as $message)
                        <tr>
                            <td>{{ $message->id }}</td>
                            <td>
                                @if($message->user_id)
                                    {{ $message->user->name . ' ' . $message->user->family }}
                                @else
                                    {{ $message->session_id }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('chat_messages.show', ['id' => $message->id]) }}"
                                   class="btn btn-floating btn-info"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center">هیچ چتی یافت نشد.</p>
            @endif
        </div>
    </div>

@stop

@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
