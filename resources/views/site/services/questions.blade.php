@if($questions->count()> 0)
    <div class="container mb-4" dir="rtl">
        <h5 class="mt-6">سوالات متداول :</h5>
        <div class="accordion mt-4" id="accordionExample">

            <!-- Question 1 -->
            @foreach($questions as $item)
                <div class="card text-right bg-white">
                    <div class="card-header text-right" id="headingOne">
                        <h2 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{$item->id}}" aria-expanded="true" aria-controls="collapseOne" style="color: #333;">
                                {{$item->question}}
                            </button>
                        </h2>
                    </div>

                    <div id="collapse{{$item->id}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                        <div class="card-body">
                            {{$item->answer}}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endif
