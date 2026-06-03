
    <div class="col-12 col-md-3 text-center mt-4">
        <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action my-1 text-white text-center font-16 {{ Route::currentRouteName() == 'profile' ? 'active' : '' }}"
                href="{{ route('profile') }}">اطلاعات کاربری</a>
           {{-- <a class="list-group-item list-group-item-action my-1 text-white text-center font-16 {{ Route::currentRouteName() == 'profile.service' ? 'active' : '' }}"
               href="{{ route('profile.service') }}">خدمات</a>--}}
            <a style="display: none" class="list-group-item list-group-item-action my-1 text-white text-center font-16 {{ Route::currentRouteName() == 'profile.wallet' ? 'active' : '' }}"
               href="{{ route('profile.wallet') }}">مشاهده و افزایش اعتبار</a>

            <a class="list-group-item list-group-item-action my-1 text-white text-center font-16 {{ Route::currentRouteName() == 'profile.factor' ? 'active' : '' }}"
             href="{{ route('profile.factor') }}">پرداخت های انجام شده</a>
			 <a class="list-group-item list-group-item-action my-1 text-white text-center font-16 {{ Route::currentRouteName() == 'profile.factorItem' ? 'active' : '' }}"
             href="{{ route('profile.factorItem') }}">پرونده های من</a>
            <a class="list-group-item list-group-item-action my-1 text-white text-center font-16 {{ Route::currentRouteName() == 'profile.notifications' ? 'active' : '' }}"
               href="{{ route('profile.notifications') }}">پیام های من</a>

            <a href="{{ route('logout') }}" class="list-group-item list-group-item-action my-1 text-white text-center font-16 bg-dark"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                خروج از حساب</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                  style="display: none;">{{ csrf_field() }}</form>

        </div>
    </div>



