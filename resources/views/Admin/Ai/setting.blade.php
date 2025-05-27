@extends('Admin.master')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h3>تنظیمات هوش مصنوعی</h3>
        <a href="{{route('chat_messages.index')}}" class="btn btn-danger">بازگشت</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('ai_settings.update', $setting->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="model">مدل هوش مصنوعی</label>
                    <select name="model" id="model" class="form-control" required>
                        @foreach($models as $key => $label)
                            <option value="{{ $key }}" {{ old('model', $setting->model) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="role">قانون پیش‌فرض (دستور)</label>
                    <textarea class="form-control" name="role" id="role" rows="6" required>{{ old('role', $setting->role) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
            </form>
        </div>
    </div>
@stop
