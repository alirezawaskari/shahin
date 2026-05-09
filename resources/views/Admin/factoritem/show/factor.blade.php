<div class="panel">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a href="#factors" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion">فاکتور</a>
        </h5>
    </div>
    <div id="factors" class="panel-collapse collapse in">
        <div class="panel-body">
            <div class="card col-12 mt-2 pt-4 col-l main border">
                @if (!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
                <div class="row p-3 position-relative">
                    @if (!is_null($factorItem['forms']))
                    @foreach ($factorItem['factorItems'] as $factoritem)
                    <div class="row col-12 col-md-12 mb-3">
                        <h5>{{ $factoritem['factoritemable']->title . ($factoritem['center'] ? ' (
                            '.$factoritem['center']->title.' ) ' : '') }}</h5>
                    </div>
                    <div class="card-header row col-12 mb-4">
                        <div class="col-12 col-md-3">
                        @if(!$factorItem['total_price'] || $factorItem['status_factor'] == 14 || $factorItem['status_factor'] == 4)
                        <a class="btn border border-secondary box-shadow-info"
                                data-toggle="modal" data-target="#changeFactoritemableModal"><i
                            class="fa fa-edit"></i> تغییر خدمت | دسته بندی</a>
                            @include('Admin.factoritem.layouts.file.changeFactorItemAble')
                        @endif
                        </div>
                        <div class="col-12 col-md-3">
                            <a class="btn border border-secondary box-shadow-info" data-toggle="modal"
                                data-target="#changeFinancialPlansTypeModal"><i class="fa fa-edit"></i> تغییر طرح</a>
                        </div>
                        <div class="col-12 col-md-3">
                            <a class="btn border border-secondary box-shadow-info"
                                href="../../dashboard/factorItem/{{ $factoritem['resnumber'] }}/edit"><i
                                    class="fa fa-edit"></i> امور اجرایی پرونده</a>
                        </div>

                    </div>
                    @foreach ($factorItem['forms'] as $form)
                    @if (in_array('panel_show_file' , $form->data_display_level))
                        @include('Admin.factoritem.layouts.formItem')
                    @endif
                    @endforeach
                    @endforeach
                    @endif
                    <div class="success-overly position-absolute mx-3"></div>
                </div>
                @else
                <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                    <div>
                        @if (!is_null($factorItem['service_item']))
                        <span>{{ $factorItem['factoritemable']->title . '-' .
                            $factorItem['service_item']->title }}</span>
                        @else
                        <span>{{ $factorItem['factoritemable']->title }}</span>
                        @endif
                    </div>
                    <i class="fa fa-check font-18 text-success"></i>
                </div>
                @endif
                <form class="form-horizontal" role="form" method="post"
                    action="{{$factorItem['total_price'] == 0 ? route('factorItem.createFactor' , [ $factorItem['resnumber']]) : route('factorItem.calculate') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                    <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
                    <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                    <div class="row">
                        <div class="col-12 col-md-3 mb-2">
                            <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                            <input class="form-control" value="{{ number_format($factorItem['total_price']) }}"
                                name="totalPrice" oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 col-md-3 mb-2" data-toggle="tooltip" data-placement="bottom"
                            title=" درصورت گذاشتن  ' - (منفی)' این مبلغ از جمع کل فاکتور کسر می شود.">
                            <label>مبلغ تخفیف</label>
                            <input class="form-control" value="{{ number_format($factorItem['discount']) }}"
                                name="discount" type="text" oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 mb-3">
                            <p class="alert alert-danger">همکار گرامی توجه داشته باشید ، درصورتی که فاکتوری از قبل برای
                                پرونده ثبت شده باشد امکان تغییر جمع کل فاکتور بصورت دستی وجود ندارد.</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label> نوع پرداخت</label>
                            <br>
                            نقد <input type="radio" name="type" value="0" {{ $factorItem['type']=='0' ? 'checked' : ''
                                }}>
                            اقساط<input type="radio" name="type" value="1" {{ $factorItem['type']=='1' ? 'checked' : ''
                                }}>
                        </div>
                    </div>
                    @permission('financial-info-file-edit')
                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $item['confirm'] ? 'disabled'
                        : '' }}>
                    @endpermission
                </form>
                @include('Admin.factoritem.layouts.file.factorHistories')
            </div>
        </div>
    </div>
</div>
@include('Admin.factoritem.layouts.file.changeFinancialPlansType')
