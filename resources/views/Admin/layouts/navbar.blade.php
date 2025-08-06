<nav class="navbar mb-5" style="z-index: 1200">
    <div class="container-fluid">
        <button class="d-none d-lg-block toggle-btn custom-menu-btn mr-5" id="toggleSidebar">
            <i class="icon ti-menu text-white menu-icon" style="font-size: 1.3rem;" id="menuIcon"></i>
        </button>
        <div class="d-flex align-items-center">
            <img class="d-none d-sm-block" src="{{ asset('assets/dashboard/images/l-d.png') }}" width="60px" height="30px">
        </div>
        <div class="d-flex align-items-center">
        <a href="/dashboard" class="text-center d-none d-md-block text-white mr-2">
            شبکه الکترونیکی هوشمند یکپارچه(شاهین)
        </a>
        </div>
        <div class="header-body" style="flex: auto; direction:ltr">
            <ul class="navbar-nav">
                @if(!auth()->user()->admin_organization_id)
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a href="#" class="nav-link" data-toggle="dropdown">
                            <i class="fa fa-th-large"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-nav-grid">
                            <div class="dropdown-menu-title">منوی سریع</div>
                            <div class="dropdown-menu-body">
                                <div class="nav-grid">
                                    <div class="nav-grid-row">
                                        <a href="{{ route('callcenter.index') }}" class="nav-grid-item">
                                            <i class="fa fa-trello"></i>
                                            <span>درخواست ها</span>
                                        </a>
                                        <a href="{{ route('factor.index') }}" class="nav-grid-item">
                                            <i class="fa fa-money"></i>
                                            <span>فاکتور ها</span>
                                        </a>
                                    </div>
                                    <div class="nav-grid-row">
                                        <a href="/dashboard/dailyDesktop" class="nav-grid-item">
                                            <i class="fa fa-tasks"></i>
                                            <span>کارتابل روزانه</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endif
                    <li class="nav-item  d-none d-lg-block text-center">
                        <div class="text-center  " style="padding: 5px">
                            {{verta()->now()->formatWord('l').' '.verta()->now()->format('%Y/%m/%d')}}
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <span class="text-center" style="padding: 5px;cursor: pointer" type="button"  data-toggle="modal" data-target="#exampleModalCenter">
                            <i class="fa fa-user-plus text-info" ></i>
                        </span>
                        @if(auth()->user()->hasRole('presentore'))
                            <span>{{ auth()->user()->name . ' ' .auth()->user()->family }} (سمت : {{ auth()->user()->employee->work_title . auth()->user()->id}})</span>
                        @else
                            <span>{{ auth()->user()->name.' '.auth()->user()->family }} (سمت : {{ auth()->user()->employee->work_title}})</span>
                        @endif

                        <a href="#" data-toggle="dropdown">
                            <figure class="avatar avatar-sm avatar-state-success">
                                <img class="rounded-circle" src="{{ asset(auth()->user()->avatar) }}" alt="...">
                            </figure>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="login.html" class="text-danger dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">خروج</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                  style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item d-lg-none d-sm-block">
                    <a href="#" id="toggleSidebarMobile" class="nav-link side-menu-open">
                        <i class="ti-menu"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div>
            <a href="{{ auth()->user()->admin_representation_id ? '/'.auth()->user()->admin_representation->slug : '/' }}" class="text-white mr-3">برو به سایت <i class="fa fa-angle-double-left" aria-hidden="true"></i></a>
        </div>
    </div>
</nav>
<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><i class="fa fa-user-plus"></i> <span style="padding-right: 3px">افزودن کاربر</span>  </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/dashboard/create-user" id="create-user-form" method="POST">
            <div class="modal-body">
              <div class="row ">
                  <div class="form-group col-md-4">
                      <label>شماره موبایل </label>
                      <input id="user-phone" name="phone"
                             oninput=""
                             class="form-control" placeholder="شماره موبایل"
                             value="" required>
                      <p class="text-danger text-center exporter_error_user"></p>
                  </div>

                  <div class="form-group col-md-4">
                      <label>نام</label>
                      <input class="form-control my-2 user-name" type="text"
                             id="new_user_name" name="name" required
                             placeholder="نام" value="" pattern="^[\u0600-\u06FF\s]+$"
                             title="لطفا با حروف فارسی وارد نمایید">
                      @if ($errors->has('name'))
                          <span
                              class="d-block text-red text-left">{{ $errors->first('name')}}</span>
                      @endif
                  </div>

                  <div class="form-group col-md-4">
                      <label>نام خانوادگی</label>
                      <input class="form-control my-2 user_family" type="text"
                             id="new_user_family" name="family" required
                             placeholder="نام خانوادگی" value=""
                             pattern="^[\u0600-\u06FF\s]+$"
                             title="لطفا با حروف فارسی وارد نمایید">
                      @if ($errors->has('family'))
                          <span
                              class="d-block text-red text-left">{{ $errors->first('family')}}</span>
                      @endif
                  </div>
                  <div class="form-group col-md-4">
                      <label> جنیست</label>
                      <select class="form-control my-2 gender" type="int"
                              name="gender" id="new_user_gender"
                              required>
                          <option selected value="">انتخاب نمایید</option>
                          <option value="0">خانم</option>
                          <option value="1">آقا</option>
                      </select>
                  </div>
                  <div class="form-group col-md-4">
                      <label>انتخاب استان</label>
                      <select class="form-control my-2 user_province_id" type="int"
                              name="province" id="new_user_province_id"
                              required>
                          <option value="">انتخاب استان</option>
                          @foreach(\App\Models\Province::all() as $province)
                              <option value="{{ $province->id }}">{{ $province->name }}
                              </option>
                          @endforeach
                      </select>
                  </div>
                  <div class="form-group col-md-4">
                      <label>انتخاب شهر</label>
                      <select class="form-control my-2 user_city_id" type="int"
                              name="city" id="new_user_city_id" required>
                          <option value="">انتخاب شهر</option>
                          @foreach(\App\Models\City::all() as $city)
                              <option value="{{ $city->id }}">{{ $city->name }}</option>
                          @endforeach
                      </select>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
                <button type="submit" class="btn btn-primary">ثبت کاربر</button>
            </div>
            </form>
        </div>
    </div>
</div>
