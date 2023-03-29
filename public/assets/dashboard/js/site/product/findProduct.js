
//start transfer Product
// AJAX call for auto complete - transfer Product
$(document).ready(function(){
    var minlength = 3;
    $("#search-box").keyup(function(){
        var value = $(this).val();
        if (value.length >= minlength ) {
            if(value !== ''){ value = value.split('|')[0]; }
            $.ajax({
                type: "post",
                url: '/dashboard/searchTransferProduct',
                headers: {"Authorization": localStorage.getItem('token')},
                data: {keyword: value},
                beforeSend: function(){
                    $("#search-box").css("background","#FFF no-repeat 165px");
                },
                success: function (result) {
                    var data =  $("<ul>");

                    $(result['child']).each(function (index, val) {
                        data.append($("<li>")
                            .attr('class','selectTitleProduct categoryCenter')
                            .attr('data-title', value  + "  |  " + "  در گروه " + val.title )
                            .attr('value',val.id).text(value + "  |  "   + "  در گروه " + val.title ));
                    });

                    $("#suggesstion-box").show().html(data).css({'height':'200px'});
                    $("#search-box").css("background","#FFF");
                }
            });
        }
    });
});

//click on one of category of center in panel's center - return list of products
$(document).on('click', '.categoryCenter', function () {
    $('.resultSelectTransferProduct').html('');
    var text = $(this).attr('data-title');
    var id = $(this).val();
    $("#search-box").val(text);
    $("#suggesstion-box").hide();
    if(text !== ''){
        text = text.split('|')[0];
    }
    $.ajax({
        type: "post",
        url: '/dashboard/selectTransferProduct',
        data: {id:id,text: text},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if(result['view_result']){
                $('.resultSelectTransferProduct').html(result['view_result']);
            }
        }
    });

});

//New seller for the goods in the form of transfer
$(document).on('click', '.centerSellToo', function () {
    var product_id = $(this).attr('id');

    $.ajax({
        type: "post",
        url: '/dashboard/centerSellToo',
        data: {product_id: product_id},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if(result['status'] === true){
                $("#"+product_id+".content1").css({'display':'none'});
                $("#"+product_id+".content2").css({'display':'block'});
            }
        }
    });

});
//end transfer Product
