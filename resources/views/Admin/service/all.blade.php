@extends('Admin.master')
@section('title')فهرست خدمات@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست خدمات</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست خدمات</li>
                </ol>
            </nav>
        </div>
        <div>
            @permission('create_service')
            <a class="btn btn-primary" href="{{ route('service.create') }}">خدمت جدید</a>
            @endpermission
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <form method="get" action="{{route('service.index')}}">
                <div class="row">
                <div class="col-12 col-md-3 mb-2 disInline">
                    <input class="form-control" name="title"
                           value="@if(isset($queries['title']) && $queries['title'] != null) {{$queries['title']}} @endif"
                           placeholder="عنوان">
                </div>
                <div class="col-12 col-md-3 mb-2 disInline">
                    <select title="pay_type" name="pay_type" class="custom-select">
                        <option @if(isset($queries['pay_type']) && $queries['pay_type'] == 'all') selected
                                @endif value="all">نوع پرداخت
                        </option>
                        @foreach($pay_type as $ind=>$pt)
                            <option @if(isset($queries['pay_type']) && $queries['pay_type'] == $ind) selected @endif
                            value="{{$ind}}">{{$pt}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 disInline">
                    <select name="purchase_type" class="custom-select" title="">
                        <option @if(isset($queries['purchase_type']) && $queries['purchase_type'] == 'all')selected
                                @endif value="all">نوع خرید
                        </option>
                        @foreach($purchase_type as $ind=>$put)
                            <option @if(isset($queries['purchase_type']) && $queries['purchase_type'] == $ind) selected
                                    @endif value="{{$ind}}">{{$put}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2 disInline">
                    <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            @if (count($services) > 0)
                <section class="dataPagination">
                    @include('Admin.service.load')
                </section>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script>
        $('body').on('click', '#filter', function(e) {
            e.preventDefault();
            var title = $('input[name="title"]').val() ;
            var pay_type = $('select[name="pay_type"]').val() ;
            var purchase_type = $('select[name="purchase_type"]').val() ;
            $.ajax({
                type: "get",
                url: '/dashboard/service',
                data: {title: title,pay_type: pay_type, purchase_type : purchase_type},
                success: function (result){
                    $('.dataPagination').html(result);
                }
            });

        });

        $(function() {
            $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();
                $('.load').append('<img style="position: absolute; left: 50%; top: 0; z-index: 100000;" src="/upload/demo_wait.gif" />');
                var url = $(this).attr('href');
                getDataPagination(url);
                window.history.pushState("", "", url);
            });
            function getDataPagination(url) {
                $.ajax({url : url}).done(function (data)
                {
                    $('.dataPagination').html(data);
                });
            }
        });
    </script>
@endsection
