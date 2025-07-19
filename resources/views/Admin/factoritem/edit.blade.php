@extends('Admin.master')
@section('title')پرونده {{ $user['family'] }}@endsection

@section('content')
    @include('Admin.factoritem.layouts.info')
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link show {{ $factorItem->center && (!$factorItem->factoritemable->session || $factorItem['status_factor'] != 6) ? 'active' : ''}}"
                       id="factor-tab" data-toggle="tab" href="#factor" role="tab" aria-controls="factor"
                       aria-selected="false">پرونده کاربر</a>
                </li>
                @if($factorItem->factoritemable->session)
                    <li class="nav-item">
                        <a class="nav-link show {{ $factorItem->center && $factorItem['status_factor'] == 6 ? 'active' : ''}}" id="contact-tab"
                           data-toggle="tab" href="#session" role="tab" aria-controls="session" aria-selected="false">نوبت دهی</a>
                    </li>
                @endif
                @if(!auth()->user()->center_id && !isset($factorItem->center))
                <li class="nav-item">
                    <a class="nav-link show {{ !$factorItem->center ? 'active' : ''}}" id="contact-tab"
                       data-toggle="tab" href="#turnRatings" role="tab" aria-controls="turnRatings" aria-selected="false">تعیین مرکز</a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link show" id="printForm-tab"
                       data-toggle="tab" href="#printForm" role="tab" aria-controls="printForm" aria-selected="false">فرم ثبت نام</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                       aria-controls="contact" aria-selected="false">توضیحات</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                @if(isset($factorItem->center))
                    <div class="tab-pane fade p-t-20 {{ $factorItem->center && (!$factorItem->factoritemable->session || $factorItem['status_factor'] != 6) ? 'active show' : ''}}"
                         id="factor" role="tabpanel" aria-labelledby="factor-tab">
                        @include('Admin.factoritem.layouts.file.index')
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $factorItem->center && $factorItem->factoritemable->session && ($factorItem['status_factor'] == 6) ? 'active show' : ''}}"
                         id="session" role="tabpanel" aria-labelledby="contact-tab">
                        @include('Admin.factoritem.layouts.session')
                    </div>
                    <div class="tab-pane fade p-t-20 {{ !$factorItem->center ? 'active show' : ''}}" id="printForm" role="tabpanel" aria-labelledby="printForm-tab">
                        @include('Admin.factoritem.layouts.print')
                     </div>
                     @include('Admin.factoritem.layouts.histories',['user_histories'=>$factorItem['user_histories'],'factor'=>$factorItem['factor']])
                @else
                    <div class="alert alert-info alert-with-border mt-3">
                        <h6>مرکز برای این پرونده انتخاب نشده است.</h6>
                    </div>
                @endif
                    <div class="tab-pane fade p-t-20 {{ !$factorItem->center ? 'active show' : ''}}"
                        id="turnRatings" role="tabpanel" aria-labelledby="contact-tab">
                       @include('Admin.factoritem.show.determineCentering')
                    </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/factorItem.js?v=1.3') }}"></script>
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=1.2') }}"></script>
    <script src="{{asset('assets/dashboard/js/fontawesome.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('/assets/dashboard/js/factorModal.js?v=1.0') }}"></script>

    <script>
        $(document).ready( function () {
            $('#datatable-history-blade').DataTable({searching: false});
        } );
        $(document).ready(function () {
            if ($('#treatment_plan').length > 0) {
                treatmentPlan();
            }
            if ($('#treatment_plan_legal').length > 0) {
                treatmentPlanLegal();
            }
        });
    </script>
@endsection
