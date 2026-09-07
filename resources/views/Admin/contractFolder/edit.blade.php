@extends('Admin.master')

@section('css')
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش پوشه قرارداد</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post" action="{{ route('contractFolder.update', [$folder->id]) }}">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان پوشه را وارد کنید"
                               name="title" value="{{ old('title', $folder->title) }}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error">{{ $errors->first('title') }}</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>پوشه والد</label>
                        <select name="parent_id" class="form-control" title="پوشه والد">
                            <option value="">بدون والد (پوشه اصلی)</option>
                            @foreach($parents as $id => $title)
                                <option value="{{ $id }}"
                                        @if(old('parent_id', $folder->parent_id) == $id) selected @endif>{{ $title }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('parent_id'))
                            <span class="help-block sub-error">{{ $errors->first('parent_id') }}</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>ترتیب</label>
                        <input type="number" class="form-control" name="position"
                               value="{{ old('position', $folder->position) }}">
                    </div>
                    <div class="col-12 col-md-8 mb-3">
                        <label>توضیحات</label>
                        <textarea class="form-control" name="description" rows="3"
                                  title="توضیحات">{{ old('description', $folder->description) }}</textarea>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $folder->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">فعال</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="/dashboard/contractFolder" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
@stop
