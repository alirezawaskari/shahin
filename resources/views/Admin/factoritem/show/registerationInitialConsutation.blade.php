<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link show active" id="registerationInitialConsutation-tab" data-toggle="tab" href="#registerationInitialConsutation" role="tab"
            aria-controls="registerationInitialConsutation" aria-selected="false">ثبت مشاوره اولیه</a>
    </li>
    <li class="nav-item">
        <a class="nav-link show" id="saveTrunRatings-tab" data-toggle="tab" href="#saveTrunRatings" role="tab"
            aria-controls="saveTrunRatings" aria-selected="false">تعیین نوبت مشاوره</a>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade p-t-20 active show" id="registerationInitialConsutation" role="tabpanel" aria-labelledby="registerationInitialConsutation-tab">
        <div class="card border shadow-lg">
            <div class="card-header">ثبت مشاوره اولیه :</div>
            <div class="card-body">
                <form action="{{ route('factorItem.setRegisterationInitialConsutation')}}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
                    <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                    <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
                    @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
                    <div class="row p-3 position-relative">
                        @if(!is_null($factorItem['forms']))
                        @foreach($factorItem['forms'] as $form)
                        @if (in_array('panel_determine_center' , $form->data_display_level) ||
                        in_array('panel_basic_document' , $form->data_display_level))
                        @include('Admin.factoritem.layouts.formItem')
                        @endif
                        @endforeach
                        @endif
                        <div class="success-overly position-absolute mx-3"></div>
                    </div>
                    @if (in_array(trim($factorItem['status_factor']) , [10,16,17]) )
                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
                    @else
                    <p class="alert alert-warning">امکان ثبت مشاوره اولیه برای این پرونده وجود ندارد.</p>
                    @endif
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="tab-pane fade p-t-20" id="saveTrunRatings" role="tabpanel" aria-labelledby="saveTrunRatings-tab">
        @if (in_array(trim($factorItem['status_factor']) , [10,16,17]) )
        @include('Admin.factoritem.show.turnRatings')
        @endif
    </div>
</div>
