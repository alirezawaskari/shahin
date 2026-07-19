@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title"><h4>ویرایش پک</h4></div>

            <form class="form-horizontal" method="post" action="{{route('pack.update',$pack->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}} {{ method_field('PATCH') }}
                <div class="row">
                    @if($pack_centers && $pack_centers->pivot)
                        @include('Admin.partials.pack.editPackCenter',['pack'=>$pack])
                    @endif
                </div>
                @if($pack_store_rooms && $pack_store_rooms->pivot)
                    @include('Admin.partials.editProductStoreRoom',['store_rooms_rec'=>$pack_store_rooms,'type'=>0])
                @endif
                <div class="row">
                    @include('Admin.partials.pack.editPack',['pack'=>$pack])

                    <div class="col-12 col-md-12 mb-3">
                        <button type="submit" class="btn btn-success">ویرایش</button>

                    </div>
                </div>

        </div>
        </form>

    </div>
    </div>
@stop

@section('css')

@stop

@section('js')

    <script src="/assets/dashboard/ckeditor/ckeditor.js"></script>
    <script src="/assets/dashboard/js/site/pack/packEdit.js"></script>


@stop