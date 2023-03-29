//start category
$(".parent_id").change(function () {
    var id = $(this).children("option:selected").val();
    $.ajax({
        type: "post",
        url: '/dashboard/findChild',
        data: {id: id },
        success: function (result) {
            if (id != '') {
                if (result['categories'] != '') {
                    $('.thirdCategory').remove();
                    $('.childCatSelect').remove();
                    $('.fourthCategory').remove();
                    var a_sel = $('<select class="form-control childCatSelect" name="child_id" >');
                    a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                    $(result['categories']).each(function (index, val) {
                        a_sel.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.fieldsBox').append( a_sel);
                }
            }
        }
    });
});

$(document).on('click', '.childCatSelect', function () {
    var id = $(this).children("option:selected").val();
    $.ajax({
        type: "post",
        url: '/dashboard/findChild',
        data: {id: id },
        success: function (result) {
            if (id != '') {
                if (result['categories'] != '') {
                    $('.thirdCategory').remove();
                    var a_sel = $('<select class="form-control thirdCategory" name="third_id" >');
                    a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                    $(result['categories']).each(function (index, val) {
                        a_sel.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.thirdBox').append( a_sel);
                }
            }
        }
    });
});

$(document).on('click', '.thirdCategory', function () {
    var id = $(this).children("option:selected").val();
    $.ajax({
        type: "post",
        url: '/dashboard/findChild',
        data: {id: id },
        success: function (result) {
            if (id != '') {
                if (result['categories'] != '') {
                    $('.fourthCategory').remove();
                    var a_sel = $('<select class="form-control fourthCategory" name="fourth_id" >');
                    a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                    $(result['categories']).each(function (index, val) {
                        a_sel.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.fourthBox').append( a_sel);
                }
            }
        }
    });
});
$(document).on("click", ".agent-add", function () {
    var name = document.getElementById('name').value
    var type = document.getElementById('type').value
    $.ajax
    ({
        "_token": "{{ csrf_token() }}",
        url: "/../../dashboard/product/store",
        type: "POST",
        data: {
            name: name,
            type: type,
        },
        success: function(result)
        {
            location.reload();
        },
        error: function(data)
        {
            console.log(data);
        }
    });
});


$(".changeCategory").click(function () {
    $('.changeCategoryBox').slideDown( "slow" );
});

//end category

// removeImg in edit product
$(document).on('click', '.remove_btn', function () {
    var gallery_id=$(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgGallery',
        data: {gallery_id: gallery_id},
        success: function (result) {
            if(result['status']){
                $('#gallery_'+gallery_id).remove();
            }
        }
    });
});

$(document).on('click', '.remove_work_sample', function () {
    var work_sample_id=$(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgWorkSample',
        data: {work_sample_id: work_sample_id},
        success: function (result) {
            if(result['status']){
                $('#work_sample_'+work_sample_id).remove();
            }
        }
    });
});



$('.type_good_selected').click(function(){
    if($(this).attr("value")== 0){
        $(".goodStockBox").css({'display':'block'});
        $(".goodForm").css({'display':'block'});
        $(".goodCenterBox").css({'display':'none'});
    }
    if($(this).attr("value")== 1){
        $(".goodBox").css({'display':'block'});
        $(".goodCenterBox").css({'display':'block'});
        $(".goodForm").css({'display':'block'});
        $(".goodStockBox").css({'display':'none'});
    }
});

$('.type_selected').click(function(){
    if($(this).attr("value") == 0){
        $(".rawMaterialBox").css({'display':'block'});
        $(".goodForm").css({'display':'none'});
        $(".serviceBox").css({'display':'none'});
    }
    if($(this).attr("value") == 1){
        $(".goodBox").css({'display':'block'});
        $(".goodForm").css({'display':'block'});
        $(".rawMaterialBox").css({'display':'none'});
        $(".serviceBox").css({'display':'none'});
    }
    if($(this).attr("value") == 2){
        $(".serviceBox").css({'display':'block'});
        $(".goodBox").css({'display':'none'});
        $(".rawMaterialBox").css({'display':'none'});
        $(".goodForm").css({'display':'none'});
    }
});


//in products and AgencyTypes
var i = 0;
$(".addRow").click(function(){
    ++i;
    $("#dynamicTable").append('<tr><td><input type="text" name="addMoreImage['+i+'][file_title]" placeholder="لطفا عنوان عکس را وارد کنید" class="form-control" /></td><td><input type="file" name="addMoreImage['+i+'][file]"  class="form-control" /></td><td><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-remove"></i></button></td></tr>');
});
$(document).on('click', '.remove-tr', function(){
    $(this).parents('tr').remove();
});

// addGoodImage
var j = 0;
$(".addGoodImage").click(function(){
    ++j;
    $("#dynamicTable").append('<tr><td><input type="text" name="goodImage['+j+'][file_title]" placeholder="لطفا عنوان عکس را وارد کنید" class="form-control" /></td><td><input type="file" name="goodImage['+j+'][file]"  class="form-control" /></td><td><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-remove"></i></button></td></tr>');
});
$(document).on('click', '.remove-tr', function(){
    $(this).parents('tr').remove();
});


$("#add_work_sample").click(function(){
    ++i;
    $("#dynamicTable").append('<tr><td><input type="text" name="workSample['+i+'][file_title]" placeholder="لطفا عنوان عکس را وارد کنید" class="form-control" /></td><td><input type="file" name="workSample['+i+'][file]"  class="form-control" /></td><td><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-remove"></i></button></td></tr>');
});
$(document).on('click', '.remove-tr', function(){
    $(this).parents('tr').remove();
});



$(document).ready(function () {
    $(".selectMulti").select2();
});





