<div class="heading-elements">
            <div class="heading-btn-group ltr mb-3">
                @permission('safe-box')
                <a href="{{ route('supplier.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>خزانه جدید</span>
                </a>
                @endpermission
            </div>
        </div>