@extends('ghorfeAdmin.master')
@section('title') جزییات دسته @endsection

@section('content')
    <div class="col-12">
        <div class="col-12 mb-3 border border-gray p-4 card" >
            <div class="row">
                @foreach($categoryPermission as $per)
                    <div class="col-12 col-md-2 mb-3 text-center">
                        <div class="border border-info rounded " style="padding: 5px">
                            <span class="text-dark">
                                {{$per->name}}
                                <hr>
                                {{$per->display_name}}
                              <a href="{{route('permission.edit',[$per->id])}}">
                                <i class="fa fa-edit btn btn-small btn-outline-primary"></i>
                            </a>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop


