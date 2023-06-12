@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>قوانین سایت</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">قوانین سایت </li>
            </ol>
        </nav>
    </div>
</div>
<div class="card">
    <div class="قوانین سایت :"></div>
    <div class="card-body">
        <form role="form" id="form" action="{{route('siteRules.update')}}" method="POST">
            @csrf
            @method('patch')
            <textarea name="body">
                {{$site_rules['body']}}
            </textarea>
            <br>
            <input type="submit" class="btn btn-success" value="ثبت تغییرات">
        </form>
    </div>
</div>
@stop

@section('js')
<script src="{{asset('assets/script/ckeditor/ckeditor.js')}}"></script>
<script>
    CKEDITOR.replace('body',ck_uploader)
</script>
@endsection
