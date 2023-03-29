@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>لیست برند ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">برند ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            @if(isset($id))
                <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                    <a href="{{route('brand.createByCatId',$id)}}" class="dropdown-item" target="_blank"> ایجاد </a>
                </div>
            @else
                <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                    <a href="{{route('brand.create')}}" class="dropdown-item" target="_blank"> ایجاد </a>
                </div>
            @endif
        </div>
    </div>
    @if(isset($last_categories))
        <div class="card">
            <div class="card-body">
                <form  id="filter_form">
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <select name="category_id" class="custom-select" title="">
                            <option value="all">دسته بندی</option>
                            @foreach($last_categories as $last_category)
                                <option value="{{$last_category->id}}" @if(isset($queries['category_id'])) @if($queries['category_id'] == $last_category->id) selected @endif @endif> {{$last_category->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <select name="status" class="custom-select" title="">
                            <option value="all">وضعیت </option>
                            <option value="1">فعال </option>
                            <option value="0">غیرفعال </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-3 disInline">
                        <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">                <thead><tr>
                    <th> #</th>
                    <th> عنوان فارسی</th>
                    <th> عنوان انگلیسی</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr></thead>
                <tbody>
                </tbody>
            </table>
            <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center"
                 id="loading">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
            </div>
            <p id="page_text"></p>
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/brand.js') }}"></script>
@stop
