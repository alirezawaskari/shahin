<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form_creditDebt" action="{{route('accounting.filter.creditDebt')}}" method="get">
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
                    <select class="form-control" name="creditType">
                        <option value="all">انتخاب نوع اعتبار</option>
                        <option value="factorItem">
                            پرونده
                        </option>
                        <option value="creditUser">
                            حواله کاربر
                        </option>
                        <option value="walltUser">
                            کیف پول کاربر
                        </option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input class="form-control" id="start-amount-creditDebt" type="number" onkeyup="startAmountOnChange()"
                        name="start_amount" placeholder="از مبلغ(ریال)" oninput="separatePrices(this)">
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input class="form-control" id="end-amount-creditDebt" type="number" onkeyup="endAmountOnChange()"
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
                    <span id="start-amount-badge-creditDebt" class="text-dark"></span>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <span id="end-amount-badge-creditDebt" class="text-dark"></span>
                </div>

                <div class="col-12 col-md-3 md-2 ">
                    <div class="custom-control custom-switch mt-2" style="display: initial">
                        <input type="checkbox" class="custom-control-input" name="by_amount" id="by_amount_creditDebt">
                        <label class="custom-control-label" for="by_amount"> <i class="fa fa-search ml-2"
                                aria-hidden="true"></i> جستجو بر اساس مبلغ</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="excel" id="excel-creditDebt">
                        <label class="custom-control-label" for="excel">خروجی اکسل</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button id="do_filter_creditDebt" class="btn btn-primary">
                        فیلتر کن
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive w-100">
        <table class="vuetable table table-bordered table-striped table-responsive-md w-100" id="datatable-json-creditDebt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره موبایل</th>
                    <th> اعتبار حواله (ریال)</th>
                    <th> اعتبار کیف پول(ریال)</th>
                    <th> اعتبار پرونده ها(ریال)</th>
                    <th> اعتبار کل (ریال)</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center"
            id="loading_creditDebt">
            <div role="status" class="spinner-border text-primary">
            </div>
            <br>
            <span>در حال بارگذاری...</span>
        </div>

        <span id="page_text_creditDebt"></span>
        <span id="total-price-page-creditDebt"></span>
        <input type="hidden" value="filter" id="url">
        <input type="hidden" value="complete" id="type">
        <div id="pagination-box-creditDebt">
            <ul id="pagination-demo-creditDebt" class="pagination"></ul>
        </div>

    </div>
</div>
