@if (!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
@if (!is_null($factorItem['centers']))
    @permission('factor_item_referral_to_center')
    <div class="tab">
        <div class="row">
            <button class="tablinks col-12 col-md-6" onclick="openTab(event, 'referralCenter')">ارجاع به مرکز</button>

            <button id="defaultOpen" class="tablinks col-12 col-md-6"
                onclick="openTab(event, 'registrationInitialConsultation')">ثبت مشاوره اولیه</button>
        </div>
    </div>
    <div id="referralCenter" class="tabcontent mb-4">
        @include('Admin.factoritem.show.referralCenter')
    </div>

    <div id="registrationInitialConsultation" class="tabcontent mb-4">
        <form action="{{ route('factorItem.information')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
            <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
            <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
            @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
            <div class="row p-3 position-relative">
                @if(!is_null($factorItem['forms']))
                @foreach($factorItem['forms'] as $form)
                @if (in_array('panel_determine_center' , $form->data_display_level))
                    @include('Admin.factoritem.layouts.formItem')
                @endif
                @endforeach
                @endif
                <div class="success-overly position-absolute mx-3"></div>
            </div>
            @if ($factorItem['status_factor'] == 16)
            <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
            @else
            <p class="alert alert-warning">امکان ثبت مشاوره اولیه برای این پرونده وجود ندارد.</p>
            @endif
            @endif
        </form>
    </div>
    @if ($factorItem['status_factor'] == 16)
    <form action="{{ $factorItem['type'] ? route('totalFactorItem.changeStatus') : route('factorItem.changeStatus') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="14">
        <button class="btn btn-danger mr-2 mb-3">ارجاع مجدد به پشتیبان</button>
    </form>
    @endif
    @endpermission
    @include('Admin.factoritem.show.turnRatings')
@endif
<div class="{{ $factorItem['complete'] ? 'bg-success-bright' : 'bg-light' }} p-3 rounded mb-3 shadow-lg border">
    <div class="mb-3">
        @if (!is_null($factorItem['service_item']))
        <span>{{ $factorItem['factoritemable']->title . '-' . $factorItem['service_item']->title }}</span>
        @else
        <span>{{ $factorItem['factoritemable']->title }}</span>
        @endif
        @if ($factorItem['complete'])
        <span class="text-warning mr-2">(تکمیل شده)</span>
        @endif
        @if(!$factorItem['total_price'] || $factorItem['status_factor'] == 14 || $factorItem['status_factor'] == 4)
        <button class="btn btn-danger" data-toggle="modal" data-target="#changeFactoritemableModal">تغییر
            خدمت | دسته بندی</button>
        @endif
    </div>
    <div>
        @if ($factorItem['complete'])
        <span class="text-warning">{{ $factorItem['date'] }}</span>
        <span class="text-dark mr-3">({{ $factorItem['center'] != null ? $factorItem['center']->title : ''
            }})</span>
        <div class="table-responsive w-100">
            <table class="table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>مقدار</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!is_null($factorItem['site_forms']))

                    @foreach ($factorItem['site_forms'] as $form)

                    @if (isset($factorItem['factor_values'][$form->name]) && in_array('site' , $form->data_display_level))
                    <tr>
                        <td>
                            {{ $form->placeholder }}
                        </td>
                        <td>
                            @if ($form->type == 'multi-select' && isset($factorItem['factor_values'][$form->name])&&is_array($factorItem['factor_values'][$form->name]) )
                            {{ implode(',', $factorItem['factor_values'][$form->name]) }}
                            @elseif($form->type == 'file')
                            <a href="{{ asset($factorItem['factor_values'][$form->name]) }}" class="btn btn-primary"
                                target="_blank">مشاهده</a>
                            @elseif($form->type == 'multi_file')
                            @foreach($factorItem['factor_values'][$form->name] as $image)
                                <a href="{{ asset($image) }}" class="btn btn-primary"
                                    target="_blank">مشاهده</a>
                            @endforeach
                            @else
                            {{ $factorItem['factor_values'][$form->name] }}
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif
<div class="modal fade" id="changeFactoritemableModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">تغییر خدمت پرونده</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('factorItem.changeFactoritemable') }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="form-row">
                        <div class="col-12 mb-3">
                            کالا <input type="radio" name="cat_type{{ $factorItem['id'] }}" value="category"
                                onclick="catType(this.value , {{ $factorItem['id'] }})" checked>
                            خدمات <input type="radio" name="cat_type{{ $factorItem['id'] }}" value="service"
                                onclick="catType(this.value , {{ $factorItem['id'] }})">
                        </div>
                        <div class="col-12 mb-5">
                            <select name="service{{ $factorItem['id'] }}" class="form-control d-none"
                                id="service{{ $factorItem['id'] }}">
                                @foreach (App\Models\Service::all() as $service)
                                <option value="{{ $service->id }}">{{ $service->title}}</option>
                                @endforeach
                            </select>
                            <select name="category{{ $factorItem['id'] }}" class="form-control"
                                id="category{{ $factorItem['id'] }}">

                                @foreach(App\Models\Category::where('parent_id' , 1)->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" value="{{ $factorItem['id'] }}" name="factor_item">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>
