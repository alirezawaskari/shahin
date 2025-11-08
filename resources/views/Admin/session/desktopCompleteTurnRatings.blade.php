<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form_trun_ratings" action="/dashboard/session/{{$url}}" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="نام" />
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="family" placeholder="نام خانوادگی" />
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="phone" placeholder="شماره موبایل" />
                </div>

                {{-- <div class="col-12 col-md-3 mb-2">
                    <select class="form-control select2" name="status">
                        <option value="all">انتخاب وضعیت جلسه</option>
                        <option value="3">
                            در انتظار مشاوره اولیه
                        </option>
                        <option value="5">
                            در انتظار مشاوره اصلی
                        </option>
                    </select>
                </div> --}}
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="end_date">
                    </date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control select2" id="employee" name="employee">
                        <option value="all">انتخاب همکار</option>
                        @foreach($admin_users as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$has_center)
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control select2" name="center">
                        <option value="all">انتخاب مرکز</option>
                        @foreach($centers as $center)
                        <option value="{{$center->id}}">{{$center->title}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="excel" id="excel-trun-ratings">
                        <label class="custom-control-label" for="excel">خروجی اکسل</label>
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <button id="do_filter_trun_ratings" class="btn btn-primary">
                        فیلتر کن
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json-trun-ratings">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره موبایل</th>
                    <th>توضیحات قبل مشاوره</th>
                    <th>تاریخ و ساعت</th>
                    <th>مرکز</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
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
        <input type="hidden" value="{{ $url }}" id="url">
        <input type="hidden" value="{{ $delete }}" id="delete-trun-ratings">
        <input type="hidden" value="{{ $type }}" id="type">
        <div id="pagination-box-trun-ratings">
            <ul id="pagination-demo-trun-ratings" class="pagination"></ul>
        </div>
    </div>
</div>
<!--modal-->
<div class="modal fade bd-example-modal-lg" id="registrationInitialConsultationModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-registration-initial-consultation">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-lg" id="visitRejectModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">لغو ویزیت</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('session.reject') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="col-12 mb-3">
                            <label for="description">توضیحات</label>
                            <input type="hidden" id="sessionID" name="session_id">
                            <textarea id="description" name="description" class="form-control">
                                    </textarea>
                            <p class="text-warning col-12"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                همکار محترم لطفا دلیل لغو ویزیت را درقسمت توضیحات ذکر نمایید.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">ثبت</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
            </div>
        </div>
    </div>
</div>
