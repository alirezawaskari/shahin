
//categoryPosition used category
$(".row_position").sortable({
    delay: 150,
    stop: function () {
        var data_type = $(this).attr('data-type');
        var selectedData = new Array();
        Swal.fire({
            title: "آیا مطمئن هستید؟",
            text: "",
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(function (yes) {
                // Called if you click Yes.
                if (yes) {
                    $('.row_position>li').each(function () {
                        if ($(this).attr("data-type") === data_type)
                            selectedData.push($(this).attr("id"));
                    });

                    $.ajax({
                        url: "/dashboard/site/changePosition",
                        type: 'post',
                        data: {position: selectedData},
                        headers: {"Authorization": localStorage.getItem('token')},
                        success: function ($res) {
                            // Make Ajax call.
                            if($res == 'ture'){

                            } Swal.fire({
                                title: "تغییرات انجام شد", text: "", icon: "success", buttons: {confirm: 'باشه'}
                            })
                        }
                    })

                }
            },
            function (no) {
                // Called if you click No.
                if (no == 'cancel') {
                    swal('Cancelled', '', 'error');
                }
            });
    }
});


//categoryPosition used category
function updateOrder(data) {
    $.ajax({
        url: "/dashboard/site/changePosition",
        type: 'post',
        data: {position: data},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function () {
            alert('تغییرات ذخیره شد..   :)');
        }
    })
}

//categoryPosition used category - toShow
$(document).on('click', '.toShow', function () {
    var id = $(this).attr('id');
    // console.log(id);
    $('#box' + id).toggle("slow", function () {
        // Animation complete.
    });
});
