@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">

            <form class="form-horizontal" method="post"
                  @if($pro_store_rooms && $pro_store_rooms->pivot )
                  action="{{route('updatePivotProductStoreRoom',$pro_store_rooms->pivot->id)}}"
                  @endif
                  enctype="multipart/form-data">
                {{csrf_field()}} {{ method_field('PATCH') }}
                @if(isset($product_id))
                    <input type="hidden" name="product_id" value="{{$product_id}}">
                @endif
                @include('Admin.partials.editProductStoreRoom',['store_rooms_rec'=>$pro_store_rooms,'type'=>$type_product])

                <br/>

                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>

            </form>

        </div>
    </div>

@stop

@section('css')

@stop

@section('js')
    <script type="text/javascript">
        if (($(".expire_date").length > 0)) {
            $('.expire_date').persianDatepicker({
                observer: true,
                format: 'YYYY-MM-DD HH:mm:ss',
                timePicker: {enabled: true, meridiem: {enabled: true}
                }
            });
        }
    </script>
@stop