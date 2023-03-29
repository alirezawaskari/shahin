@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">

    <style>
    ul{
        cursor: default;
    }
    </style>
@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h4>نمایش دسته بندی کالا / خدمات در منو سایت</h4><br/>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        @if($selected_menu == 1 )
                            <div class="col-xs-3">
                                <ul id="1" data-type="typeOne" class="row_position">
                                    @foreach($menu as $index=>$item)
                                        <li data-type="typeOne" id="{{$item->id}}"
                                            class="borderLight @if($index == 0) active @endif">
                                            <a class="toShow curpon bg-dark-bright" id="{{$item->id}}"
                                               data-toggle="tab">{{$item->title}} </a>
                                        </li>
                                        @if($item->childs()->where('menuable_type',$item->menuable_type)->count() > 0 )
                                        <ul data-type="type{{$item->id}}"
                                            class="tab-pane @if(0 == $index) active @endif row_position padR200"
                                            id="box{{$item->id}}" style="display: none;">
                                            @foreach($item->childs()->where('menuable_type',$item->menuable_type)->get() as $child)

                                                <li data-type="type{{$item->id}}" id="{{$child->id}}"
                                                    class="posMenuLi toShow curpon ">{{$child->title}}
                                                    @if($child->duplicate == 0 && count($child->childs) > 0)
                                                        <ul data-type="type{{$child->id}}"
                                                            class="posMenuDiv row_position ulThree padR200"
                                                            id="box{{$child->id}}" style="display: none;">
                                                            @foreach($child->childs as $ch)
                                                                <li data-type="type{{$child->id}}" class="curpon borderLight"
                                                                    id="{{$ch->id}}">{{$ch->title}}</li>

                                                                @if(count($ch->childs) > 0)
                                                                    <ul data-type="type{{$ch->id}}"
                                                                        class="posMenuDiv row_position ulThree padR200">
                                                                        @foreach($ch->childs as $c)
                                                                            <li data-type="type{{$ch->id}}" class="borderLight"
                                                                                id="{{$c->id}}">{{$c->title}}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @endif

                                                    @if($child->duplicate == 1 && $child->duplicate_subset == 1)
                                                        <ul data-type="type{{$child->id}}"
                                                            class="posMenuDiv row_position ulThree padR200"
                                                            id="box{{$child->id}}" style="display: none;">
                                                            @foreach($child->childs as $ch)
                                                                <li data-type="type{{$child->id}}" class="curpon borderLight"
                                                                    id="{{$ch->id}}">{{$ch->title}}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        @endif

                                    @endforeach
                                </ul>
                            </div>


                        @elseif($selected_menu == 2 )
                            <div class="col-xs-3">
                                <ul id="2" data-type="typeOne" class="row_position">
                                    @foreach($menu as $index=>$item)

                                        <li data-type="typeOne" id="{{$item->id}}"
                                            class="padTB15 @if($index == 0) active @endif">
                                            <a class="toShow curpon bg-dark-bright" id="{{$item->id}}"
                                               data-toggle="tab">{{$item->title}}</a>
                                        </li>

                                        <ul data-type="type{{$item->id}}"
                                            class="tab-pane @if($index == 0) active @endif row_position padR200"
                                            id="box{{$item->id}}" style="display: none;">

                                            @foreach($item->childByRelation('App\Models\Service')->get() as $child)

                                                <li data-type="type{{$item->id}}" id="{{$child->id}}"
                                                    class="posMenuLi toShow curpon">
                                                    {{$child->title}}

                                                    <ul data-type="type{{$child->id}}"
                                                        class="posMenuDiv row_position ulThree padR200"
                                                        id="box{{$child->id}}" style="display: none;">

                                                        @foreach($child->childByRelation('App\Models\ServiceItem')->get() as $ch)
                                                            <li data-type="type{{$child->id}}" class="curpon"
                                                                id="{{$ch->id}}">{{$ch->title}}</li>
                                                        @endforeach
                                                    </ul>


                                                </li>
                                            @endforeach

                                        </ul>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/category/categoryPosition.js') }}"></script>

@stop
