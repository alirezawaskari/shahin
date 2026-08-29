


var center_id_calendar = $('.center_id').val();
var time_intervals_calendar = $('.time_intervals').children("option:selected").val();
var user_id = $('.user_id').val();
$(document).on("change", ".time_intervals", function (e) {
    e.preventDefault();
    time_intervals_calendar = $(this).children("option:selected").val();
});

var date_start;
var date_end; var calendar ;
document.addEventListener('DOMContentLoaded', function () {
    var initialLocaleCode = 'fa';
    var localeSelectorEl = document.getElementById('locale-selector');
    var calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        // initialDate: '2021-05-24',
        locale: initialLocaleCode,
        buttonIcons: false, // show the prev/next text
        weekNumbers: true,
        navLinks: true, // can click day/week names to navigate views
        editable: true,
        dayMaxEvents: true, // allow "more" link when too many events
        selectable: true,
        select: function (arg) {
            $(".modal").modal("show");
            $("input").prop("readonly", false);
            $("#save-event").on("click", function (event) {
                date_start = arg.startStr;
                date_end = arg.endStr;
                if (($('.start_time').val() !== '' && $('.end_time').val() !== '') && center_id_calendar) {
                    if (($('.start_time').val() < $('.end_time').val() ) && center_id_calendar) {

                        $.ajax({
                            type: "post",
                            url: '/dashboard/fullCalendar',
                            headers: {"Authorization": localStorage.getItem('token')},
                            data: {
                                start_time: $('.start_time').val(),
                                end_time: $('.end_time').val(),
                                start_date: date_start,
                                end_date: date_end,
                                time_intervals: time_intervals_calendar,
                                center_id: center_id_calendar,
                                user_id: user_id
                            },
                            success: function (result) {
                                $.each(result, function (index, value) {
                                    if (value.fullName != '') {
                                        calendar.addEvent({
                                            id: value.id,
                                            title: value.fullName + " " + $('.start_time').val() + " - " + $('.end_time').val(),
                                            start: value.date_en,
                                            end: value.date_en,
                                            url: '/dashboard/fullCalendar/' + value.id + '/edit'
                                        });
                                    } else {
                                        calendar.addEvent({
                                            id: value.id,
                                            title: value.center_name + ' ' + $('.start_time').val() + ' - ' + $('.end_time').val(),
                                            start: value.date_en,
                                            end: value.date_en,
                                            url: '/dashboard/fullCalendar/' + value.id + '/edit'
                                        });
                                    }

                                })
                            }
                        });

                        $(".modal").modal("hide");
                    }
                    else {
                        toastr.warning('ساعت شروع و پایان باید باهم تفاوت داشته باشد.');
                    }
                }
                else {
                    toastr.warning('برای ثبت روز های کاری بایستی ساعت ها را انتخاب کنید.');
                }
            });
        },
        eventClick: function (arg) {
            // if (confirm('آیا برای حذف این رویداد مطمئن هستید ؟')) {
            //     console.log(arg);
            //     console.log(arg.event);
            // }
        },
        events: [
            $.ajax({
                type: "get",
                url: '/dashboard/allCalenderEvent',
                headers: {"Authorization": localStorage.getItem('token')},
                data: {user_id: user_id, center_id_calendar: center_id_calendar},
                success: function (result) {
                    $.each(result, function (index, value) {
                        if (value.fullName != '') {
                            calendar.addEvent({
                                id: value.id,
                                title: value.fullName + " " + value.start_time + " - " + value.end_time,
                                start: value.date_en,
                                end: value.date_en,
                                url: '/dashboard/fullCalendar/' + value.id + '/edit'
                            });
                        } else {
                            calendar.addEvent({
                                id: value.id,
                                title: value.center_name + ' ' + value.start_time + ' - ' + value.end_time,
                                start: value.date_en,
                                end: value.date_en,
                                url: '/dashboard/fullCalendar/' + value.id + '/edit'
                            });
                        }

                    })
                }
            })
        ]
    });

    calendar.render();

    // build the locale selector's options
    calendar.getAvailableLocaleCodes().forEach(function (localeCode) {
        var optionEl = document.createElement('option');
        optionEl.value = localeCode;
        optionEl.selected = localeCode == initialLocaleCode;
        optionEl.innerText = localeCode;
        // localeSelectorEl.appendChild(optionEl);
    });
});



