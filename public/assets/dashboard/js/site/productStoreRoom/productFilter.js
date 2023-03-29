
$('body').on('click', '#filter', function(e) {
    e.preventDefault();
    var name = $('input[name="name"]').val() ;
    var start_date = $('input[name="start_date"]').val() ;
    var end_date = $('input[name="end_date"]').val() ;
    var storage = $('select[name="storage"]').val() ;
    var main_unit = $('select[name="main_unit"]').val() ;
    var category_store_room_id = $('select[name="category_store_room_id"]').val() ;

    if($('input[name="expired_products"]').is(':checked')) var expired_products = 1;
    else var expired_products = 0;

    if($('input[name="expiring_products"]').is(':checked')) var expiring_products = 1;
    else var expiring_products = 0;

    if($('input[name="finishing_products"]').is(':checked')) var finishing_products = 1;
    else var finishing_products = 0;

    $.ajax({
        type: "get",
        url: '/dashboard/products/'.type_id,
        data: {name: name, start_date : start_date, end_date : end_date,
            storage :storage, main_unit : main_unit , category_store_room_id:category_store_room_id,
            expired_products:expired_products,expiring_products:expiring_products,finishing_products:finishing_products
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

