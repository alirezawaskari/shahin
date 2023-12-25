@extends('site.master')

@section('title')
مدارک
@endsection

@section('content')

<div class="col-12 mb-2">
    <div class="card-body">
        <div class="show">
            <a href="{{ asset('upload\2023\4\document\logestic.pdf') }}" target="_blank">
                <embed src="{{ asset('upload\2023\4\document\logestic.pdf') }}" type="application/pdf" class="card-img-top" height="250px">
            </a>
        </div>
        <a href="{{ asset('upload\2023\4\document\logestic.pdf') }}" class="btn btn-success py-1 px-2 mr-2 mt-3" download> <i class="fa fa-download" aria-hidden="true"></i> دانلود  </a>
        <a href="{{ asset('upload\2023\4\document\logestic.pdf') }}" class="btn btn-primary py-1 px-2 mr-2 mt-3" target="_blank"> <i class="fa fa-eye" aria-hidden="true"></i> مشاهده  </a>
    </div>
</div>
@endsection
