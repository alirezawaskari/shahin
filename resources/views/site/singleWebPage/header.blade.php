<div class="header-representation">
    <div class="row pt-3">
        <div class="col-5 d-flex align-items-center">
            <a href=""><img class="mr-md-5 d-none d-md-block" src="{{ asset('assets/site/images/footer-logo.png') }}"
                    width="150px" alt="شبکه خدمات رفاهی لاله"></a>
            @auth
            <a href="/cart" class="btn btn-warning position-relative ml-2">
                <i class="fas fa-shopping-cart font-16 ml-2"></i>
                سبدخرید
                <span
                    class="cart-count d-flex justify-content-center align-items-center position-absolute font-12 text-dark"
                    id="cart-count">{{ session()->has('cart.total.count') ? session()->get('cart.total.count') : 0 }}</span>
            </a>
            @endauth
        </div>
        <div class="col-4 d-flex align-items-center text-center">
            <a href="{{ $singleWebPage->path() }}">
                <h5 class="text-dark text-center">{{ $singleWebPage->fa_name }}</h5>
            </a>
        </div>
        <div class="col-3 d-flex justify-content-end">
            <a href="{{ url('singleWebPage'.$singleWebPage->path()) }}">
                <span class="ml-md-5 text-dark font-16 d-flex align-items-center">
                    برگشت
                    <i class="fas fa-arrow-left text-orange font-18 m-2"></i>
                </span>
            </a>
        </div>
    </div>
</div>
