@extends('Admin.master')
@section('title')فهرست استانداردهای تعلیق@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست استانداردهای تعلیق</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست استانداردهای تعلیق</li>
            </ol>
        </nav>
    </div>
    @permission('suspensionStandard_change')
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            <a class="btn btn-primary" href="{{ route('suspensionStandard.create') }}">استاندارد تعلیق جدید</a>
        </div>
    </div>
    @endpermission
</div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>زمان انتظار(بر اساس ساعت)</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($suspensionStandards as $key => $suspensionStandard)
                        <tr class="active" id="item_{{$suspensionStandard->id}}" style="cursor: move;">
                            <td>{{ $suspensionStandard->name }}</td>
                            <td>{{ $suspensionStandard->waitting_time }}</td>
                            <td>
                                <a href="{{ route('suspensionStandard.edit' , [$suspensionStandard->id]) }}"
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
