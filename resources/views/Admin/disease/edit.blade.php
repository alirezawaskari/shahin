@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('menu.update' , [$menu->id ])}}">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="{{ $menu->title }}"
                               class="form-control"
                               name="title" required>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>لینک</label>
                        <input type="text" value="{{ $menu->link }}"
                               class="form-control"
                               name="link" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>سر دسته</label>
                        <select class="form-control"
                                name="parent" id="menu">
                            <option value="0" @if($menu->parent_id == 0) selected @endif>دسته
                                اصلی
                            </option>
                            @foreach($menus as $item)
                                @if($item->id!=$menu->id)
                                    <option value="{{ $item->id }}"
                                            @if($menu->parent_id==$item->id) selected @endif>{{ $item->title }}</option>
                                    @foreach($item->getChild as $midmenu)
                                        @if($midmenu->id!=$menu->id)
                                            <option value="{{ $midmenu->id }}"
                                                    @if($menu->parent_id==$midmenu->id) selected @endif>
                                                - {{ $midmenu->title }}</option>
                                            @foreach($midmenu->getChild as $minmenu)
                                                @if($minmenu->id!=$menu->id)
                                                    <option value="{{ $minmenu->id }}"
                                                            @if($menu->parent_id==$minmenu->id) selected @endif>
                                                        -- {{ $minmenu->title }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label>آدرس</label>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
