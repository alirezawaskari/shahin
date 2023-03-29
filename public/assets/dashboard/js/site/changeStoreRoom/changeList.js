$('body').on('click', '#filter', function(e) {
    e.preventDefault();
    var name = $('input[name="name"]').val() ;
    var family = $('input[name="family"]').val() ;
    var start_date = $('input[name="start_date"]').val() ;
    var end_date = $('input[name="end_date"]').val() ;
    var product_name = $('input[name="product_name"]').val() ;
    var type = $('select[name="type"]').val() ;
    var store_room_id = $('select[name="store_room_id"]').val() ;
    var product_type = $('select[name="product_type"]').val() ;

    $.ajax({
        type: "get",
        url: '/dashboard/changeStoreRoom',
        data: {name: name,family:family,start_date: start_date,
            end_date : end_date,product_name : product_name, type : type,store_room_id:store_room_id,product_type:product_type
        },
        success: function (result){
            $('.dataPagination').html(result);
        }
    });
});

$(function() {
    $('body').on('click', '.pagination a', function(e) {
        e.preventDefault();
        $('.load').append('<img style="position: absolute; left: 50%; top: 0; z-index: 100000;" src="/upload/demo_wait.gif" />');
        var url = $(this).attr('href');
        getDataPagination(url);
        window.history.pushState("", "", url);
    });
    function getDataPagination(url) {
        $.ajax({url : url}).done(function (data)
        {
            $('.dataPagination').html(data);
        });
    }
});