@extends('Admin.master')
@section('title')فهرست بیماری ها@endsection

@section('content')
    <div class="page-header">
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#diseaseModal">
           افزودن بیماری
        </button>
    </div>
    <div class="page-header">
        <div>
            <h3>فهرست بیماری ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست بیماری ها</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="{{route('disease.filter')}}" method="get">
                <div class="row">
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="title" placeholder="عنوان"/>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button id="do_filter"  class="btn btn-primary">
                            فیلتر کن
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">عنوان</th>
                    <th scope="col">عملیات </th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
            </div>
            <p id="page_text"></p>
{{--            <input type="hidden" value="{{$can_update_status_check}}" id="can-update-status-check">--}}
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
    <!--Store Modal -->
    <div class="modal fade" id="diseaseModal" tabindex="-1" role="dialog" aria-labelledby="diseaseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="diseaseModalLabel">افزودن</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="store-disease-form" action="{{route('disease.store')}}">
                <div class="modal-body">
                        <div class="form-group">
                            <label for="name">عنوان</label>
                            <input name="name" type="text" class="form-control" id="name" placeholder="عنوان بیماری را وارد نمایید ">
                        </div>
                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                        <button type="submit" class="btn btn-primary"> ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
    <script src="/assets/dashboard/js/disease.js?v=1.0"></script>
@endsection
