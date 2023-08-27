@extends('Admin.master')
@section('title')خزانه@endsection
@section('css')
    <link rel="stylesheet" href="{{asset('assets/script/fullcalendar/main.min.css')}}">
    <style>


        #top {
            background: #eee;
            border-bottom: 1px solid #ddd;
            padding: 0 10px;
            line-height: 40px;
            font-size: 12px;
        }

        #calendar {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 10px;
        }

    </style>

@endsection
@section('content')

    <div class="page-header">
        <div>
            <h3>خزانه</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">خزانه</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>


@stop

@section('js')
    <script src="{{asset('assets/script/fullcalendar/main.min.js')}}"></script>
    <script src="{{asset('assets/script/fullcalendar/locales-all.min.js')}}"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {
            var initialLocaleCode = 'fa';
            var localeSelectorEl = document.getElementById('locale-selector');
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                initialDate: new Date(),
                locale: initialLocaleCode,
                buttonIcons: false, // show the prev/next text
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                events: [
                    @foreach($checks as $check)
                    {
                      title: '{{'چک :'.$check->applicantable->name.' '.$check->applicantable->family.'['.number_format($check->amount).' ریال '.']'}}',
                      'start': '{{date('Y-m-d', strtotime($check->date))}}'
                    },
                    @endforeach
                ]
            });

            calendar.render();
        });
    </script>
@stop
