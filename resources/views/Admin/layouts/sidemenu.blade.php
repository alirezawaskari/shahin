<div class="side-menu sidebar-custom collapsed" style="z-index: 1100" id="myNav">
    <div class="side-menu-body">
        <ul>
            <li><a class="active" href="#"><i class="icon ti-home"></i> <span>داشبورد</span> </a>
                <ul>
                    <li><a href="/dashboard"> <span>گزارشات مالی</span> </a></li>
                    <li><a href="/dashboard/dailyDesktop"> <span>کارتابل روزانه</span> </a></li>
                </ul>
            </li>

            @php
                $permissions = \App\Models\Permission::with('category')->get();

                $groupedPermissions = $permissions->groupBy(function ($item) {
                    return $item->category ? $item->category->name : 'بدون دسته‌بندی';
                });
            @endphp

            @foreach($groupedPermissions as $categoryName => $items)
                <li><a href="#"><i class="icon ti-control-record"></i> <span>{{ $categoryName }}</span></a>
                    <ul>
            @foreach($items as $list)
                    @if($list->isMenu)
                        <li><a href="{{ route($list->link)  }}">
                                <span>{{ $list->display_name }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
                    </ul>
            @endforeach
        </ul>
    </div>
</div>
