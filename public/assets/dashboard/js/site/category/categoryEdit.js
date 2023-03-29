
// remove slider Img in edit slider
$(document).on('click', '.remove_slider', function () {
    var slider_id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgSlider',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {slider_id: slider_id},
        success: function (result) {
            if (result['status'] == 'true') {
                $('#slider_' + slider_id).remove();
            }
        }
    });
});

$(document).on('click', '.removeCatMenu', function () {
    var id = $(this).attr('id');
    $.ajax({
        url: "/dashboard/removeCatMenu",
        type: 'post',
        data: {id: id},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if (result['status'] == 'true') {
                $('#' + id).remove();
            }
        }
    })
});

