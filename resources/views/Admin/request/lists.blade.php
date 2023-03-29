@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3> درخواست ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست درخواست ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('request.create')}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" action="{{route('request.index')}}">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="name" placeholder="نام">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="family" placeholder="نام خانوادگی">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select title="status" name="status" class="custom-select">
                            <option @if(isset($queries['status'])) @if($queries['status'] == 'all') selected
                                    @endif @endif value="all">وضعیت
                            </option>
                            @foreach($statuses as $ind=>$status)
                                <option @if(isset($queries['status'])) @if($queries['status'] == $ind) selected
                                        @endif @endif value="{{$ind}}"> {{$status}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select title="store_room_id" name="store_room_id" class="custom-select">
                            <option value="all">انبار
                            </option>
                            @foreach($store_rooms as $ind=>$store)
                                <option value="{{$store->id}}"> {{$store->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select title="card_id" name="card_id" class="custom-select">
                            <option value="all">واحد
                            </option>
                            @foreach($cards as $card)
                                <option value="{{$card->id}}"> {{$card->title}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2 mb-2">
                        <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            @if (count($request_lists) > 0)
                <section class="dataPagination">
                    @include('Admin.request.load')
                </section>
            @endif
        </div>
    </div>

@stop
@section('js')

    <script>
        $('body').on('click', '#filter', function (e) {
            e.preventDefault();
            var name = $('input[name="name"]').val();
            var family = $('input[name="family"]').val();
            var status = $('select[name="status"]').val();
            var store_room_id = $('select[name="store_room_id"]').val();
            var card_id = $('select[name="card_id"]').val();
            var type_id = $('input[name="type_id"]').val();

            $.ajax({
                type: "get",
                data: {
                    name: name,
                    family: family, status: status, store_room_id: store_room_id,
                    card_id: card_id, type_id: type_id
                },
                success: function (result) {
                    $('.dataPagination').html(result);
                }
            });

            // $.ajax({url : '/dashboard/products/'.type_id}).done(function (data) {$('.dataPagination').html(data);});


        });

        $(function () {
            $('body').on('click', '.pagination a', function (e) {
                e.preventDefault();
                $('.load').append('<img style="position: absolute; left: 50%; top: 0; z-index: 100000;" src="/upload/demo_wait.gif" />');
                var url = $(this).attr('href');
                getDataPagination(url);
                window.history.pushState("", "", url);
            });

            function getDataPagination(url) {
                $.ajax({url: url}).done(function (data) {
                    $('.dataPagination').html(data);
                });
            }
        });

    </script>

@stop
