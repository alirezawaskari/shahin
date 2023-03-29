<div class="card border col-12 mt-5 pt-4 col-l main">
    <h4 class="mb-3 card-header"><i class="fas fa-upload"></i> آپلود تصاویر قبل کار</h4>
    <form action="{{ route('factorItem.document')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
        <input type="hidden" name="status" value="4">
        <input type="hidden" name="status_factor"
            value="{{ $factorItem['factor']->representation_id && $factorItem['factor']->representation_id != 3 ? 11 : 9}}">
        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
        <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
        @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
        <div class="row p-3 position-relative">
            @if(!is_null($factorItem['forms']))
            @foreach($factorItem['forms'] as $form)
            @if (in_array('panel_upload_before_work_doc' , $form->data_display_level))
                @include('Admin.factoritem.layouts.formItem')
            @endif
            @endforeach
            @endif
            <div class="success-overly position-absolute mx-3"></div>
        </div>
        @endif
        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
    </form>
</div>
