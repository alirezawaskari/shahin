@if($financial_plans_types)
<h6 class="text-primary">ملاحضات : درصورت انتخاب طرح مورد نظر برای پرداخت اقساط یا نقد مدارک و تضامین لازم را بررسی و آماده نمائید.</h6>
    <div class="row">
        @foreach($financial_plans_types as $item)
            <div class="col-12 col-md-8">
                <div class="bg-white shadow rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-dark">{{ $item->title }}</span>
                            <span class="text-success">{{ number_format($item->price)}} ریال</span>
                        </div>
                        <button class="btn btn-success font-12 py-1 add-to-cart" data-href="{{URL::to('/')}}" data-service_id="{{ $service->id }}" data-id="{{ $item->id }}" data-financial_plans_type_id="{{ $item->id }}" data-representation="{{ $representation }}" data-task_id="{{isset($task) && !is_null($task)?$task->id:''}}" data-callcenter="{{ $callcenter }}" data-factor="{{ isset($factor)?$factor:'' }}" data-type="item" {{ !$item->status ? 'disabled' : '' }}>
                            <i class="fas fa-cart-plus"></i>
                            @if(in_array(trim($service->id) , App\Models\AgencyType::pluck('service_id')->toArray()))
                                 جهت اعلام آمادگی اینجا کلیک کنید
                            @else
                               تکمیل فرایند ثبت نام
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <button class="btn btn-orange px-4 my-3 text-white add-to-cart" data-href="{{URL::to('/')}}" data-service_id="{{ $service->id }}" data-id="{{ $service->id }}" data-representation="{{ $representation }}" data-callcenter="{{ $callcenter }}" data-type="service">

        <i class="fas fa-cart-plus"></i> ثبت نام </button>
@endif
