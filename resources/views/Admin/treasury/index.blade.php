@extends('Admin.master')
@section('title')خزانه@endsection
@section('css')
    <link rel="stylesheet" href="{{asset('assets/script/fullcalendar/main.min.css')}}">
    <style>

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
            font-size: 14px;
        }

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
                initialDate: '2023-08-19',
                locale: initialLocaleCode,
                buttonIcons: false, // show the prev/next text
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                events: [
                    @foreach($checks as $check)
                    {
                      title: '{{$check->id}}',
                      'start': '{{date('Y-m-d', strtotime($check->date))}}'
                    },
                    @endforeach
                    {
                        title: 'All Day Event',
                        start: '2023-08-19'
                    },
                    {
                        title: 'Long Event',
                        start: '2023-11-07',
                        end: '2023-11-10'
                    },
                    {
                        groupId: 999,
                        title: 'Repeating Event',
                        start: '2021-11-11T16:00:00'
                    },
                    {
                        groupId: 999,
                        title: 'Repeating Event',
                        start: '2021-11-16T16:00:00'
                    },
                    {
                        title: 'Conference',
                        start: '2021-11-11',
                        end: '2021-11-13'
                    },
                    {
                        title: 'Meeting',
                        start: '2021-11-12T10:30:00',
                        end: '2021-11-12T12:30:00'
                    },
                    {
                        title: 'Lunch',
                        start: '2021-11-12T12:00:00'
                    },
                    {
                        title: 'Meeting',
                        start: '2021-11-12T14:30:00'
                    },
                    {
                        title: 'Happy Hour',
                        start: '2021-11-12T17:30:00'
                    },
                    {
                        title: 'Dinner',
                        start: '2021-11-12T20:00:00'
                    },
                    {
                        title: 'Birthday Party',
                        start: '2021-11-13T07:00:00'
                    },
                    {
                        title: 'Click for Google',
                        url: 'http://google.com/',
                        start: '2021-11-28'
                    }
                ]
            });

            calendar.render();
        });
    </script>
@stop
