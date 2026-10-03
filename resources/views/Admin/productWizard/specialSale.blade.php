@extends('Admin.master')

@section('title','ویزارد ایجاد محصول')

@section('content')

    <div class="page-header">
        <div>
            <h3>ویزارد ایجاد محصول</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('productWizard.index') }}">ویزارد ایجاد محصول</a></li>
                    <li class="breadcrumb-item active" aria-current="page">شگفت‌انگیز</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('Admin.productWizard.partials.steps',['current'=>'special','kind'=>'product'])

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap">
                <span>
                    این مرحله اختیاری است. کالا تا زمان خروج انتخاب‌شده در بخش «شگفت‌انگیز» سایت با درصد تخفیف تعیین‌شده
                    نمایش داده می‌شود و پس از آن به‌صورت خودکار خارج می‌شود.
                </span>
                <a href="{{ $done_url }}" class="btn btn-sm btn-outline-primary">اتمام</a>
            </div>

            @include('Admin.layouts.errors')

            <form method="post" action="{{ route('productWizard.specialSale.store') }}">
                @csrf
                @if($product)<input type="hidden" name="wizard_product_id" value="{{ $product->id }}">@endif

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="font-weight-bold d-block">کالاها</label>
                        @php $mode = old('mode', $product ? 'single' : 'multiple'); @endphp
                        @if($product)
                            <div class="mb-2">
                                <label class="ml-4">
                                    <input type="radio" name="mode" value="single" class="special-sale-mode" @if($mode === 'single') checked @endif>
                                    فقط همین کالا («{{ $product->name }}»)
                                </label>
                                <label>
                                    <input type="radio" name="mode" value="multiple" class="special-sale-mode" @if($mode === 'multiple') checked @endif>
                                    انتخاب چند کالا
                                </label>
                            </div>
                            <input type="hidden" name="product_ids[]" value="{{ $product->id }}" class="special-sale-single"
                                   @if($mode !== 'single') disabled @endif>
                        @endif

                        <div class="special-sale-multiple" @if($mode !== 'multiple') style="display:none" @endif>
                            @php $selected = array_map('intval', old('product_ids', $product ? [$product->id] : [])); @endphp
                            <select name="product_ids[]" class="form-control" style="width:100%" multiple data-placeholder="جستجو و انتخاب کالا"
                                    @if($mode !== 'multiple') disabled @endif>
                                @foreach($products as $item)
                                    <option value="{{ $item->id }}" data-search="{{ $item->id }}"
                                            @if(in_array($item->id, $selected, true)) selected @endif>
                                        {{ $item->name }} ({{ $item->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label class="font-weight-bold">درصد تخفیف <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="percentage" class="form-control" min="1" max="99" step="0.01"
                                   value="{{ old('percentage') }}">
                            <div class="input-group-append"><span class="input-group-text">٪</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label class="font-weight-bold">زمان خروج از شگفت‌انگیز <span class="text-danger">*</span></label>
                        <date-picker placeholder="انتخاب تاریخ و ساعت" format="jYYYY/jMM/jDD HH:mm" name="ends_at"
                                     type="datetime" display-format="jYYYY/jMM/jDD HH:mm"
                                     value="{{ old('ends_at') }}"></date-picker>
                    </div>

                    <div class="col-12 col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success px-5">افزودن به شگفت‌انگیز</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">کالاهای فعلی شگفت‌انگیز</h5>

            @if($items->isEmpty())
                <p class="text-muted mb-0">در حال حاضر کالایی در شگفت‌انگیز نیست.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>کد کالا</th>
                            <th>عنوان</th>
                            <th>درصد تخفیف</th>
                            <th>زمان خروج</th>
                            <th>اقدام</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product_id }}</td>
                                <td>{{ optional($item->product)->name }}</td>
                                <td>{{ rtrim(rtrim($item->percentage, '0'), '.') }}٪</td>
                                <td>
                                    @if($item->group->ends_at)
                                        {{ verta($item->group->ends_at)->format('Y/m/d H:i') }}
                                        <small class="text-muted d-block">{{ verta($item->group->ends_at)->formatDifference() }}</small>
                                    @else
                                        بدون زمان پایان
                                    @endif
                                </td>
                                <td>
                                    <form method="post" action="{{ route('productWizard.specialSale.remove', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        @if($product)<input type="hidden" name="wizard_product_id" value="{{ $product->id }}">@endif
                                        <button type="submit" class="btn btn-sm btn-outline-danger showConfirmDelete">خروج از شگفت‌انگیز</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/site/sweetalert2.css') }}" />
@stop

@section('js')
    {{-- تأیید «خروج از شگفت‌انگیز» (.showConfirmDelete در adminJs.js) از Swal استفاده می‌کند --}}
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
    <script>
        // «فقط همین کالا» شناسه کالای ویزارد را می‌فرستد و «انتخاب چند کالا» فهرست انتخاب‌شده را؛
        // فیلد غیرفعال ارسال نمی‌شود، پس همیشه فقط یکی از این دو به سرور می‌رسد.
        $(document).on('change', '.special-sale-mode', function () {
            var multiple = $(this).val() === 'multiple';
            $('.special-sale-single').prop('disabled', multiple);
            $('.special-sale-multiple').toggle(multiple).find('select').prop('disabled', !multiple);
        });
    </script>
@stop
