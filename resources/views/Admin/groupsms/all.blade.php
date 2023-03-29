@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست پیامک ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست پیامک ها</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group" role="group">
        <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            ایجاد پیامک
        </button>
        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
            <a class="dropdown-item" href="{{ route('sms.single') }}">پیامک تکی</a>
            <a class="dropdown-item" href="{{ route('groupsms.create') }}">پیامک گروهی</a>
            <a class="dropdown-item" data-toggle="modal" data-target="#sendConsentModal">پیامک ثبت رضایت مشتری</a>
        </div>
    </div>
</div>


<div class="card position-relative">
    <div class="card-body">
        <table class="table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>موبایل</th>
                    <th>نوع</th>
                    <th>متن پیام</th>
                    <th>تاریخ ارسال</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupsms as $key => $groupsm)
                <tr>
                    <td>
                        {{ $groupsm->title }}
                    </td>

                    <td>
                        {{ $groupsm->phones }}
                    </td>

                    <td>
                        @if ($groupsm->type == 'single')
                        ارسال تکی
                        @elseif($groupsm->type == 'groupsService')
                        گروهی خدمات
                        @else
                        گروهی فهرست شماره ها
                        @endif
                    </td>

                    <td>
                        {{ $groupsm->message }}
                    </td>

                    @if ($groupsm->type == 'single')
                    <td>
                        ندارد
                    </td>
                    @else
                    <td>
                        {{ verta($groupsm->send_at)->format('%d %B ، %Y') }}
                    </td>
                    @endif

                    <td>
                        {{ verta($groupsm->created_at)->format('%d %B ، %Y - H:i:s') }}
                    </td>


                    <td>
                        @if ($groupsm->status == 0)
                        <span class="label label-danger">پیش نویس</span>
                        @elseif($groupsm->status == 1)
                        <span class="label label-warning">آماده ارسال</span>
                        @else
                        <span class="label label-success">ارسال شده</span>
                        @endif
                    </td>

                    @if ($groupsm->type != 'single')
                    <td class="text-right">
                        <a href="{{ route('groupsms.edit', [$groupsm->id]) }}" class="btn btn-primary">ویرایش
                        </a>
                    </td>
                    @endif

                    <td>
                        <form class="delete_form" method="post"
                            action="{{ route('groupsms.destroy', [$groupsm->id]) }}">
                            @method('DELETE')
                            @csrf

                            <input type="button" class="btn btn-danger delete_submit" id="{{ $groupsm->id }}"
                                value="حذف" />
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @include('Admin.layouts.pagination')
    </div>
</div>
<div class="modal fade" id="sendConsentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ارسال پیامک ثبت رضایت مشتری</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sms.sendConsent') }}" method="POST">
                <div class="modal-body">
                    @csrf

                    <div class="col-12 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="ارسال لینک ثبت رضایتمندی"
                               class="form-control"
                               name="title" required>
                    </div>

                    <div class="col-12  mb-3">
                        <label>متن پیام</label>
                        <textarea required name="message" id="" cols="30" rows="4" class="form-control">عضو محترم خدمات رفاهی لاله جهت ثبت رضایت خود از مجموعه لاله از طریق لینک زیر اقدام فرمایید: https://trustseal.enamad.ir/?id=40920&Code=6abmm9WQ9521uXon0exd</textarea>
                    </div>

                    <div class="col-12  mb-3">
                        <label>شماره ها</label>
                        <textarea required placeholder="لطفا شماره های خود را با کامت (,) جدا کنید" name="phones" id="" cols="30" rows="4" class="form-control"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ارسال</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/layouts.js"></script>

@endsection
