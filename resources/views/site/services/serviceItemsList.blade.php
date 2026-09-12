<style>
.item-check {
    width: 26px;
	margin-left : 7px;
    height: 26px;
    border-radius: 50%;
    background: #198754;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(25,135,84,.2);
}
</style>
@if(isset($service_items))
@foreach ($service_items as $service_item)
    <div class="row alert alert-success align-items-center">
        <div class="col-8 d-flex align-items-center gap-2">
            {{-- آیکن تیک سبز ثابت --}}
            <span class="item-check">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                     stroke="currentColor" stroke-width="3"
                     stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </span>

            <h5 class="card-title mb-0">{{ $service_item->title }}</h5>
        </div>

        <div class="col-4 text-end">
            <a href="{{ route('services.serviceItem.show', $service_item->slug) }}"
               class="btn btn-primary">
                ثبت نام
            </a>
        </div>
    </div>
@endforeach
@endif