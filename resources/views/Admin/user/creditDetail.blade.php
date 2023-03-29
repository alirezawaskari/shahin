@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>جزئیات اعتبار غیرنقدی کاربر</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.index') }}">فهرست کاربران</a></li>
                <li class="breadcrumb-item active" aria-current="page">جزئیات اعتبار غیرنقدی کاربر</li>
            </ol>
        </nav>
    </div>
    @permission('edit-user-credit')
    <div>
        <a data-toggle="modal" data-target="#creditModal" class="btn btn-primary" href="">تغییر اعتبار</a>
    </div>
    @endpermission
</div>
<div>
    <div class="card position-relative">
        <div class="card-header row col-12 col-md-12 mb-3">
            <div class="col-md-9">
                <h5>نام کاربر : {{ $user->name.' '.$user->family }}</h5>
            </div>
            <div class="col-md-3">
                <h5 class="btn border border-success mr-3">میزان اعتبار کاربر : {{ number_format($user->credit) }} ریال
                </h5>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>مبلغ (ریال)</th>
                        <th>ثبت کننده</th>
                        <th>توضیحات</th>
                        <th>نوع تغییرات</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($credit_details as $key => $credit_detail)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ number_format($credit_detail->price) }}</td>
                        <td>{{ $credit_detail->registrar->name.' '.$credit_detail->registrar->family }}</td>
                        <td>{{ $credit_detail->description }}</td>
                        <td>
                            @if($credit_detail->type == 'increase')
                            <button class="badge badge-success border-0">افزایش اعتبار</button>
                            @else
                            <button class="badge badge-danger border-0">کاهش اعتبار</button>
                            @endif
                        </td>
                        <td>{{ verta($credit_detail->created_at)->format('Y/m/d H:m') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="creditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">تغییر اعتبار کاربر</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('userCreditDetail.store' , [$user->id]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="col-12">
                    @include('Admin.layouts.errors')
                </div>
                <div class="col-12 mt-5 mb-4">
                    <div class="col-12 mb-2">
                        <label>مبلغ مورد نظر (ریال)</label>
                        <input class="form-control" value="0" name="price" type="text" oninput="separatePrices(this)">
                    </div>
                    <div class="col-12 mb-2">
                        <label>نوع تغییرات</label>
                        <select class="form-control" name="type">
                            <option value="increase">افزایش اعتبار</option>
                            <option value="decrease">کاهش اعتبار</option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label>توضیحات مربوطه</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>

                    <div class="col-12 mb-2 mt-4">
                        <input type="submit" class="btn btn-success mb-3 submit_btn" value="ثبت">
                    </div>
                    <div class="col-12 p-4 alert-warning">
                        <h6>ملاحضات</h6>
                        <p>1- مبلغ مورد نظر مبلغی می باشد که میخواهید به اعتبار کاربر اضافه یا از آن کم کنید.</p>
                        <p>2- توضیحات مربوط به افزایش یا کاهش اعتبار را در قسمت توضیحات مربوطه وارد کنید.</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
@endsection
