@extends('Admin.master')
@section('title')ایجاد صفحه درباره ما@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد صفحه درباره ما</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('about.index') }}">درباره ما</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @include('Admin.layouts.errors')

        <form method="post" action="{{ route('about.store') }}">
            @csrf
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="alert alert-info mb-0">
                        {{-- One page per owner, so an owner already holding one is not in the list. --}}
                        هر کسب و کار فقط یک صفحه درباره ما دارد. مواردی که در این لیست هستند هنوز صفحه‌ای ندارند.
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>این صفحه برای کدام مالک ساخته شود؟</label>
                    <select name="owner" class="selectMulti form-control" required>
                        <option value="">انتخاب کنید</option>
                        @foreach($owners as $key => $label)
                            <option value="{{ $key }}" @selected(old('owner') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 my-3 d-flex justify-content-end">
                    <input type="submit" class="btn btn-success border-0" value="ایجاد صفحه">
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
@stop
