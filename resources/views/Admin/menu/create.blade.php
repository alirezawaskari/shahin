@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('menu.store')}}">
                @csrf

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="{{ old('title') }}"
                               class="form-control"
                               name="title" required>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>لینک</label>
                        <input type="text" value="{{ old('link') }}"
                               class="form-control"
                               name="link" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>سر دسته</label>
                        <select class="form-control"
                                name="parent">
                            <option value="0"
                                    @if(collect(old('parent'))->contains(0)) selected @endif>
                                دسته اصلی
                            </option>
                            @foreach($menus as $menu)
                                <option value="{{ $menu->id }}"
                                        @if(collect(old('parent'))->contains($menu->id)) selected @endif>{{ $menu->title }}</option>
                                @foreach($menu->getChild as $middmenu)
                                    <option value="{{ $middmenu->id }}"
                                            @if(collect(old('parent'))->contains($middmenu->id)) selected @endif>
                                        - {{ $middmenu->title }}</option>
                                    @foreach($middmenu->getChild as $minmenu)
                                        <option value="{{ $minmenu->id }}"
                                                @if(collect(old('parent'))->contains($minmenu->id)) selected @endif>
                                            -- {{ $minmenu->title }}</option>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
