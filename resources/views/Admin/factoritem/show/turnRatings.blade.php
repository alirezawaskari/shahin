@if (!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
@if (!is_null($factorItem['centers']))
    <div class="card shadow-lg">
        <div class="card-header">انتخاب مرکز : </div>
        <div class="card-body">
            <form action="{{ route('factorItem.setCenter') }}" method="POST">
            <div class="row">
                    @csrf
                <div class="col-12 col-md-6 mb-2">
                    <label for="factorItem[center]">انتخاب مرکز:</label>
                    <select name="center_id" class="form-control" required>
                        <option value="">انتخاب مرکز</option>
                        @foreach ($factorItem['centers'] as $center)
                                <option value="{{ $center->id }}" class="font-12" {{ $factorItem['center'] !=null && $factorItem['center']->id ==
                    $center->id ? 'selected' : '' }}>
                                {{ $center->title }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                </div>
                <div class="col-12 col-md-12  mt-auto">
                    <button type="submit" class="btn btn-success">ثبت</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <div class="card border shadow-lg">
        <div class="card-header">تعیین نوبت : </div>
        <div class="card-body">
            <form action="{{ route('factorItem.turnRatings') }}" method="POST">
                @csrf
                <div class="row">
                    <input type="hidden" name="type" value="supporter">
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                    <div class="col-md-3">
                        <label for="item[date]">زمان پیشنهادی جهت حضور در مرکز:</label>
                        <input type="text" name="date" class="form-control date" value="{{ $factorItem['date'] }}">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="description">توضیحات</label>
                        <textarea id="description" name="description" class="form-control">
                            </textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="turnRatingsCheck" checked="" name="check">
                            <label class="custom-control-label" for="turnRatingsCheck">زمان مراجعه برای کاربر
                                پیامک شود.</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-12  mt-auto">
                        <button type="submit" class="btn btn-success" {{ is_null($factorItem['center'] )? 'disabled': ''}}>ثبت</button>
                        @if(is_null($factorItem['center'] ))
                            <span class="text-warning"><i class="fa fa-warning"></i>جهت امکان ثبت نوبت لطفا ابتدا تعیین مرکز نمایید !</span>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
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
