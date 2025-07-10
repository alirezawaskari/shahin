<div class="card border col-12 mt-5 pt-4 col-l main ">
    <div class="card-body">
        @php($message = Session::get('message'))
        <form action="{{ route('factorItem.updateServiceTariff')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @foreach($factorItem['forms'] as $form)
                @if (in_array('panel_factor' , $form->data_display_level))
                    @switch($form->type)
                        @case('factor_dentist')
                            @include('Admin.factoritem.dentist.list')
                            @break
                        @case('factor_building')
                            @include('Admin.factoritem.building.list')
                            @break
                    @endswitch
                @endif
            @endforeach
        </form>
    </div>
    <div class="col-md-12 border border-secondary box mb-5">
        <form action="{{ route('factorItem.createFactor')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @foreach($factorItem['forms'] as $form)
                @if (in_array('panel_factor' , $form->data_display_level))
                    @switch($form->type)
                        @case('factor_dentist')
                            @include('Admin.factoritem.dentist.create')
                            @break
                        @case('factor_building')
                            @include('Admin.factoritem.building.create')
                            @break
                    @endswitch
                @endif
            @endforeach
            <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}" id="factorItem">
            <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
            <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
            <div class="row">

                @if(!is_null($factorItem['forms']))
                    @foreach($factorItem['forms'] as $form)
                        @if (in_array('panel_factor' , $form->data_display_level))
                            @include('Admin.factoritem.layouts.formItem')
                        @endif
                    @endforeach
                @endif
                <br/>
                <input type="hidden" value="{{ $factorItem['id'] }}" name="id">
                <div class="col-12 col-md-12 mb-12">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="customCheck" name="checked">
                        </div>
                    </div>
                </div>
                <p></p>
                <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" id="btnSave">
            </div>
        </form>
    </div>
</div>
