@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست واحدها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست واحدها</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('file-fastEdit')
            <a href="{{ route('countingUnit.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>واحد جدید</span>
            </a>
            @endpermission
            <a href="{{ route('purchaseInvoice.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>فاکتور جدید</span>
            </a>
            <a href="{{ route('product.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                <span>کالا جدید</span>
            </a>
            <a href="{{ route('storeRoom.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>انبار جدید</span>
            </a>
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>نوع</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($counting_units as $key => $counting_unit)
                        <tr class="active" id="item_{{$counting_unit->id}}" style="cursor: move;">
                            <td>{{ $counting_unit->title }}</td>
                            <td>{{ $counting_unit::TYPE[$counting_unit->type] }}</td>
                            <td>
                                <a href="{{ route('countingUnit.edit' , [$counting_unit->id]) }}"
                                   class="btn btn-primary">ویرایش
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('Admin.layouts.pagination')
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/pagination.js"></script>
@endsection
