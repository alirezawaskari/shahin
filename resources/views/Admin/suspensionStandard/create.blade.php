@extends('Admin.master')
@section('title')استاندارد تعلیق جدید@endsection

@section('content')
<div class="page-header">
    <div>
        <h3> استاندارد تعلیق جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">استاندارد تعلیق جدید</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('suspensionStandard_list')
            <a href="{{ route('suspensionStandard.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست استانداردهای تعلیق</span>
            </a>
            @endpermission
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('suspensionStandard.store')}}">
                @csrf

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="{{ old('name') }}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>زمان انتظار (براساس ساعت)</label>
                        <input type="text" value="{{ old('waitting_time') }}"
                               class="form-control"
                               name="waitting_time" required
                               .85>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
