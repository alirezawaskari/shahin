@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش اطلاعات دعوت از دوستان</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('inviteCategory.update',$invite_category->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>درصد *</label>
                        <input type="text" class="form-control" placeholder="لطفا درصد را وارد کنید"
                               name="percent" value="{{$invite_category->percent}}">
                        @if($errors->has('percent'))
                            <span class="help-block sub-error"> {{$errors->first('percent')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('inviteCategory.index')}}" class="btn btn-warning">بازگشت</a>
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

    <script src="/assets/dashboard/ckeditor/ckeditor.js"></script>
    <script type="text/javascript">

        CKEDITOR.replace('editor1', {
            filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token()])}}",
            filebrowserUploadMethod: 'form'
        });

    </script>

    <script>
        $(document).ready(function () {
            $(".selectMultiTag").select2({
                tags: true,
                tokenSeparators: [',', ' ']
            })
        });

    </script>

@stop

