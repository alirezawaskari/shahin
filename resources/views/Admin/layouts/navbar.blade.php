<nav class="navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <img class="d-none d-sm-block" src="{{ asset('assets/dashboard/images/l-d.png') }}" width="60px" height="30px">
        </div>
        <div class="d-flex align-items-center">
        <a href="/dashboard" class="text-center d-none d-md-block text-white mr-2">
            شبکه الکترونیکی هوشمند یکپارچه(شاهین)
        </a>
        </div>
        <div class="header-body">
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
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    @if(auth()->user()->hasRole('presentore'))
                        <span>{{ auth()->user()->name . ' ' .auth()->user()->family }} (سمت : {{ auth()->user()->roles->first()->display_name . auth()->user()->id}})</span>
                    @else
                        <span>{{ auth()->user()->name.' '.auth()->user()->family }} (سمت : {{ auth()->user()->roles->first()->display_name}})</span>
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
                <li class="nav-item d-lg-none d-sm-block">
                    <a href="#" class="nav-link side-menu-open">
                        <i class="ti-menu"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div>
            <a href="{{ auth()->user()->admin_representation_id ? '/representation/'.auth()->user()->admin_representation->slug : '/' }}" class="text-white mr-3">برو به سایت <i class="fa fa-angle-double-left" aria-hidden="true"></i></a>
        </div>
    </div>
</nav>
