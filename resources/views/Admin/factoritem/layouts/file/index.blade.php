<div class="container">
    <div class="wizard my-5">
        <ul class="nav nav-tabs process justify-content-center" id="myTab" role="tablist">
            <li class="nav-item flex-fill " role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 0 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step1" id="step1-tab" data-bs-toggle="tab" role="tab" aria-controls="step1"
                    aria-selected="true">
                    <i class="fas fa-user"></i>
                </a>
                <h5 class="text-center">تکمیل اطلاعات کاربر</h5>
            </li>
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 1 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step2" id="step2-tab" data-bs-toggle="tab" role="tab" aria-controls="step2"
                    aria-selected="false">
                    <i class="fas fa-list"></i>
                </a>
                <h5 class="text-center">فاکتور</h5>
            </li>
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 2 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step3" id="step3-tab" data-bs-toggle="tab" role="tab" aria-controls="step3"
                    aria-selected="false">
                    <i class="fas fa-calculator"></i>
                </a>
                <h5 class="text-center">محاسبه مبالغ</h5>
            </li>
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 3 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step4" id="step4-tab" data-bs-toggle="tab" role="tab" aria-controls="step4"
                    aria-selected="false" title="پرداخت مبلغ">
                    <i class="fas fa-credit-card"></i>
                </a>
                <h5 class="text-center">پرداخت مبلغ</h5>
            </li>
            @if($factorItem->factoritemable->session)
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 || !$factorItem['confirm'] ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem->center && $factorItem->factoritemable->session && ($factorItem['status_factor'] == 6) ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step8" id="step8-tab" data-bs-toggle="tab" role="tab" aria-controls="step8"
                    aria-selected="false" title="ثبت رویدادهای جلسات">
                    <i class="fas fa-credit-card"></i>
                </a>
                <h5 class="text-center">ثبت رویدادهای جلسات</h5>
            </li>
            @endif
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 4 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step5" id="step5-tab" data-bs-toggle="tab" role="tab" aria-controls="step5"
                    aria-selected="false" title="بارگذاری تصاویر">
                    <i class="fas fa-upload"></i>
                </a>
                <h5 class="text-center">بارگذاری تصاویر قبل کار</h5>
            </li>
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 6 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step7" id="step7-tab" data-bs-toggle="tab" role="tab" aria-controls="step7"
                    aria-selected="false" title="بارگذاری تصاویر">
                    <i class="fas fa-upload"></i>
                </a>
                <h5 class="text-center">بارگذاری تصاویر بعد کار</h5>
            </li>
            <li class="nav-item flex-fill {{ $factorItem['status'] == 0 ? 'ul-li-disabled' : ''}}" role="presentation">
                <a class="nav-link beauty-nav-link {{ $factorItem['status'] == 5 ? 'active' : ''}} rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    href="#step6" id="step6-tab" data-bs-toggle="tab" role="tab" aria-controls="step6"
                    aria-selected="false">
                    <i class="fas fa-hourglass-end"></i>
                </a>
                <h5 class="text-center">اتمام کار</h5>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade {{ $factorItem['status'] == 0 ? 'show active' : ''}}" role="tabpanel" id="step1"
                aria-labelledby="step1-tab">
                @permission('user-information')
                @include('Admin.factoritem.layouts.file.userInformation')
                @endpermission
            </div>
            <div class="tab-pane fade {{ $factorItem['status'] == 1 ? 'show active' : ''}}" role="tabpanel" id="step2"
                aria-labelledby="step2-tab">
                @include('Admin.factoritem.layouts.file.factor')
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous">
                        <i class="fas fa-angle-right"></i>قبلی
                    </a>
                    <a class="btn btn-primary px-4 mb-3 text-white next {{ $item['total_price']==0 ? 'disabled' : ''}}">
                        بعدی
                        <i class="fas fa-angle-left"></i>
                    </a>
                </div>
            </div>
            <div class="tab-pane fade {{ $factorItem['status'] == 2 ? 'show active' : ''}}" role="tabpanel" id="step3"
                aria-labelledby="step3-tab">
                @include('Admin.factoritem.layouts.file.calculate')
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous"> <i class="fas fa-angle-right"></i>
                        قبلی </a>
                    <a class="btn btn-primary px-4 mb-3 text-white next"> بعدی <i class="fas fa-angle-left"></i> </a>
                </div>
            </div>
            <div class="tab-pane fade {{ $factorItem['status'] == 3 ? 'show active' : ''}}" role="tabpanel" id="step4"
                aria-labelledby="step4-tab">
                <div class="mt-5">
                    @include('Admin.factoritem.layouts.file.prepaymentLink')
                    @if($factorItem['type'])
                    @include('Admin.factoritem.layouts.file.priceDiffLink')
                    @endif
                    @include('Admin.factoritem.layouts.payments')
                </div>
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous"> <i class="fas fa-angle-right"></i>
                        قبلی </a>
                    <a class="btn btn-primary px-4 mb-3 text-white next {{$factorItem['prepayment']['debt']==$item['prepayment']['price'] && $item['prepayment']['price'] !=0
                    ? 'disabled' : '' }}"> بعدی <i class="fas fa-angle-left"></i> </a>
                </div>
            </div>
            <div class="tab-pane fade {{ $factorItem->center && $factorItem->factoritemable->session && ($factorItem['status_factor'] == 6) ? 'active show' : ''}}"
                role="tabpanel" id="step8" aria-labelledby="step8-tab">
                <div class="mt-5">
                    @include('Admin.factoritem.layouts.session')
                </div>
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous"> <i class="fas fa-angle-right"></i>
                        قبلی </a>
                    <a class="btn btn-primary px-4 mb-3 text-white next {{$factorItem['prepayment']['debt']==$item['prepayment']['price'] && $item['prepayment']['price'] !=0
                    ? 'disabled' : '' }}"> بعدی <i class="fas fa-angle-left"></i> </a>
                </div>
            </div>
            <div class="tab-pane fade {{ $factorItem['status'] == 4 ? 'show active' : ''}}" role="tabpanel" id="step5"
                aria-labelledby="step5-tab">
                @include('Admin.factoritem.layouts.file.uploadBeforeWorkDocument')
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous"> <i class="fas fa-angle-right"></i>
                        قبلی </a>
                    <a class="btn btn-primary px-4 mb-3 text-white next"> بعدی <i class="fas fa-angle-left"></i> </a>
                </div>
            </div>
            <div class="tab-pane fade {{ $factorItem['status'] == 6 ? 'show active' : ''}}" role="tabpanel" id="step7"
                aria-labelledby="step7-tab">
                @include('Admin.factoritem.layouts.file.uploadAfterWorkDocument')
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous"> <i class="fas fa-angle-right"></i>
                        قبلی </a>
                    <a class="btn btn-primary px-4 mb-3 text-white next"> بعدی <i class="fas fa-angle-left"></i> </a>
                </div>
            </div>
            <div class="tab-pane fade {{ $factorItem['status'] == 5 ? 'show active' : ''}}" role="tabpanel" id="step6"
                aria-labelledby="step6-tab">
                @include('Admin.factoritem.layouts.file.completionFileWork')
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary px-4 mb-3 ml-2 text-white previous"> <i class="fas fa-angle-right"></i>
                        قبلی </a>
                </div>
            </div>
        </div>
    </div>
</div>
