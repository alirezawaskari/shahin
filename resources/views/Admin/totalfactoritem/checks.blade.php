@extends('Admin.master')
@section('title')چک های {{ $user['family'] }}@endsection

@section('content')
 <div class="card row">
     <div class="card-header">
         <h3>
             مدیریت چک های پرونده
         </h3>
     </div>
     <div class="row">
         <div class="col-12 mt-5">
             <div class="table-responsive">
                 <table class="table table-bordered">
                     <tr>
                         <td>نام و نام خانوادگی</td>
                         <td>
                             {{$user->name . ' ' . $user->family}}
                         </td>
                     </tr>
                     <tr>
                         <td>شماره موبایل</td>
                         <td>
                             {{$user->phone}}
                         </td>
                     </tr>
                 </table>
             </div>
         </div>
     </div>
    @include('Admin.totalfactoritem.layouts.checkManagement')
 </div>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/check.js?1.1') }}"></script>
@endsection
