<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title','پنل مدیریت لاله')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
{{--    <link href="{{asset('assets/dashboard/css/bootstrap.min.css')}}" rel="stylesheet">--}}
 {{--    <link rel="stylesheet" href="{{asset('assets/dashboard/css/adminCss.css')}}">--}}
    <link rel="stylesheet" href="{{asset('assets/script/jquery-ui-1.13.2.custom/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-5.3.8-dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/dashboard.css?v=2.1') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/select2-admin.css?v=1.0') }}">

    <script src="{{asset('assets/dashboard/js/sweetalert.min.js')}}"></script>
     <!-- begin::favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/dashboard/images/laleh_icon.png') }}">
    <!-- end::favicon -->
    <!-- begin::theme color -->
    <meta name="theme-color" content="#3f51b5" />
    <!-- end::theme color -->
    @yield('css')
</head>
<body class="vertical-side-menu">
{{--@include('sweet::alert')--}}
@include('sweetalert::alert')
<div class="page-loader" id="gif">
    <div class="spinner-border"></div>
    <span>در حال بارگذاری ...</span>
</div>
    @include('Admin.layouts.sidemenu')
    @include('Admin.layouts.navbar')
    @include('Admin.layouts.terms')
<main class="main-content" id="app">
    <div class="container-fluid">
        @if(session('business_transfer_success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('business_transfer_success') }}
                <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="بستن">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(old('_business_transfer') && $errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </div>
</main>
<script src="{{asset('assets/dashboard/js/dashboard.js?v=2.7') }}"></script>
{{-- Select2 را روی هر select با فهرست بلند فعال می‌کند؛ باید بلافاصله بعد از
     dashboard.js (jQuery + Select2) و پیش از اسکریپت‌های صفحه بارگذاری شود. --}}
<script src="{{asset('assets/dashboard/js/select2-init.js?v=1.2')}}"></script>
{{-- تقویم واحد پنل: هر فیلد تاریخی که قبلا با pwt-datepicker ساخته می‌شد را به همان
     کامپوننت <date-picker> فیلترهای درخواست‌ها وصل می‌کند. باید بعد از dashboard.js
     (Vue و کامپوننت date-picker) بارگذاری شود. --}}
<script src="{{asset('assets/dashboard/js/jalaliDatePicker.js?v=1.0')}}"></script>
<script src="{{asset('assets/dashboard/js/datatables.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/site/adminJs.js?v=2.0')}}"></script>
<script src="{{asset('assets/dashboard/js/jquery.twbsPagination.min.js')}}"></script>
<script src="{{asset('assets/script/jquery-ui-1.13.2.custom/jquery-ui.js')}}"></script>
<script src="{{asset('assets/bootstrap-5.3.8-dist/js/bootstrap.min.js')}}"></script>
@permission('transfer_to_business')
    <script>window.BUSINESS_TRANSFER_URL = '{{ route('businessTransfer.store') }}';</script>
    <script src="{{ asset('assets/dashboard/js/businessTransfer.js?v=2.0') }}"></script>
@endpermission

<script>
  ck_uploader=  {
        filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token() ])}}",
      filebrowserUploadMethod: 'form'
    }
</script>
@include('Admin.layouts.errorLeft')
<script>
    $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip();
    });
    document.getElementById('toggleSidebarMobile').addEventListener('click', function () {
        const sidebar = document.getElementById('myNav');
        sidebar.classList.toggle('active');
    });
    document.getElementById('toggleSidebar').addEventListener('click', function () {
        const sidebar = document.getElementById('myNav');
        const icon = document.getElementById('menuIcon');
        const mainContent = document.querySelector('.main-content');
        if (mainContent.style.marginRight === '165px') {
            mainContent.style.marginRight = '';
        } else {
            mainContent.style.marginRight = '165px';
        }
        sidebar.classList.toggle('collapsed');
        icon.classList.toggle('rotated');
    });
</script>
@yield('js')
@yield('scripts')

<footer class="footer">
    <p>کلیه حقوق این نرم افزار  متعلق به شبکه خدمات رفاهی لاله می باشد.</p>
</footer>
</body>
</html>
