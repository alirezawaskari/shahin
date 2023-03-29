$(document).ready(function() {
    $.ajax({
        url: '/dashboard/user-performance-detail-filter/{{ $user->id }}',
        type: 'GET',
        success: function(data) {
            var table = $('#datatable-json').DataTable({
                data: data.data,
                columns: [{
                        "data": "id"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "phone"
                    },
                    {
                        "data": "price"
                    },
                    {
                        "data": "type"
                    },
                    {
                        "data": "session_number"
                    },
                    {
                        "data": "done_session_number"
                    },
                    {
                        "targets": -1,
                        "data": null,
                        "defaultContent": "<button class= 'btn btn-success'>مشاهده</button>"
                    }
                ],
                responsive: true,
            });
            $('#datatable-json').on('click', 'button', function(e) {
                e.preventDefault;
                var rows = table.row($(this).parents('tr'))
                    .data(); //Get Data Of The Selected Row
            });
        }
    });
});
