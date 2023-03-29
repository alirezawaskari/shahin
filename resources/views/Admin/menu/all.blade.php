@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست منو</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست منو</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('menu.create') }}">آیتم جدید</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>لینک</th>
                        <th>والد</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($menus as $key => $menu)
                        <tr class="active" id="item_{{$menu->id}}" style="cursor: move;">
                            <td>{{$menu->title}}</td>
                            <td>{{$menu->link==null ? '-' : $menu->link}}</td>
                            <td>
                                @if(count($menu->getParent)==0)
                                    <span class="label label-info">اصلی</span>
                                @else
                                    @foreach($menu->getParent as $parent)
                                        <span class="label label-warning">{{$parent->title}}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>

                                <a href="{{ route('menu.edit' , [ $menu->id]) }}"
                                   class="btn btn-primary">ویرایش
                                </a>
                            </td>
                            <td>
                                <form class="delete_form" method="post"
                                      action="{{ route('menu.destroy',[$menu->id]) }}">
                                    @method('DELETE')
                                    @csrf

                                    <input type="button" class="btn btn-danger delete_submit"
                                           id="{{ $menu->id }}"
                                           value="حذف"/>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
@endsection
