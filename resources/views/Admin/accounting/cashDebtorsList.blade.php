<div class="card">
    <div class="card-body table-responsive">
        <form role="form" id="filter_form" action="{{route('accounting.filter.cashDebtors')}}" method="get">
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
                <div class="col-12 col-md-3 mb-2">
                    <input class="form-control" id="start-amount" type="text" onkeyup="startAmountOnChange()"
                        name="start_amount" placeholder="از مبلغ(ریال)" oninput="separatePrices(this)">
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input class="form-control" id="end-amount" type="text" onkeyup="endAmountOnChange()"
                        name="end_amount" placeholder="تا مبلغ(ریال) " oninput="separatePrices(this)">
                </div>
                @if(is_null(auth()->user()->center_id))
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control select2" name="center">
                        <option value="all">مراکز</option>
                        @foreach($centers as $center)
                        <option value="{{$center->id}}">
                            {{$center->title}}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-3 mb-2"></div>
                <div class="col-12 col-md-3 mb-2">
                    <span id="start-amount-badge" class=" text-dark"></span>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <span id="end-amount-badge" class="  text-dark"></span>
                </div>

                <div class="col-12 col-md-3 md-2 ">
                    <div class="custom-control custom-switch mt-2" style="display: initial">
                        <input type="checkbox" class="custom-control-input" name="by_amount" id="by_amount">
                        <label class="custom-control-label" for="by_amount"> <i class="fa fa-search ml-2"
                                aria-hidden="true"></i> جستجو بر اساس مبلغ</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="excel" id="excel">
                        <label class="custom-control-label" for="excel">خروجی اکسل</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button id="do_filter" class="btn btn-primary">
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
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره موبایل</th>
                    <th> اضافه کارکرد</th>
                    <th> بدهکاری کل (ریال)</th>
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

        <span id="page_text"></span>
        <span id="total-price-page"></span>
        <input type="hidden" value="filter" id="url">
        <input type="hidden" value="complete" id="type">
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>

    </div>
</div>
