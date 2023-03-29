@extends('site.master')

@section('content')

    <div class="profile" style="margin-top: 50px">

        {{-- profile --}}
        <div class="profile-section py-5">
            <div class="container">

                <div class="row">
					@include('site.profile.sidebar')
                    @yield('content2')

                </div>

            </div>
        </div>

    </div>


@endsection
