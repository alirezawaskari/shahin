<div class="card border col-12 mt-5 pt-4 col-l main">
    <h4 class="mb-3 card-header"><i class="fas fa-hourglass-end"></i> اتمام کار</h4>
    <form action="{{ route('factorItem.completion')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
        <input type="hidden" name="status" value="5">
        <input type="hidden" name="status_factor" value="7">
        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
        <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
        @if(!is_null($factorItem['forms']))
        @foreach($factorItem['forms'] as $form)
        @if (in_array('panel_complation_file_work' , $form->data_display_level))
        @include('Admin.factoritem.layouts.formItem')
        @endif
        @endforeach
        @endif
        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $factorItem->status_factor == 7 ?
        'disabled': ''}}>
    </form>
</div>
