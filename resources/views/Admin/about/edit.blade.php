@extends('Admin.master')
@section('title')ویرایش درباره ما@endsection

@php
    // Which pane opens: the one the last save came back from, otherwise the first.
    $activeTab = session('active_tab', 'identity_info');
    // Branch list + specialist staff live on the business, so those two panes only make
    // sense for a business-owned page (a representation / main-site page has no business).
    $ghorfe = $about->ghorfeOnline;
    // A null polymorphic owner is the main-site row rendered by the legacy site.about view.
    $isMainSite = ! $about->aboutable;
    $tourLimit = \App\Models\About::TOUR_DESCRIPTION_LIMIT;

    $tabs = array_filter([
        'identity_info' => 'هویت صفحه',
        'business_info' => 'اطلاعات کسب و کار',
        'tour_info'     => 'تور مجازی',
        'branch_info'   => $ghorfe ? 'شعبه ها' : null,
        'staff_info'    => $ghorfe ? 'همکاران متخصص' : null,
        'why_info'      => 'چرا ما؟',
        'goals_info'    => 'اهداف و چشم انداز',
        'faq_info'      => $about->representation ? null : 'سوالات متداول',
        'legacy_info'   => $isMainSite ? 'سایت قدیمی' : null,
    ]);
@endphp

@section('content')
<div class="page-header">
    <div>
        <h3>درباره {{ $about->business_name }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('about.index') }}">درباره ما</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card edit-about">
    <div class="card-body">
        @include('Admin.layouts.errors')

        <form class="form-horizontal" role="form" method="post"
              action="{{ route('about.update', $about->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            {{-- filled by the tab links so the save returns to the pane the admin was on --}}
            <input type="hidden" name="active_tab" id="active_tab" value="{{ $activeTab }}">

            <div class="row">
                <!-- Sidebar Menu -->
                <div class="col-12 col-sm-2">
                    <ul class="nav nav-pills d-block nav-justified custom-steps mb-4">
                        @foreach($tabs as $key => $label)
                            <li class="nav-item mb-2">
                                <a class="nav-link about-tab {{ $activeTab == $key ? 'active' : '' }}"
                                   data-toggle="pill" data-bs-toggle="pill" data-tab="{{ $key }}" href="#{{ $key }}">
                                    <span class="step-circle">{{ $loop->iteration }}</span>
                                    <span class="step-title">{{ $label }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Content -->
                <div class="col-12 col-sm-10">
                    <div class="tab-content border justify-content-center p-3 rounded-2">

                        {{-- ================= هویت صفحه ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'identity_info' ? 'show active' : '' }}"
                             id="identity_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    عنوان، تصویر اصلی و متن معرفی
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان صفحه</label>
                                    {{-- Derived from the business name, never typed: renaming the
                                         business renames the page everywhere it is shown. --}}
                                    <input type="text" class="form-control" value="درباره {{ $about->business_name }}" disabled>
                                    <small class="text-muted">این عنوان از نام کسب و کار ساخته می‌شود و قابل ویرایش نیست.</small>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>تصویر اصلی صفحه</label>
                                    <div id="main_image_src" class="show mb-2">
                                        @if($about->main_image)
                                            <img src="{{ $about->main_image }}" class="border-radius5"
                                                 style="height:120px;object-fit:cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center text-muted"
                                                 style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                        @endif
                                    </div>
                                    <label for="main_image" class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="main_image" class="d-none file-upload file_upload_ajax" type="file"
                                           name="main_image" accept=".jpg,.jpeg,.png,.webp">
                                    <input type="hidden" id="main_image_path" class="path" name="main_image_path">
                                </div>

                                <div class="col-12 mb-3">
                                    <label>متن معرفی</label>
                                    <textarea class="form-control" name="intro_text" rows="6"
                                              placeholder="معرفی کوتاه کسب و کار که در ابتدای صفحه درباره ما نمایش داده می‌شود">{{ old('intro_text', $about->intro_text) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ================= اطلاعات کسب و کار ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'business_info' ? 'show active' : '' }}"
                             id="business_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    اطلاعات کسب و کار
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>سال تاسیس</label>
                                    <input type="text" class="form-control" name="established_year"
                                           value="{{ old('established_year', $about->established_year) }}" placeholder="1398">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>شماره تماس مرکز</label>
                                    <input type="text" class="form-control" name="contact_phone"
                                           value="{{ old('contact_phone', $about->contact_phone) }}" placeholder="021-42568879">
                                    @if($ghorfe && $ghorfe->call)
                                        <small class="text-muted">شماره ثبت شده کسب و کار: {{ $ghorfe->call }}</small>
                                    @endif
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>روزهای کاری</label>
                                    <input type="text" class="form-control" name="working_days"
                                           value="{{ old('working_days', $about->working_days) }}" placeholder="شنبه تا چهارشنبه">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>ساعات کاری</label>
                                    <input type="text" class="form-control" name="working_hours"
                                           value="{{ old('working_hours', $about->working_hours) }}" placeholder="۱۰ صبح تا ۱۰ شب">
                                    @if($ghorfe && $ghorfe->working_hours)
                                        <small class="text-muted">ساعت کاری ثبت شده کسب و کار: {{ $ghorfe->working_hours }}</small>
                                    @endif
                                </div>

                                <div class="col-12 col-md-8 mb-3">
                                    <label>آدرس</label>
                                    <input type="text" class="form-control" name="address"
                                           value="{{ old('address', $about->address) }}"
                                           placeholder="خیابان ولی‌عصر، کوچه ایران‌زمین، پلاک ۱۲">
                                    @if($ghorfe && $ghorfe->address)
                                        <small class="text-muted">آدرس ثبت شده کسب و کار: {{ $ghorfe->address }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ================= تور مجازی ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'tour_info' ? 'show active' : '' }}"
                             id="tour_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    ویدیو / تور مجازی ۳۶۰ درجه
                                </div>

                                <div class="col-12 mb-3">
                                    {{-- Optional on purpose: an online business has no venue to tour,
                                         and the page has to render correctly without this section. --}}
                                    <div class="alert alert-info mb-0">
                                        این بخش اختیاری است. اگر ویدیویی ثبت نشود، صفحه درباره ما بدون این بخش نمایش داده می‌شود.
                                        در فاز اول به جای تور پانوراما، یک ویدیوی جایگزین نمایش داده می‌شود.
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان بخش</label>
                                    <input type="text" class="form-control" name="tour_title"
                                           value="{{ old('tour_title', $about->tour_title) }}" placeholder="تور مجازی مطب">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>لینک ویدیو (آپارات، یوتیوب یا آدرس مستقیم)</label>
                                    <input type="text" class="form-control" name="tour_video_link"
                                           value="{{ old('tour_video_link', $about->tour_video_link) }}"
                                           placeholder="https://www.aparat.com/v/xxxxx">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>تصویر پیش‌نمایش ویدیو</label>
                                    <div id="tour_poster_src" class="show mb-2">
                                        @if($about->tour_poster)
                                            <img src="{{ $about->tour_poster }}" class="border-radius5"
                                                 style="height:120px;object-fit:cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center text-muted"
                                                 style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                        @endif
                                    </div>
                                    <label for="tour_poster" class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="tour_poster" class="d-none file-upload file_upload_ajax" type="file"
                                           name="tour_poster" accept=".jpg,.jpeg,.png,.webp">
                                    <input type="hidden" id="tour_poster_path" class="path" name="tour_poster_path">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>بارگذاری فایل ویدیو (mp4 - حداکثر ۲۰ مگابایت)</label>
                                    @if($about->tour_video)
                                        <video src="{{ $about->tour_video }}" controls
                                               style="width:100%;max-height:160px;background:#000;"></video>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                   name="remove_tour_video" id="remove_tour_video">
                                            <label class="form-check-label" for="remove_tour_video">حذف ویدیو ذخیره شده</label>
                                        </div>
                                    @endif
                                    {{-- Not the shared ajax widget: uploadFilesTemporarily rejects video
                                         mimes, so the file is posted with the form itself. --}}
                                    <input type="file" class="form-control mt-2" name="tour_video_file" accept="video/mp4,video/webm">
                                </div>

                                <div class="col-12 mb-3">
                                    <label>متن توضیحات تور مجازی</label>
                                    <textarea class="form-control" name="tour_description" id="tour_description" rows="4"
                                              maxlength="{{ $tourLimit }}">{{ old('tour_description', $about->tour_description) }}</textarea>
                                    <small class="text-muted">
                                        <span id="tour_description_count">{{ mb_strlen((string) old('tour_description', $about->tour_description)) }}</span>
                                        از {{ $tourLimit }} کاراکتر
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- ================= شعبه ها ================= --}}
                        @if($ghorfe)
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'branch_info' ? 'show active' : '' }}"
                             id="branch_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    شعبه های این کسب و کار
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>عنوان بخش</label>
                                    <input type="text" class="form-control" name="branches_title"
                                           value="{{ old('branches_title', $about->branches_title) }}" placeholder="شعبه های ما">
                                </div>

                                <div class="col-12">
                                    {{-- A branch is described on its own page, never here: this table only
                                         says WHICH branches of this business the about-us page shows and in
                                         WHAT ORDER (row order = the pivot `sort`, as the tag order used to
                                         be). «ویرایش» and «ایجاد شعبه جدید» open the branch forms under
                                         «مراکز», so a branch keeps exactly one form. --}}
                                    <table class="table table-bordered" id="branch-table">
                                        <thead>
                                        <tr>
                                            <th style="width:60px">ترتیب</th>
                                            <th>نام شعبه</th>
                                            <th>آدرس</th>
                                            <th style="width:150px"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="branch-rows">
                                        @foreach($branches as $branch)
                                            <tr>
                                                <td class="branch-index text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <input type="hidden" name="branch_ids[]" value="{{ $branch->id }}">
                                                    {{ $branch->display_title }}
                                                </td>
                                                <td>{{ \Illuminate\Support\Str::limit((string) $branch->address, 80) }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('center.edit', $branch->id) }}" target="_blank"
                                                       class="btn btn-primary btn-sm">ویرایش</a>
                                                    <button type="button" class="btn btn-danger btn-sm branch-remove">
                                                        <i class="fa fa-remove"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    @if($branches->isEmpty())
                                        <div class="alert alert-warning">هنوز هیچ شعبه‌ای برای این کسب و کار ثبت نشده است.</div>
                                    @endif

                                    {{-- ghorfe_online_list_id rides along so the new branch is attached to
                                         THIS business: the branch form is shared, and a لاله admin creating
                                         one from here has no ghorfe of their own to infer it from. --}}
                                    <a href="{{ route('center.create', ['ghorfe_online_list_id' => $ghorfe->id]) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> ایجاد شعبه جدید
                                    </a>
                                    <small class="d-block text-muted mt-2">
                                        «حذف» فقط شعبه را از فهرست صفحه درباره ما برمی‌دارد و خود شعبه پاک نمی‌شود.
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- ================= همکاران متخصص ================= --}}
                        @if($ghorfe)
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'staff_info' ? 'show active' : '' }}"
                             id="staff_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    همکاران متخصص
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>عنوان بخش</label>
                                    <input type="text" class="form-control" name="staff_title"
                                           value="{{ old('staff_title', $about->staff_title) }}" placeholder="همکاران ما را بشناسید">
                                </div>

                                <div class="col-12">
                                    {{-- The staff of this business ARE the staff of its branches — there is
                                         no second, curated list to keep in step. The three descriptive lines
                                         belong to the employee's own profile, so they are shown read-only
                                         here and «ویرایش» opens that profile. --}}
                                    <table class="table table-bordered" id="staff-table">
                                        <thead>
                                        <tr>
                                            <th>نام و نام خانوادگی</th>
                                            <th>عنوان شغلی</th>
                                            <th>شعبه</th>
                                            <th>متن اول</th>
                                            <th>متن دوم</th>
                                            <th>متن سوم</th>
                                            <th style="width:150px"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="staff-rows">
                                        @foreach($employees as $employee)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="staff_ids[]" value="{{ $employee->id }}">
                                                    {{ $employee->full_name }}
                                                </td>
                                                <td>{{ $employee->work_title }}</td>
                                                <td>{{ optional($employee->center)->title }}</td>
                                                <td>{{ $employee->description_one ?: '—' }}</td>
                                                <td>{{ $employee->description_two ?: '—' }}</td>
                                                <td>{{ $employee->description_three ?: '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('employee.edit', $employee->id) }}" target="_blank"
                                                       class="btn btn-primary btn-sm">ویرایش</a>
                                                    <button type="button" class="btn btn-danger btn-sm staff-remove">
                                                        <i class="fa fa-remove"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    @if($employees->isEmpty())
                                        <div class="alert alert-warning">هنوز همکاری به شعبه های این کسب و کار اضافه نشده است.</div>
                                    @endif

                                    <small class="d-block text-muted mt-2">
                                        همکاران از شعبه های همین کسب و کار خوانده می‌شوند. «حذف» همکار را از شعبه جدا می‌کند
                                        و او دیگر در صفحه درباره ما نمایش داده نمی‌شود؛ رکورد همکار پاک نمی‌شود.
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- ================= چرا ما ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'why_info' ? 'show active' : '' }}"
                             id="why_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    {{ $about->why_heading }}
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان بخش</label>
                                    <input type="text" class="form-control" name="why_title"
                                           value="{{ old('why_title', $about->why_title) }}"
                                           placeholder="چرا {{ $about->business_name }}؟">
                                    <small class="text-muted">خالی بگذارید تا از نام کسب و کار ساخته شود.</small>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>سابقه فعالیت (سال)</label>
                                    <input type="text" class="form-control" name="years_experience"
                                           value="{{ old('years_experience', $about->years_experience) }}" placeholder="12">
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>برچسب</label>
                                    <input type="text" class="form-control" name="stat_experience_label"
                                           value="{{ old('stat_experience_label', $about->stat_experience_label) }}" placeholder="سال تجربه">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>تعداد مشتریان راضی</label>
                                    <input type="text" class="form-control" name="satisfied_customers"
                                           value="{{ old('satisfied_customers', $about->satisfied_customers) }}" placeholder="8500">
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>برچسب</label>
                                    <input type="text" class="form-control" name="stat_customers_label"
                                           value="{{ old('stat_customers_label', $about->stat_customers_label) }}" placeholder="بیمار راضی">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>میانگین امتیاز (۰ تا ۵)</label>
                                    <input type="text" class="form-control" name="average_rating"
                                           value="{{ old('average_rating', $about->average_rating) }}" placeholder="4.8">
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>برچسب</label>
                                    <input type="text" class="form-control" name="stat_rating_label"
                                           value="{{ old('stat_rating_label', $about->stat_rating_label) }}" placeholder="میانگین امتیاز">
                                </div>
                            </div>
                        </div>

                        {{-- ================= اهداف ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'goals_info' ? 'show active' : '' }}"
                             id="goals_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    اهداف و چشم انداز
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان بخش</label>
                                    <input type="text" class="form-control" name="goals_title"
                                           value="{{ old('goals_title', $about->goals_title) }}" placeholder="اهداف ما">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>تصویر مرتبط</label>
                                    <div id="goals_image_src" class="show mb-2">
                                        @if($about->goals_image)
                                            <img src="{{ $about->goals_image }}" class="border-radius5"
                                                 style="height:120px;object-fit:cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center text-muted"
                                                 style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                        @endif
                                    </div>
                                    <label for="goals_image" class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="goals_image" class="d-none file-upload file_upload_ajax" type="file"
                                           name="goals_image" accept=".jpg,.jpeg,.png,.webp">
                                    <input type="hidden" id="goals_image_path" class="path" name="goals_image_path">
                                </div>

                                <div class="col-12 mb-3">
                                    <label>متن اهداف و چشم انداز</label>
                                    <textarea class="form-control" name="goals_text" rows="8">{{ old('goals_text', $about->goals_text) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ================= سوالات متداول ================= --}}
                        @unless($about->representation)
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'faq_info' ? 'show active' : '' }}"
                             id="faq_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    سوالات متداول
                                </div>

                                <div class="col-12">
                                    <table class="table table-bordered" id="faq-table">
                                        <thead>
                                        <tr>
                                            <th style="width:35%">سوال</th>
                                            <th>پاسخ</th>
                                            <th style="width:60px"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="faq-rows">
                                        @foreach($faqs as $faq)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="faq_ids[]" value="{{ $faq->id }}">
                                                    <input type="text" class="form-control" name="faq_titles[]"
                                                           value="{{ $faq->title }}" placeholder="سوال">
                                                </td>
                                                <td>
                                                    <textarea class="form-control" name="faq_texts[]" rows="2"
                                                              placeholder="پاسخ">{{ $faq->text }}</textarea>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm faq-remove">
                                                        <i class="fa fa-remove"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <button type="button" class="btn btn-primary btn-sm" id="faq-add">
                                        <i class="fa fa-plus"></i> افزودن سوال
                                    </button>
                                    <small class="d-block text-muted mt-2">
                                        سوالی که متن آن خالی بماند ذخیره نمی‌شود و سوال حذف شده از جدول، هنگام ثبت پاک می‌شود.
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endunless

                        {{-- ================= سایت قدیمی ================= --}}
                        @if($isMainSite)
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'legacy_info' ? 'show active' : '' }}"
                             id="legacy_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    محتوای صفحه درباره ما سایت قدیمی
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="alert alert-secondary mb-0">
                                        این سه فیلد فقط برای صفحه درباره ما سایت قدیمی لاله کارت استفاده می‌شود و در فرانت جدید نمایش داده نمی‌شود.
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label>عنوان</label>
                                    <input type="text" class="form-control" name="title"
                                           value="{{ old('title', $about->title) }}">
                                </div>

                                <div class="col-12 mb-3">
                                    <label>متن قسمت اول</label>
                                    <textarea id="content" class="form-control" name="content" rows="6">{!! old('content', $about->content) !!}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label>متن قسمت دوم</label>
                                    <textarea id="content2" class="form-control" name="content2" rows="6">{!! old('content2', $about->content2) !!}</textarea>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-12 my-3 d-flex justify-content-end">
                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
    @if($isMainSite)
        <script src="/assets/script/ckeditor/ckeditor.js"></script>
        <script>
            CKEDITOR.replace('content', ck_uploader);
            CKEDITOR.replace('content2', ck_uploader);
        </script>
    @endif

    <script>
        $(function () {
            // Remember the open pane so a save returns to it instead of the first tab.
            $('.about-tab').on('click', function () {
                $('#active_tab').val($(this).data('tab'));
            });

            // Live counter for the tour caption (the column is capped at the same length).
            var $tour = $('#tour_description');
            if ($tour.length) {
                $tour.on('input', function () {
                    $('#tour_description_count').text($(this).val().length);
                });
            }

            // Branch list. A removed row simply stops posting its branch_ids[], and the
            // controller syncs the pivot to exactly what was posted — so the branch is
            // detached from the page without the center itself being touched.
            $('#branch-rows').on('click', '.branch-remove', function () {
                $(this).closest('tr').remove();
                $('#branch-rows .branch-index').each(function (i) {
                    $(this).text(i + 1);
                });
            });

            // Staff list. Same contract as the branch table: the row stops posting its
            // staff_ids[], and on save the controller detaches that employee from the branch
            // (employees.center_id = null) — the employee record itself is untouched.
            $('#staff-rows').on('click', '.staff-remove', function () {
                $(this).closest('tr').remove();
            });

            // FAQ repeater. A new row posts an EMPTY faq_ids[] so the three arrays stay
            // index-aligned — the controller pairs them by position.
            $('#faq-add').on('click', function () {
                $('#faq-rows').append(
                    '<tr>' +
                    '<td><input type="hidden" name="faq_ids[]" value="">' +
                    '<input type="text" class="form-control" name="faq_titles[]" placeholder="سوال"></td>' +
                    '<td><textarea class="form-control" name="faq_texts[]" rows="2" placeholder="پاسخ"></textarea></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-sm faq-remove">' +
                    '<i class="fa fa-remove"></i></button></td>' +
                    '</tr>'
                );
            });

            $('#faq-rows').on('click', '.faq-remove', function () {
                $(this).closest('tr').remove();
            });
        });
    </script>
@stop
