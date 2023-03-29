
$(".statusCoupon").on('click', function() {
    var id = $(this).attr('id');
    var status = $(this).attr('data-title');
    $.ajax({
        type: "post",
        url: '/dashboard/coupon/statusCoupon',
        headers: {"Authorization": localStorage.getItem('token')},
        data: { status:status,id:id } ,
        success: function (result) {
            if (result['status'] === 'true') {
                if (status == 1) {
                    $('#' + id + '.statusCoupon').attr('data-title', 0).html('غیرفعال');
                }
                if (status == 0) {
                    $('#' + id + '.statusCoupon').attr('data-title', 1).html('فعال');
                }
            }
        }
    });
});

