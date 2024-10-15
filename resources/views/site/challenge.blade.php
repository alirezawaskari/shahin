@extends('site.master')
@section('title')
    لاله کارت
@endsection
@section('content')

    <div class="index">
		 <div class="row wrapper mt-3">
			 <div class="col-md-2">
			 </div>
			 <div class="col-md-8 col-12 text-center text-gray2">
				 <h1><img src="{{ asset('assets/site/images/IMG-20200401-WA0010.png') }}" width="100px"> پویش #از_خانه_بخریم </h1>
			 </div>
			 <div class="col-md-2">
			 </div>
        </div>
        {{--Services--}}
        <div class="service-section bg-gray py-2">
            <div class="container">
                <div class="row">
                    @foreach($services as $service)
                        <article class="col-4 overflow-hidden pr-1 pl-1">
                            <a href="/services/{{ $service->slug }}" class="ml-2">
                                <img src="{{ $service->icon }}" class="card-img" alt="{{ $service->title }}">
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        if (auth()->user()->level != 'organ' && screen.width <= 699) {
            document.location.href = "services";
        }
    </script>
@endsection
