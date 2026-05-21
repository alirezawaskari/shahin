<div class="side-menu sidebar-custom collapsed" style="z-index: 1100" id="myNav">
    <div class="side-menu-body">
        <ul>
            <li><a class="active" href="#"><i class="icon ti-home"></i> <span>داشبورد</span> </a>
                <ul>
                    <li><a href="/dashboard"> <span>گزارشات مالی</span> </a></li>
                    <li><a href="/dashboard/dailyDesktop"> <span>کارتابل روزانه</span> </a></li>
                </ul>
            </li>
            @foreach($menu as $categoryName => $items)

                <li>
                    <a href="#">
                        <i class="icon ti-control-record"></i>
                        <span>{{ $categoryName }}</span>
                    </a>

                    <ul>
                        @foreach($items as $list)
                            @if($list->isMenu)
                                <li>
                                    <a href="{{ route($list->link) }}">
                                        {{ $list->display_name }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
</div>
