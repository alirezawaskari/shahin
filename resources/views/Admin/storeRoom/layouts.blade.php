<div class="heading-elements">
            <div class="heading-btn-group ltr mb-3">
                @permission('supplier')
                <a href="{{ route('supplier.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>فهرست تامین کنندگان</span>
                </a>
                @endpermission
                @permission('store-room')
                <a href="{{ route('countingUnit.index') }}"
                   class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                    <span>فهرست واحدها</span>
                </a>
                @endpermission
                @permission('product' | 'product-detail')
                <a href="{{ route('product.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                    <span>فهرست کالا ها</span>
                </a>
                @endpermission
                @permission('store-room')
                <a href="{{ route('storeRoom.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>فهرست انبار</span>
                </a>
                @endpermission
            </div>
        </div>
