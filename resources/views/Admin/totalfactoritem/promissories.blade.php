@extends('Admin.master')
@section('title')سفته های {{ $user['family'] }}@endsection

@section('content')
 <div class="card">
     <div class="card-header">
         <h3>
             مدیریت سفته های پرونده

         </h3>
     </div>
     <div class="row">
         <div class="col-md-3 m-5">
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
    @include('Admin.totalfactoritem.layouts.promissoryManagement')
 </div>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/promissory.js?v=1.0') }}"></script>
@endsection
