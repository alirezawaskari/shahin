<div class="side-menu" id="myNav">
    <div class="side-menu-body">
        <ul>
            <li><a class="active" href="#"><i class="icon ti-home"></i> <span>داشبورد</span> </a>
                <ul>
                    <li><a href="/dashboard"> <span>گزارشات مالی</span> </a></li>
                    <li><a href="/dashboard/dailyDesktop"> <span>کارتابل روزانه</span> </a></li>
                </ul>
            </li>
            @permission('callcenter|callcenter-detail|influencer-marketing|in-person-registration')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>درخواست ها</span> </a>
                <ul>
                    @permission('callcenter-detail|influencer-marketing|in-person-registration')
                    <li><a href="{{ route('callcenter.index') }}"> <span>درخواست ها</span> </a></li>
                    @endpermission
                    @permission('callcenter-cancellation')
                    <li><a href="{{ route('callcenter.cancellation') }}"> <span>انصراف از دریافت پیامک</span> </a>
                        @endpermission
                    @permission('callcenter-desktop')
                    <li><a href="{{ route('callcenter.desktop') }}"> <span>میزکار درخواست ها</span> </a></li>
                    @endpermission
                    @permission('callcenter-paid')
                    <li><a href="{{ route('callcenter.paid') }}"> <span>پرداخت شده ها</span> </a>
                        @endpermission
                        @permission('callcenter-suspension')
                    <li><a href="{{ route('callcenter.suspension') }}"> <span>معلق ها</span> </a>
                        @endpermission
                        @permission('callcenter-delete')
                    <li><a href="{{ route('callcenter.delete') }}"> <span>سطل زباله</span> </a>
                        @endpermission
                        @permission('suspensionStandard_list')
                    <li><a href="{{ route('suspensionStandard.index') }}"> <span>استانداردهای تعلیق</span> </a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('installment-calculate')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>محاسبه اقساط</span> </a>
                <ul>
                    <li><a href="{{ url('dashboard/totalFactorItem/calculate')}}"> <span>محاسبه اقساط</span> </a></li>
                </ul>
            </li>
            @endpermission
            @permission('user-detail|employee-detail')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>کاربران</span> </a>
                <ul>
                    <li><a href="{{ route('sellers') }}"><span> فروشندگان</span> </a></li>
                    @permission('user-detail')
                    <li><a href="{{ route('user.index') }}">فهرست کاربران</a></li>
                    @endpermission
                    @permission('employee-detail')
                    <li><a href="{{ route('employee.index') }}">فهرست همکاران</a></li>
                    @endpermission
                    @permission('user-delete')
                    <li><a href="{{ route('user.delete') }}">سطل زباله</a></li>
                    @endpermission
                    @permission('cash-debtors-list|installments-debtors-list|credit-debt-list')
                    <li><a href="{{route('accounting.all')}}"><span>حسابرسی</span> </a></li>
                    @endpermission
                    @permission('user-attributes')
                    <li><a href="#"><i class="icon ti-control-record"></i> <span>ویژگی ها</span> </a>
                        <ul>
                            <li><a href="{{ route('disease.index') }}">سلامتی</a></li>
                        </ul>
                    </li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('factor|factor-detail|callcenter-supporter')
            @if(auth()->user()->admin_organization_id)
            <li><a href="#"><i class="icon ti-control-record"></i> <span>امور مالی</span> </a>
                <ul>
                    <li><a href="{{ route('factor.index') }}">فهرست تراکنش ها </a></li>
                </ul>
            </li>
            @else
            <li><a href="#"><i class="icon ti-control-record"></i> <span>امور مالی</span> </a>
                <ul>
                    <li><a href="{{ route('factor.index') }}">فهرست تراکنش ها </a></li>
                    <li><a href="{{ route('factor.index') }}">فهرست تراکنش های دریافتی </a></li>
                    <li><a href="{{ route('factor.index') }}">فهرست تراکنش های پرداختی </a></li>
                    <li><a href="{{ route('WalletTransaction.index') }}">فهرست شارژ کیف پول </a></li>
                    @role('mali')
                    <li><a href="{{ route('factor.desktop') }}">میزکار تراکنش ها</a></li>
                    @endrole
                    @permission('system-income')
                    <li><a href="{{ route('system.income') }}">درآمد ماهانه و سالانه سیستم</a></li>
                    @endpermission
                    @permission('treasury-calendar|list-check|list-promissory')
                    <li><a href="#"><i class="icon ti-control-record"></i> <span>خزانه</span> </a>
                        <ul>
                            @permission('treasury-calendar')
                            <li><a href="{{ route('treasury.calendar') }}">تقویم</a></li>
                            @endpermission
                            @permission('list-check')
                            <li><a href="{{ route('check.index') }}">چک ها</a></li>
                            @endpermission
                            @permission('list-promissory')
                            <li><a href="{{ route('promissory.index') }}">سفته ها</a></li>
                            @endpermission
                        </ul>
                    </li>
                    @endpermission
                    @permission('factor-deleted-list')
                    <li><a href="{{ route('factor.delete') }}"><i class="icon ti-control-record"></i> <span>سطل
                                زباله</span> </a>
                        @endpermission
                </ul>
            </li>
            @endif

            @endpermission
            <li><a href="#"><i class="icon ti-control-record"></i> <span>حساب کاربری من</span> </a>
                <ul>
                    <li><a href="{{ route('employee.resume.edit', [auth()->user()->employee->id]) }}">رزومه کاری</a></li>
                    @permission('user_wallet')
                    <li><a href="{{ route('employee.financial.report', [auth()->user()->employee->id]) }}">عملکرد
                            ماهانه من</a></li>
                    @endpermission
                    @permission('financialReport_employee_list')
                    <li><a href="{{ route('employee.financialReport.list') }}">عملکرد مالی همکاران</a>
                    </li>
                    @endpermission
                    @permission('center_financial_report')
                    @if (auth()->user()->center_id)
                    <li><a href="{{ route('center.financialReport' , [auth()->user()->center_id]) }}">عملکرد مالی مرکز</a>
                    </li>
                    @endif
                    @endpermission
                </ul>
            </li>
            @permission('session|session-detail')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>جلسات</span> </a>
                <ul>
                    @permission('session-desktop-confirm')
                    <li><a href="{{ route('session.desktop.confirm') }}"> <span>میزکار تایید جلسات</span></a></li>
                    @endpermission
                    @permission('session-desktop-complete')
                    <li><a href="{{ route('session.desktop.complete') }}"> <span>میزکار مراجعات</span></a></li>
                    @endpermission
                    <li><a href="{{ route('session.index') }}"> <span> جلسات</span></a></li>
                </ul>
            </li>
            @endpermission
            @if(!auth()->user()->center)
            @permission('file-detail|file|file-desktop')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>پرونده ها</span> </a>
                <ul>
                    @permission('installments-file-desktop')
                    <li><a href="{{ url('/dashboard/totalFactorItem/desktop') }}">میزکار تایید پرونده های اقساطی</a></li>
                    @endpermission
                    @permission('file-desktop')
                    <li><a href="{{ url('/dashboard/factorItem/desktop') }}">پرونده های قابل اجرا</a></li>
                    @endpermission
                    @permission('file-detail|file')
                    <li><a href="{{ route('factorItem.index') }}">کل پرونده ها</a></li>
                    <li><a href="{{ route('totalFactorItem.index') }}">پرونده های اقساطی</a></li>
                    @endpermission
                    @permission('file-report')
                    <li><a href="{{ url('dashboard/totalFactorItem/reports') }}">گزارش مالی پرونده ها</a></li>
                    @endpermission
                    @permission('file-report-installments')
                    <li><a href="{{ route('totalFactorItemInstallment.index') }}">بدهکاری اقساط</a></li>
                    @endpermission
                    @permission('list-check')
                    <li><a href="{{ route('check.index') }}">چک ها</a></li>
                    @endpermission
                    @permission('list-promissory')
                    <li><a href="{{ route('promissory.index') }}">سفته ها</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @endif
            @permission('percentageAllocationEmployee_all')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>تسهیم درآمد</span> </a>
                <ul>
                    @permission('percentageAllocationEmployee_all')
                    <li><a href="{{ route('percentageAllocationEmployee.index') }}">فهرست تسهیم</a></li>
                    @endpermission
                    @permission('percentageAllocationEmployee_report')
                    <li><a href="{{ route('percentageAllocationEmployee.report') }}">گزارش تسهیم</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('center-category')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>سفارشات</span> </a>
                <ul>
                    <li><a href="{{ url('/dashboard/factorItem/desktop') }}">میزکار سفارشات</a></li>
                    <li><a href="{{ url('/dashboard/factorItemProduct') }}">سفارشات آنلاین</a></li>
                    <li><a href="{{ route('factorItem.index') }}">سفارشات حضوری</a></li>
                    @permission('file-report-installments')
                    <li><a href="{{ route('totalFactorItemInstallment.index') }}">بدهکاری اقساط</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('center-service')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>پرونده ها</span> </a>
                <ul>
                    <li><a href="{{ url('/dashboard/factorItem/desktop') }}">پرونده های قابل اجرا</a></li>
                    <li><a href="{{ route('factorItem.index') }}">کل پرونده ها</a></li>
                    @permission('file-report-installments')
                    <li><a href="{{ route('totalFactorItemInstallment.index') }}">بدهکاری اقساط</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('store-room-detail')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>انبارداری</span> </a>
                <ul>
                    <li><a href="{{ route('storeRoom.index') }}">فهرست انبارها</a></li>
                    <li><a href="{{ route('supplier.index') }}">فهرست تامین کنندگان</a></li>
                    <li><a href="{{ route('center.index') }}">فهرست مراکز</a></li>
                    <li><a href="{{ route('purchaseInvoice.index') }}">فهرست فاکتورها </a></li>
                    <li><a href="{{ route('changeStoreRoom.index') }}"> خروج از انبار </a></li>
                    @permission('request' || 'request-detail')
                    <li><a href="{{ route('request.create') }}"> ایجاد درخواست </a></li>
                    <li><a href="{{ route('request.index') }}"> درخواست ها </a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('store-room')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>انبارداری</span> </a>
                <ul>
                    <li><a href="{{ route('storeRoom.index') }}">فهرست انبارها</a></li>
                    <li><a href="{{ route('changeStoreRoom.index') }}"> خروج از انبار </a></li>
                    @permission('request' || 'request-detail')
                    <li><a href="{{ route('request.create') }}"> ایجاد درخواست </a></li>
                    <li><a href="{{ route('request.index') }}"> درخواست ها </a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('purchase-invoice')
            <li><a href="#"><i class="icon ti-control-record"></i><span>فاکتور های خرید</span> </a>
                <ul>
                    <li><a href="{{ route('purchaseInvoice.desktop') }}">میزکار فاکتورهای خرید</a></li>
                    <li><a href="{{ route('purchaseInvoice.index') }}">فهرست فاکتورهای خرید</a></li>
                </ul>
            </li>
            @endpermission
            @permission('purchase-invoice-detail')
            <li><a href="#"><i class="icon ti-control-record"></i><span>فاکتور خرید</span> </a>
                <ul>
                    <li><a href="{{ route('purchaseInvoice.index') }}">فهرست فاکتورها</a></li>
                    <li><a href="{{ route('purchaseInvoice.create') }}">فاکتور جدید</a></li>
                </ul>
            </li>
            @endpermission
            @permission('service')
            <li><a href="{{ route('service.index') }}"><i class="icon ti-control-record"></i> <span>خدمات</span> </a>
            </li>
            @endpermission
            @permission('center|center-request|center-request-detail')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>مراکز</span> </a>
                <ul>
                    @permission('center-request|center-request-detail')
                    <li><a href="{{ route('centerRequest.index') }}">درخواست مراکز</a></li>
                    @endpermission
                    @permission('center')
                    <li><a href="{{ route('center.index') }}">فهرست مراکز</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission

            @permission('contact|contact-detail')
            <li><a href="{{ route('contact.index') }}"><i class="icon ti-control-record"></i> <span>تماس با ما</span>
                </a></li>
            @endpermission
            <li><a href="#"><i class="icon ti-control-record"></i><span>سایت</span> </a>
                <ul>
                    @permission('product-detail')
                    <li><a href="#"><i class="icon ti-control-record"></i><span>محصولات</span></a>
                        <ul>
                            @if(\App\Models\User::checkManager() != null)
                            <li><a href="{{ route('products.show', 1) }}"><i
                                        class="icon ti-control-record"></i><span>مدیریت محصولات</span></a></li>
                            <li><a href="{{ route('editInformation', 1) }}"><i
                                        class="icon ti-control-record"></i><span>مدیریت تنوع و قیمت گذاری</span></a>
                            </li>
                            <li><a href="{{ route('periodicPrices.index') }}"><i
                                        class="icon ti-control-record"></i><span>تخفیف های هوشمند</span></a></li>

                            @else
                            <li><a href="{{ route('products.index') }}"><i class="icon ti-control-record"></i><span>مواد
                                        اولیه</span></a></li>
                            <li><a href="{{ route('products.show', 1) }}"><i
                                        class="icon ti-control-record"></i><span>مدیریت محصولات</span></a></li>
                            @endif

                        </ul>
                    </li>
                    <li><a href="{{ route('pack.index') }}"><i class="icon ti-control-record"></i>پک محصولات</a></li>

                    @if(\App\Models\User::checkManager() != null)
                    <li><a href="/dashboard/find/product">
                            <i class="icon ti-control-record"></i>جستجو و درج محصول </a>
                    </li>
                    @endif
                    @endpermission

                    @permission('center-manager-list')
                    @if(\App\Models\User::hasDoctor(\App\Models\User::checkManager()) > 0)
                    <li><a href="/dashboard/doctorsByCenter/{{\App\Models\User::checkManager()}}"><i
                                class="icon ti-control-record"></i>لیست پزشکان </a></li>
                    @endif
                    @endpermission

                    @permission('categories')
                    <li><a href="#"><i class="icon ti-control-record"></i><span> دسته بندی </span> </a>
                        <ul>
                            <li><a href="{{ route('category.index') }}"><i class="icon ti-control-record"></i> <span>
                                        لیست دسته بندی</span> </a></li>
                            <li><a href="{{ route('categoryPosition', 1) }}"><i class="icon ti-control-record"></i>
                                    <span> نمایش دسته بندی کالا در منو</span> </a></li>
                            <li><a href="{{ route('categoryPosition', 2) }}"><i class="icon ti-control-record"></i>
                                    <span> نمایش خدمات در منو</span> </a></li>
                        </ul>
                    </li>
                    @endpermission
                    @permission('attributes-category')
                    <li><a href="#"><i class="icon ti-control-record"></i> <span>گروه های ویژگی</span></a>
                        <ul>
                            <li><a href="{{ route('attributeGroup.index') }}"> <i class="icon ti-control-record"></i>
                                    گروه های ویژگی </a></li>
                            <li><a href="{{ route('attributeRequest.suggest') }}"> <i
                                        class="icon ti-control-record"></i>ویژگی های درخواستی</a></li>
                        </ul>
                    </li>
                    @endpermission
                    @permission('attributes-category')
                    <li><a href="{{ route('color.index') }}"><i class="icon ti-control-record"></i>رنگ</a></li>
                    <li><a href="{{ route('brand.index') }}"><i class="icon ti-control-record"></i>برند</a></li>
                    @endpermission
                    @permission('slider')
                    <li><a href="{{ route('slider.index') }}"><i class="icon ti-control-record"></i>اسلایدر</a></li>
                    @endpermission

                    @permission('about-page')
                    <li><a href="{{ route('about.index') }}"><i class="icon ti-control-record"></i>درباره ما</a></li>
                    @endpermission

                    @permission('user-invite')
                    <li><a href="#"><i class="icon ti-control-record"></i> <span>دعوت از کاربران</span></a>
                        <ul>
                            <li><a href="{{ route('userInvite.index') }}"> <i class="icon ti-control-record"></i>
                                    لیست</a></li>
                            <li><a href="{{ route('inviteCategory.index') }}"> <i class="icon ti-control-record"></i>
                                    دسته بندی دعوت </a></li>
                        </ul>
                    </li>
                    @endpermission
                    @permission('article')
                    <li><a href="#"><i class="icon ti-control-record"></i><span>مقاله</span></a>
                        <ul>
                            <li><a href="{{ route('categoryArticle.index') }}"><i class="icon ti-control-record"></i>
                                    <span> دسته بندی </span> </a></li>
                            <li><a href="{{ route('article.index') }}"><i class="icon ti-control-record"></i> <span>
                                        مقاله ها </span> </a></li>
                        </ul>
                    </li>
                    @endpermission
                    @permission('news')
                    <li><a href="#"><i class="icon ti-control-record"></i><span>اخبار</span></a>
                        <ul>
                            <li><a href="{{ route('categoryNews.index') }}"><i class="icon ti-control-record"></i>
                                    <span> دسته بندی </span> </a></li>
                            <li><a href="{{ route('news.index') }}"><i class="icon ti-control-record"></i> <span> اخبار
                                    </span> </a></li>
                        </ul>
                    </li>
                    @endpermission

                    @permission('validation')
                    <li><a href="{{ route('validation.index') }}"><i class="icon ti-control-record"></i> اعتبارسنجی
                            محصولات </a></li>
                    @endpermission

                    @permission('comment-detail')
                    <li><a href="{{ route('comment.index') }}"><i class="icon ti-control-record"></i>نظرات </a></li>
                    @endpermission

                    @permission('coupon')
                    <li><a href="#"><i class="icon ti-control-record"></i> <span> کد تخفیف</span></a>
                        <ul>
                            <li><a href="{{ route('coupon.index') }}"> <i class="icon ti-control-record"></i> کد تخفیف
                                    عمومی</a></li>
                            <li><a href="{{ route('coupons.discountVarietyProduct') }}"> <i
                                        class="icon ti-control-record"></i>کد تخفیف تنوع محصولات توسط مراکز</a></li>
                        </ul>
                    </li>
                    @endpermission
                </ul>
            </li>

            @permission('financial-plans-type')
            <li><a href="{{ route('financialPlansType.index') }}"><i class="icon ti-control-record"></i> <span>طرح های
                        مالی</span> </a></li>
            @endpermission

            @if(auth()->user()->center)
            @permission('service-tariff')
            <li><a href="#"><i class="icon ti-control-record"></i><span>تعرفه ها</span></a>
                <ul>
                    @foreach (auth()->user()->center->services as $service)
                    <li><a href="{{ route('center.serviceTariff', [auth()->user()->center_id,$service->slug]) }}">فهرست
                            تعرفه ها ({{$service->title}})</a>
                    </li>
                    @endforeach
                </ul>
            </li>
            @endpermission
            @endif

            @permission('create_agency')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>عاملیت ها</span> </a>
                <ul>
                    @permission('agency')
                    <li><a href="{{ route('agency.index') }}">درخواست عاملیت</a></li>
                    @endpermission
                    <li><a href="{{ route('representation.index') }}">فهرست عاملیت ها</a></li>
                    @permission('agency-type')
                    <li><a href="{{ route('agencyType.index') }}">انواع عاملیت</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('create_organ')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>سازمان ها</span> </a>
                <ul>
                    <li><a href="{{ route('organization.index') }}">فهرست سازمان ها</a></li>
                    <li><a href="{{ route('organization.create') }}">سازمان جدید</a></li>
                </ul>
            </li>
            @endpermission
            @permission('single_web_page')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>تک صفحه ها</span> </a>
                <ul>
                    <li><a href="{{ route('singleWebPage.index') }}">فهرست تک صفحه ها</a></li>
                </ul>
            </li>
            @endpermission
            @permission('contract')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>قراردادها</span> </a>
                <ul>
                    <li><a href="{{ route('contract.index') }}">فهرست قراردادها</a></li>
                    <li><a href="{{ route('contract.create') }}">میزکار</a></li>
                    <li><a href="{{ route('contractForm.index') }}">فرم قرارداد های پرونده های اقساطی</a></li>
                </ul>
            </li>
            @endpermission
            @permission('resume|resume-detail')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>رزومه ها</span> </a>
                <ul>
                    <li><a href="{{ route('resume.index') }}"> فهرست رزومه ها</a></li>
                    @permission('resume-desktop')
                    <li><a href="{{ route('resume.desktop') }}"> میزکار رزومه ها</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('recruitment_notice')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>موقعیت شغلی</span> </a>
                <ul>
                    <li><a href="{{ route('recruitmentNotice.index') }}"> موقعیت های شغلی</a></li>
                    <li><a href="{{ route('recruitmentNotice.create') }}">موقعیت شغلی جدید</a></li>
                </ul>
            </li>
            @endpermission
            @permission('role')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>نقش ها</span> </a>
                <ul>
                    <li><a href="{{ url('dashboard/role') }}">فهرست نقش ها</a></li>
                    <li><a href="{{ route('role.create') }}">نقش جدید</a></li>
                </ul>
            </li>
            @endpermission
            @permission('menu')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>راهنمای سایت</span> </a>
                <ul>
                    <li><a href="{{ route('help.index') }}">راهنمای سایت</a></li>
                </ul>
            </li>
            @endpermission
            <li><a href="#"><i class="icon ti-control-record"></i> <span>اتوماسیون اداری</span> </a>
                <ul>
                    <li><a href="{{ route('send.tickets')}}"><i class="icon ti-control-record"></i> <span>مکاتبات
                                ارسالی</span> </a></li>
                    <li><a href="{{ route('received.tickets')}}"><i class="icon ti-control-record"></i> <span>مکاتبات
                                دریافتی</span> </a></li>
                    <li><a href="{{ route('ticket.create')}}"><i class="icon ti-control-record"></i> <span>مکاتبه
                                جدید</span> </a></li>
                </ul>
            </li>
            @permission('coupon')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>کد تخفیف</span> </a>
                <ul>
                    <li><a href="{{ route('coupon.index') }}">فهرست کد ها</a></li>
                    <li><a href="{{ route('coupon.create') }}">کد جدید</a></li>
                </ul>
            </li>
            @endpermission

            @permission('sms')
            <li><a href="#"><i class="icon ti-control-record"></i> <span>پیامک</span> </a>
                <ul>

                    @permission('sms-list')
                    <li><a href="{{ route('groupsms.index') }}">فهرست پیامک ها</a></li>
                    @endpermission
                    @permission('send-group-sms')
                    <li><a href="{{ route('groupsms.create') }}">پیامک گروهی</a></li>
                    @endpermission
                    @permission('send-single-sms')
                    <li><a href="{{ route('sms.single') }}">پیامک تکی</a></li>
                    @endpermission
                </ul>
            </li>
            @endpermission
            @permission('phone')
            <li><a href="{{ route('lalecard.index') }}"><i class="icon ti-control-record"></i> <span>لاله کارت</span>
                </a></li>
            <li><a href="{{ route('phone.index') }}"><i class="icon ti-control-record"></i> <span>شماره های ورودی</span>
                </a></li>
            @endpermission
            @permission('newsletter')
            <li><a href="{{ route('newsletter.index') }}"><i class="icon ti-control-record"></i> <span>ایمیل ها</span>
                </a></li>
            @endpermission
            @permission('update_site_rules')
            <li><a href="{{ route('siteRules.index') }}"><i class="icon ti-control-record"></i> <span>قوانین سایت</span>
                </a></li>
            @endpermission
            @role('superadmin')
            <li><a href="{{ route('short-link.index') }}"><i class="fa fa-link "></i> <span class="mr-2">لینک های
                        کوتاه</span> </a></li>
            @endrole
            @role('superadmin')
            <li><a href="{{ route('employee.terms.index') }}"><i class="fa fa-file "></i> <span class="mr-2">قوانین
                        پرسنل</span> </a></li>
            @endrole
            @role('superadmin')
            <li><a href="{{ route('settings.index') }}"><i class="fa fa-gears "></i> <span class="mr-2">تنظیمات</span>
                </a></li>
            @endrole
        </ul>
    </div>
</div>
