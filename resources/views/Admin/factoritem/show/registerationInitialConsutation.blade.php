<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link show active" id="saveTrunRatings-tab" data-toggle="tab" href="#saveTrunRatings" role="tab"
            aria-controls="saveTrunRatings" aria-selected="false">تعیین نوبت مشاوره</a>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade p-t-20 active show" id="saveTrunRatings" role="tabpanel" aria-labelledby="saveTrunRatings-tab">
        @if (in_array(trim($factorItem['status_factor']) , [10,16,17]) )
        @include('Admin.factoritem.show.turnRatings')
        @endif
    </div>
</div>
