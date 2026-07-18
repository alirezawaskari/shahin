@if (!is_null($factor_item['centers']) || !is_null($factor_item['forms']))
@if (!is_null($factor_item['centers']))
@permission('factor_item_referral_to_center')
<div class="tab">
    <div class="row">
        <button class="tablinks col-12 col-md-6" onclick="openTab(event, 'referralCenter{{$factorItem->id}}')">ارجاع به شعبه</button>

        <button id="defaultOpen" class="tablinks col-12 col-md-6"
            onclick="openTab(event, 'registrationInitialConsultation{{$factorItem->id}}')">ثبت مشاوره اولیه</button>
    </div>
</div>
<div id="referralCenter{{$factorItem->id}}" class="tabcontent mb-4">
    @include('Admin.factoritem.show.setCenter')
</div>

<div id="registrationInitialConsultation{{$factorItem->id}}" class="tabcontent mb-4">
    <form action="{{ route('factorItem.information')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $factorItem->id }}">
        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
        <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
        @if(!is_null($factor_item['centers']) || !is_null($factor_item['forms']))
        <div class="row p-3 position-relative">
            @if(!is_null($factor_item['forms']))
            @foreach($factorItem->factoritemable->beforeforms as $form)
            @if (in_array('panel_determine_center' , $form->data_display_level) || in_array('panel_basic_document' ,
            $form->data_display_level))
            @include('Admin.factoritem.layouts.formItem')
            @endif
            @endforeach
            @endif
            <div class="success-overly position-absolute mx-3"></div>
        </div>
        @if ($factorItem->status_factor == 16)
        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
        @else
        <p class="alert alert-warning">امکان ثبت مشاوره اولیه برای این پرونده وجود ندارد.</p>
        @endif
        @endif
    </form>
</div>
@if ($factorItem->status_factor == 16)
<form action="{{ $factorItem->type ? route('totalFactorItem.changeStatus') : route('factorItem.changeStatus') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
    <input type="hidden" name="status_factor" value="14">
    <button class="btn btn-danger mr-2 mb-3">ارجاع مجدد به پشتیبان</button>
</form>
@endif
@endpermission
@endif
@endif
