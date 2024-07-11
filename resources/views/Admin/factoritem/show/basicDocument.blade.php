<div id="accordion" class="panel-group">
    <div class="panel">
        <div class="panel-heading">
            <h5 class="panel-title">
                <a href="#basic_document_executive" class="accordion-toggle collapsed" data-toggle="collapse"
                    data-parent="#accordion">مدارک اولیه امور اجرایی</a>
            </h5>
        </div>
        <div id="basic_document_executive" class="panel-collapse collapse">
            <div class="panel-body">
                <form action="{{ route('factorItem.information')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
                    <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                    <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
                    @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
                    <div class="row p-3 position-relative">
                        @if(!is_null($factorItem['forms']))
                        @foreach($factorItem['forms'] as $form)
                        @if (in_array('panel_basic_document' , $form->data_display_level))
                            @include('Admin.factoritem.layouts.formItem')
                        @endif
                        @endforeach
                        @endif
                        <div class="success-overly position-absolute mx-3"></div>
                    </div>
                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
<span class="btn btn-primary py-1 px-2 text-white mt-4" data-toggle="modal" data-target="#linkModal">ارسال لینک
    پرداخت</span>
    @if (in_array(trim($factorItem['status_factor']) , [0,4,14]) )
<a href="{{route('factorItem.referral' , [ $factorItem['resnumber']])}}"><span class="btn btn-warning py-1 px-2 text-white mt-4">ارجاع به
        مشاوره</span></a>

    @endif

    @permission('supporter_confirmation')
    <div class="mt-3">
    @if (in_array(trim($factorItem['status_factor']) , [0,4,14]) )
        @include('Admin.factoritem.show.referralCenter')
    <p class="alert alert-warning col-12"> همکار محترم ارجاع به مرکز به منظور تایید پرونده از جانب شما برای شروع کار اجرایی پرونده می باشد.</p>
    @endif
    </div>
    @endpermission
