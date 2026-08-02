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
                    <th>مربوط به</th>
                    <th>متن معرفی</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($abouts as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        {{-- Owner of the row: a ghorfe, a representation, or the main site. --}}
                        <td>
                            @if($item->ghorfeOnline)
                                <span class="badge badge-info">کسب و کار</span> {{ $item->ghorfeOnline->title }}
                            @elseif($item->representation)
                                <span class="badge badge-warning">نمایندگی</span> {{ $item->representation->fa_name }}
                            @else
                                <span class="badge badge-secondary">سایت اصلی</span>
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->intro_text), 120) }}</td>
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