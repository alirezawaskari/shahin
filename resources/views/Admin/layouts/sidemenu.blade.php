<div class="side-menu sidebar-custom collapsed" style="z-index: 1100" id="myNav">
    <div class="side-menu-body">
        <ul>
            <li><a class="active" href="#"><i class="icon ti-home"></i> <span>داشبورد</span> </a>
                <ul>
                    <li><a href="/dashboard"> <span>گزارشات مالی</span> </a></li>
                    <li><a href="/dashboard/dailyDesktop"> <span>کارتابل روزانه</span> </a></li>
                </ul>
            </li>


            @foreach($menu as $category)

                <li>
                    <a href="#">
                        <i class="icon {{ $category['icon'] }}"></i>
                        <span>{{ $category['name'] }}</span>
                    </a>

                    {{-- MenuService has already dropped anything that is not in the menu or has no
                         usable route name, and returns the items in their ترتیب order. --}}
                    <ul>
                        @foreach($category['items'] as $list)
                            <li>
                                <a href="{{ $list->menu_url }}">
                                    {{ $list->display_name ?: $list->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

            @endforeach
        </ul>
    </div>
</div>
