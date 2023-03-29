@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('help.update' , [$help->id ])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-6 mb-2">
                        <label>عنوان</label>
                        <input type="text" value="{{ $help->title }}"
                               class="form-control"
                               name="title" required>
                    </div>

                    <div class="col-12 mb-2">
                        <label>متن اصلی</label>
                        <textarea name="content" >{{ $help->content }}</textarea>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop


@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
    <script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
    <script>
        @if(session()->get('message'))
            toastr.success('{{ session()->get('message') }}');
        @endif
        CKEDITOR.replace( 'content' ) ;
    </script>
@endsection
