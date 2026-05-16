<div class="side-menu sidebar-custom collapsed" style="z-index: 1100" id="myNav">
    <div class="side-menu-body">
        <ul>
            <li><a href="#"><i class="icon ti-control-record"></i> <span>لیست ماژولها {{auth()->user()->isAdmin}}</span> </a>
                <ul>

                    @foreach (auth()->user()->isAdmin as $category)
                        <li><a href="{{ route('ghorfeCategoryDetail', $category->id) }}"> <span>{{$category->name}}</span> </a></li>
                    @endforeach
                </ul>
        </ul>
    </div>
</div>
