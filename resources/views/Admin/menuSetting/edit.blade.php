@extends('Admin.master')
@section('title')ویرایش منو @endsection

@section('css')
<style>
    /* Each sub-menu is a row of inputs, which cannot stay a table on a phone. Below the
       md breakpoint the header row is dropped and every cell becomes a labelled block,
       so one sub-menu reads as one card. */
    .submenu-table th,
    .submenu-table td {
        vertical-align: middle;
    }

    .submenu-table .col-order {
        width: 90px;
    }

    .submenu-table .col-status {
        width: 130px;
    }

    .submenu-table .col-action {
        width: 110px;
    }

    .submenu-row.has-broken-link {
        background-color: #fff5f5;
    }

    .submenu-row .row-error {
        display: none;
        margin-top: .35rem;
        font-size: .8rem;
        color: #dc3545;
    }

    @media (max-width: 767.98px) {
        .submenu-table,
        .submenu-table tbody,
        .submenu-table tr,
        .submenu-table td {
            display: block;
            width: 100%;
        }

        .submenu-table thead {
            display: none;
        }

        .submenu-table tr.submenu-row {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            margin-bottom: 1rem;
            padding: .5rem .25rem;
            background-color: #fff;
        }

        .submenu-table td {
            border: 0 !important;
            padding: .35rem .75rem;
        }

        .submenu-table td::before {
            content: attr(data-label);
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: .25rem;
        }

        .submenu-table .col-order,
        .submenu-table .col-status,
        .submenu-table .col-action {
            width: auto;
        }

        .submenu-table .btn-save {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ویرایش منو</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb flex-wrap">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="{{ route('menuSetting.index') }}">فهرست منو ها</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $menuSetting->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="card-header bg-dark-gradient col-12 border border-right-0 border-left-0">
            ویرایش منو و زیرمنو های آن
        </div>

        <form class="form-horizontal" role="form" method="post"
            action="{{ route('menuSetting.update', [$menuSetting->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('Admin.layouts.errors')

            <div class="row mt-3">
                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>عنوان منو</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $menuSetting->name) }}"
                        required>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>ایکون</label>
                    <input type="text" class="form-control" name="icon" value="{{ old('icon', $menuSetting->icon) }}"
                        placeholder="مثال: ti-home">
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>وضعیت نمایش در منو</label>
                    <select class="form-control" name="is_active">
                        <option value="1" {{ old('is_active', $menuSetting->is_active) == 1 ? 'selected' : '' }}>فعال
                        </option>
                        <option value="0" {{ old('is_active', $menuSetting->is_active) == 0 ? 'selected' : '' }}>غیرفعال
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>ترتیب نمایش</label>
                    <input type="number" min="0" name="position" value="{{ old('position', $menuSetting->position) }}"
                        class="form-control">
                </div>
            </div>

            <div class="col-12 mb-3 text-left px-0">
                <input type="submit" class="btn btn-success border-0" value="ثبت">
            </div>
        </form>

        <div class="alert alert-info">
            زیرمنوها به ترتیب عددی «ترتیب» در منوی کناری نمایش داده می‌شوند (عدد کوچکتر بالاتر).
            هر زیرمنویی که «نمایش در منو» آن فعال باشد باید لینک معتبر داشته باشد.
            نام انگلیسی زیرمنو کلید دسترسی است و قابل تغییر نیست.
        </div>

        <div class="card position-relative">
            <div class="card-body table-responsive">

                <table class="table table-bordered submenu-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام انگلیسی (غیرقابل تغییر)</th>
                            <th>عنوان نمایشی</th>
                            <th>توضیحات</th>
                            <th>لینک</th>
                            <th class="col-order">ترتیب</th>
                            <th class="col-status">نمایش در منو</th>
                            <th class="col-action"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($menuSetting->permission as $key => $permission)
                            @php
                                $menuUrl = \App\Services\V1\Admin\MenuService::linkToUrl($permission->link);
                                $isBroken = $permission->isMenu && ! $menuUrl;
                            @endphp
                            <tr class="submenu-row {{ $isBroken ? 'has-broken-link' : '' }}"
                                data-id="{{ $permission->id }}">

                                <td data-label="#">{{ $key + 1 }}</td>

                                <td data-label="نام انگلیسی">
                                    {{-- Never submitted: this is the Laratrust key that roles and
                                         @permission() checks match on. --}}
                                    <input type="text" value="{{ $permission->name }}" class="form-control" readonly>
                                </td>

                                <td data-label="عنوان نمایشی">
                                    <input type="text" data-field="display_name" class="form-control"
                                        value="{{ $permission->display_name }}">
                                </td>

                                <td data-label="توضیحات">
                                    <input type="text" data-field="description" class="form-control"
                                        value="{{ $permission->description }}">
                                </td>

                                <td data-label="لینک">
                                    <input type="text" data-field="link" class="form-control" list="route-names"
                                        value="{{ $permission->link }}" placeholder="نام مسیر، مثال: user.index">
                                    <div class="row-error"></div>
                                    @if($isBroken)
                                        <small class="text-danger d-block mt-1 broken-hint">
                                            این زیرمنو فعال است ولی لینک معتبری ندارد.
                                        </small>
                                    @endif
                                </td>

                                <td data-label="ترتیب" class="col-order">
                                    <input type="number" min="0" data-field="position" class="form-control"
                                        value="{{ $permission->position }}">
                                </td>

                                <td data-label="نمایش در منو" class="col-status">
                                    <select data-field="is_active" class="form-control">
                                        <option value="1" {{ $permission->isMenu ? 'selected' : '' }}>فعال</option>
                                        <option value="0" {{ !$permission->isMenu ? 'selected' : '' }}>غیرفعال</option>
                                    </select>
                                </td>

                                <td data-label="" class="col-action">
                                    <button type="button" class="btn btn-success btn-save">ذخیره</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">زیرمنویی برای این منو ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- The link field only accepts a real route name; offering them makes that usable. --}}
                <datalist id="route-names">
                    @foreach($routeNames as $routeName)
                        <option value="{{ $routeName }}"></option>
                    @endforeach
                </datalist>

            </div>
        </div>

        <div class="card">
            <div class="card-header bg-dark-gradient col-12 border border-right-0 border-left-0">
                افزودن زیرمنوی جدید به «{{ $menuSetting->name }}»
            </div>

            <div class="card-body">
                <form method="post" action="{{ route('menuSetting.subMenu.store', [$menuSetting->id]) }}">
                    @csrf

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                            <label>نام انگلیسی <span class="text-danger">*</span></label>
                            {{-- This becomes the Laratrust permission key. It can be chosen now, but
                                 never edited afterwards. --}}
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="مثال: report_index" required>
                            <small class="text-muted">فقط حروف انگلیسی، عدد، _ ، . و - . بعداً قابل تغییر نیست.</small>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                            <label>عنوان نمایشی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="display_name"
                                value="{{ old('display_name') }}" required>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                            <label>توضیحات</label>
                            <input type="text" class="form-control" name="description" value="{{ old('description') }}">
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                            <label>لینک</label>
                            <input type="text" class="form-control" name="link" list="route-names"
                                value="{{ old('link') }}" placeholder="نام مسیر، مثال: user.index">
                            <small class="text-muted">اگر «نمایش در منو» فعال باشد، الزامی است.</small>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                            <label>ترتیب</label>
                            <input type="number" min="0" class="form-control" name="position"
                                value="{{ old('position') }}" placeholder="خالی بگذارید تا انتهای فهرست اضافه شود">
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                            <label>نمایش در منو</label>
                            <select class="form-control" name="is_active">
                                <option value="1" {{ old('is_active', 0) == 1 ? 'selected' : '' }}>فعال</option>
                                <option value="0" {{ old('is_active', 0) == 0 ? 'selected' : '' }}>غیرفعال</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 mb-2 text-left px-0">
                        <input type="submit" class="btn btn-primary border-0" value="افزودن زیرمنو">
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function () {

        $('.btn-save').on('click', function () {

            var $button = $(this);
            var $row = $button.closest('tr');
            var $error = $row.find('.row-error');

            var value = function (field) {
                return $row.find('[data-field="' + field + '"]').val();
            };

            $error.hide().text('');
            $button.prop('disabled', true);

            $.ajax({
                url: "{{ route('permissions.updateSubMenu') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: $row.data('id'),
                    display_name: value('display_name'),
                    description: value('description'),
                    link: value('link'),
                    position: value('position'),
                    is_active: value('is_active')
                },
                success: function () {
                    // The server refuses to enable a sub-menu without a usable link, so a
                    // successful save means this row is no longer broken.
                    $row.removeClass('has-broken-link').find('.broken-hint').remove();
                    toastr.success('ذخیره شد');
                },
                error: function (xhr) {
                    var message = 'ذخیره نشد.';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        message = $.map(xhr.responseJSON.errors, function (list) {
                            return list;
                        }).join(' ');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    $error.text(message).show();
                    toastr.error(message);
                },
                complete: function () {
                    $button.prop('disabled', false);
                }
            });

        });

    });
</script>
@endsection
