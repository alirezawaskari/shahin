@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست عامیلت ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">عامیلت ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary dropdown-toggle">اقدامات</button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                    <a href="{{route('agencyType.create')}}" class="dropdown-item"> ایجاد  </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>عکس</th>
                        <th>عنوان</th>
                        <th>عنوان انگلیسی</th>
                        <th>قیمت کل</th>
                        <th>پیش پرداخت </th>
                        <th>نمایش در سایت </th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($agencyTypes as $item)
                        <tr>
                            <td>
                                @if($item->image)
                                    <img src="{{$item->image}}" class="border-radius5" width="60px">
                                @endif
                            </td>
                            <td>{{$item->title}} </td>
                            <td>{{$item->en_title}} </td>
                            <td>{{number_format($item->total_price)}} </td>
                            <td>{{number_format($item->prepayment)}} </td>
                            <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->is_show) !!} </td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{route('agencyType.edit',[$item->id])}}">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                        {{-- positional, like the edit link above: the route parameter is
                                             {agencyType}, so an ['id'=>…] key never fills it (UrlGenerationException) --}}
                                        <form method="post" action="{{route('agencyType.destroy',[$item->id])}}">
                                            {{ method_field('DELETE') }}  {{csrf_field()}}
                                            <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            {{$agencyTypes->links()}}
        </div>
    </div>


@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
