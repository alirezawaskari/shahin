<div class="card border col-12 mt-5 pt-4 col-l main">
    <h4 class="mb-3 card-header"><i class="fas fa-user"></i> فرم اطلاعات اولیه کاربر</h4>
    @foreach($errors->all() as $error)
    <div class="alert alert-danger" role="alert">
        {{ $error }}
    </div>
    @endforeach
    <form action="{{ route('factorItem.information')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
        <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
        @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
        <div class="row p-3 position-relative">
            {{-- <div class="col-12 col-md-3 mb-2">
                <label>شماره پرونده</label>
                <input type="text" name="file_number" class="form-control border-warning mb-2"
                       value="{{ $factorItem['file_number'] }}">
            </div> --}}
            @if(!is_null($factorItem['forms']))
            @foreach($factorItem['forms'] as $form)
            @if (in_array('panel_user_info' , $form->data_display_level))
                @include('Admin.factoritem.layouts.formItem')
            @endif
            @endforeach
            @endif
            <div class="success-overly position-absolute mx-3"></div>
        </div>
        <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" class="custom-control-input" id="customCheck" onclick="setMobile()">
            <label class="custom-control-label" for="customCheck">دارای سرپرست</label>
        </div>
        <div class="col-12 col-md-4 mb-5">
            <input class="form-control" name="mobile_supervisor" id="mobile_supervisor" type="text" minlength="11"
                maxlength="11"
                value="{{ $factorItem->supervisor ? $factorItem->supervisor->factor->user->phone : '' }}">
        </div>
        @endif
        <input type="submit" class="btn btn-primary px-4 mb-3" value="بعدی">
    </form>
</div>
<script>
    document.getElementById('mobile_supervisor').style.display = 'none';
</script>
