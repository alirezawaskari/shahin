
$('body').on('click', '.bellMessage', function(e) {
    console.log('bellMessage');
    var id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/getProduct',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id},
        success: function (result) {
            Swal.fire({
                title: "آخرین توضیحات",
                icon: 'info',
                html:result,
                showCloseButton: true,
                focusConfirm: false,
                confirmButtonText:'باشه'
            })
        }
    });
});

$('body').on('click', '#filter', function(e) {
    e.preventDefault();
    var id = $('input[name="id"]').val() ;
    var name = $('input[name="name"]').val() ;
    var en_name = $('input[name="en_name"]').val() ;
    var status = $('select[name="status"]').val() ;
    var brand_id = $('select[name="brand_id"]').val() ;
    var root_category_id = $('select[name="root_category_id"]').val() ;
    var type_id = $('input[name="type_id"]').val() ;

    $.ajax({
        type: "get",
        url: '/dashboard/products/'.type_id,
        data: {id: id,name: name,
            en_name : en_name,  status : status, brand_id : brand_id,
            root_category_id :root_category_id, type_id : type_id
        },
        success: function (result){
            $('.dataPagination').html(result);
        }
    });

    // $.ajax({url : '/dashboard/products/'.type_id}).done(function (data) {$('.dataPagination').html(data);});


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

