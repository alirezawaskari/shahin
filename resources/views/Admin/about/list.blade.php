@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>درباره ما</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">درباره ما</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>شناسه</th>
                    <th>مربوط به صفحه</th>
                    <th>متن باکس اول</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($abouts as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>@if($item->id == 1)    درباره ما
                        @elseif($item->id == 2) فروش نقدی با تخفیف
                        @elseif($item->id == 3)فروش اقساطی
                        @endif
                        </td>
                        <td>{!! $item->content_one !!}</td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" 
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('about.edit',$item->id)}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>




@stop
@section('js')
@stop