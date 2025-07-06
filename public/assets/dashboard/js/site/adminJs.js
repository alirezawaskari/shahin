$body = $("body");

$(document).on({
    ajaxStart: function () {
        $body.addClass("loading");
    },
    ajaxStop: function () {
        $body.removeClass("loading");
    }
});

// removeImg in edit product
$(document).on('click', '.remove_btn', function () {
    var gallery_id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgGallery',
        data: {
            gallery_id: gallery_id
        },
        headers: {
            "Authorization": localStorage.getItem('token')
        },
        success: function (result) {
            if (result['status'] == 'true') {
                $('#gallery_' + gallery_id).remove();
                $('.gallery_' + gallery_id).remove();
            }
        }
    });
});

//select2
$(document).ready(function () {
    $('.selectMulti').select2({
        dir: "rtl"
    });

    $('.js-example-basic-single').select2();
});

//upload image good - use in createGood.blade
var index_add_img = 0;

function readURL(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpg", "image/jpeg"];
        if ($.inArray(fileType, validImageTypes) > 0) {
            if (6000000 >= input.files[0].size) {
                var fd = new FormData();
                fd.append('file', input.files[0]);
                fd.append('index_add_img', index_add_img);
                var reader = new FileReader();
                reader.onload = function (e) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/uploadPhotosTemporarily',
                        data: fd,
                        contentType: false,
                        processData: false,
                        headers: {
                            "Authorization": localStorage.getItem('token')
                        },
                        success: function (result) {
                            if (result['status'] === true) {
                                if (index_add_img === 0) {
                                    input_first_photo = '<input id="' + index_add_img + '" class="radio_good"  name="image_name_good[' + index_add_img + '][index]" type="radio" checked value="1"><i class="fa fa-lg fa-flag-o"></i>';
                                } else {
                                    input_first_photo = '<input id="' + index_add_img + '" class="radio_good"  name="image_name_good[' + index_add_img + '][index]" type="radio" value="0"><i class="fa fa-lg fa-flag-o"></i>';
                                }

                                var tr_images = $('<tr id="' + index_add_img + '" class="row_image">');
                                var image = '<td><img src="' + e.target.result + '" id="' + index_add_img + '" class="disInline image_create border-radius5" width="80px"><input type="hidden" id="' + index_add_img + '" class="image_name_good" name="image_name_good[' + index_add_img + '][name]" value="' + result['image_name'] + '"></td>';
                                var remove = '<td><a id="' + index_add_img + '" class="flagProduct">' + input_first_photo + '</a><a id="' + index_add_img + '" class="btn btn-sm btn-danger removeImageCreated"><i class="fa fa-trash"></i></a></td>';

                                tr_images.append(image).append(remove);
                                $('.imagesList').append(tr_images);
                                ++index_add_img;
                                $('.submitGood').css({
                                    'display': 'block'
                                });
                            }
                        }
                    });

                };

                reader.readAsDataURL(input.files[0]);
            } else {
                toastr.error('آپلود ناموفق : حجم عکس از 6 مگا بایت بیشتر می باشد.');
            }
        } else {
            toastr.error('آپلود ناموفق : فرمت تصویر بایستی JPG باشد.');
        }

    }
}

//sliderCategory
function multipleImages(input) {
    var multipleImagesHasLink = "0";
    if ($('.multipleImagesHasLink').val()) multipleImagesHasLink = $('.multipleImagesHasLink').val();
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpg", "image/jpeg"];
        if ($.inArray(fileType, validImageTypes) > 0) {
            if (6000000 >= input.files[0].size) {
                var fd = new FormData();
                fd.append('file', input.files[0]);
                var reader = new FileReader();
                reader.onload = function (e) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/uploadPhotosTemporarily',
                        data: fd,
                        contentType: false,
                        processData: false,
                        headers: {
                            "Authorization": localStorage.getItem('token')
                        },
                        success: function (result) {
                            if (result['status'] === true) {

                                var tr_images = $('<tr id="' + index_add_img + '" class="row_image">');
                                var image = '<td><img src="' + e.target.result + '" id="' + index_add_img + '" class="disInline image_create border-radius5" width="80px"><input type="hidden" id="' + index_add_img + '" class="image_name_good" name="multiple_image[' + index_add_img + '][file]" value="' + result['image_name'] + '"></td>';
                                var remove = '<td><a id="' + index_add_img + '" class="btn btn-sm btn-danger removeImageCreated"><i class="fa fa-trash"></i></a></td>';
                                var title = '<td><input type="text" class="form-control" placeholder="عنوان" name="multiple_image[' + index_add_img + '][title]"></td>';
                                if (multipleImagesHasLink === "1") {
                                    var link = '<td><input type="text" class="form-control" placeholder="لینک" name="multiple_image[' + index_add_img + '][link]" required></td>';
                                    tr_images.append(image).append(title).append(link).append(remove);
                                } else tr_images.append(image).append(title).append(remove);

                                $('.imagesList').append(tr_images);
                                ++index_add_img;
                            }
                        }
                    });

                };

                reader.readAsDataURL(input.files[0]);
            } else {
                toastr.error('آپلود ناموفق : حجم عکس از 6 مگا بایت بیشتر می باشد.');
            }
        } else {
            toastr.error('آپلود ناموفق : فرمت تصویر بایستی JPG باشد.');
        }
    }
}

ct = 1;
//upload single image
var index_add_photo = 0;
$(document).on('change', '.file_upload_ajax', function () {
    var file = $($(this)).prop('files')[0];
    if (file) {
        var file_attr_name = $(this).attr('name');
        var file_attr_id = $(this).attr('id');
        var fileType = file["type"];
        var validImageTypes = [
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/svg+xml",
            "image/psd",
            "image/tiff",
            "image/gif",
            "application/pdf",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-excel",
            "application/x-rar-compressed",
            "application/x-zip-compressed",
        ];
        var size = file.size;
        if ($.inArray(fileType, validImageTypes) > 0) {
            if (6000000 >= size) {
                var fd = new FormData();
                fd.append('file', file);
                var reader = new FileReader();
                loading = `<div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading` + file_attr_id + `">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
                </div>`;
                $('#' + file_attr_id + '_src.show').append(loading);
                $('#loading' + file_attr_id).css('display', 'block');
                reader.onload = function (e) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/uploadPhotosTemporarily',
                        data: fd,
                        contentType: false,
                        processData: false,
                        headers: {
                            "Authorization": localStorage.getItem('token')
                        },
                        success: function (result) {

                            if (result['status'] === true) {
                                $('#' + file_attr_id + '_src.show div').remove();
                                $('#' + file_attr_id + '_src.show img').remove();
                                var file = '';
                                if (!((fileType == 'application/x-rar-compressed') || (fileType == 'application/x-zip-compressed') || (fileType == 'application/pdf') || (fileType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') || (fileType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))) {
                                    file = '<img src="' + e.target.result + '" id="' + index_add_photo + '" ' +
                                        'class="card-img-top" height="150px">';
                                } else {
                                    file = '<a href="' + result['image_name'] + '" id="' + index_add_photo + '" ' +
                                        'class="disInline image_create btn border border-success mr-3" >مشاهده</a>';
                                }
                                if (file_attr_name.includes("[]") && !file_attr_name.includes("consent_image")) {
                                    new_link(file_attr_id);
                                    $('#' + file_attr_id + '_src' + ct + '.show').append(file);
                                    $('#' + file_attr_id + ct + '.path').val(result['image_name']);
                                } else {
                                    $('#' + file_attr_id + '_src.show').append(file);
                                    $('#' + file_attr_id + '.path').val(result['image_name']);
                                }
                            } else if (result['status'] === false) {
                                let errorMessage = result['message'];
                                $('.alert-danger').remove();
                                let errorAlert = `
                                    <div class="alert alert-danger mb-4" role="alert">
                                        ${errorMessage}
                                    </div>
                                `;
                                $('#error-container').append(errorAlert);
                                $('html, body').animate({
                                    scrollTop: $('#error-container').offset().top - 100 // کمی بالاتر از خطا
                                }, 500);
                            }
                            $('#loading' + file_attr_id).css('display', 'none');
                        }
                    });
                };
                reader.readAsDataURL(file);
            } else {
                toastr.error('آپلود ناموفق : حجم عکس از 6 مگا بایت بیشتر می باشد.');
            }
        } else {
            toastr.error('آپلود ناموفق : فرمت فایل بایستی pdf|svg+xml|JPG|png|xlsx باشد.');
        }
    }
});

//flagProduct
$(document).on('click', '.flagProduct', function () {
    var index = $(this).attr('id');
    $("a.flagProduct > .radio_good").prop('checked', false);
    $("#" + index + ".radio_good").prop('checked', true);
});

//reviews
var counter_strength = $('.counter_review_strength').val();

var counter_weakness = 1;

$('.addInputStrength').click(function () {
    var new_div = document.createElement('div');
    new_div.id = 'dynamicInputStrength' + counter_strength;
    new_div.className = 'input-group mrg10';
    new_div.innerHTML = "<input type='text' class='form-control' name='reviewstrength[" + counter_strength + "][text]' placeholder='نقاط قوت'> " +
        "<span class='input-group-btn'>  <button id=" + counter_strength + " type='button' class='btn btn-danger removeInputStrength'>-</button></span> </div> ";
    document.getElementById('reviewstrength').appendChild(new_div);
    counter_strength++;

});

$(document).on('click', '.removeInputStrength', function () {
    var id = $(this).attr('id');
    var elem = document.getElementById('dynamicInputStrength' + id);
    return elem.parentNode.removeChild(elem);
});

$('.addInputWeakness').click(function () {
    var new_div = document.createElement('div');
    new_div.id = 'dynamicAddInputWeakness' + counter_weakness;
    new_div.className = 'input-group mrg10';
    new_div.innerHTML = "<input type='text' class='form-control' name='reviewWeakness[" + counter_weakness + "][text]' placeholder='نقاط ضعف'> " +
        "<span class='input-group-btn'>  <button id=" + counter_weakness + " type='button' class='btn btn-danger removeInputWeakness'>-</button></span> </div> ";
    document.getElementById('reviewWeakness').appendChild(new_div);
    counter_weakness++;

});

$(document).on('click', '.removeInputWeakness', function () {
    var id = $(this).attr('id');
    var elem = document.getElementById('dynamicAddInputWeakness' + id);
    return elem.parentNode.removeChild(elem);
});

//remove_review
$(document).on('click', '.remove_review', function () {
    var id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeReview',
        headers: {
            "Authorization": localStorage.getItem('token')
        },
        data: {
            id: id
        },
        success: function (result) {
            if (result['status'] == true) {
                $('#dynamicReview' + id).remove();
            }
        }
    });
});

// Jquery Dependency

$("input[data-type='currency']").on({
    keyup: function () {
        formatCurrency($(this));
    },
    blur: function () {
        formatCurrency($(this), "blur");
    }
});

function formatNumber(n) {
    // format number 1000000 to 1,234,567
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}

function formatCurrency(input, blur) {
    // puts cursor back in right position.

    // get input value
    var input_val = input.val();

    // don't validate empty input
    if (input_val === "") {
        return;
    }

    // original length
    var original_len = input_val.length;

    // initial caret position
    var caret_pos = input.prop("selectionStart");

    // check for decimal
    if (input_val.indexOf(".") >= 0) {
        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf(".");

        // split number by decimal point
        // var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);

        // add commas to left side of number
        // left_side = formatNumber(left_side);

        // validate right side
        right_side = formatNumber(right_side);

        // On blur make sure 2 numbers after decimal
        if (blur === "blur") {
            // right_side += "00";
        }

        // Limit decimal to only 2 digits
        // right_side = right_side.substring(0, 2);

        // join number by .
        // input_val = left_side + "." + right_side;

    } else {
        // no decimal entered
        // add commas to number
        // remove all non-digits
        input_val = formatNumber(input_val);
        // input_val = input_val;

        // final formatting
        if (blur === "blur") {
            // input_val += ".00";
        }
    }

    // send updated string to input
    input.val(input_val);

    // put caret back in the right position
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}

$('.showConfirmDelete').click(function (event) {
    var form = $(this).closest("form");
    event.preventDefault();

    Swal.fire({
        title: "آیا مطمئن هستید؟",
        showDenyButton: true,
        confirmButtonText: 'بله',
        denyButtonText: 'خیر'
    })
        .then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });


});

//type brand in create new brand
$(document).on('click', '.type_zero', function () {
    $('.type_zero_box').css({
        'display': 'flex'
    });
    $('.type_one_box').css({
        'display': 'none'
    });
});

//type brand in create new brand
$(document).on('click', '.type_one', function () {
    $('.type_one_box').css({
        'display': 'flex'
    });
    $('.type_zero_box').css({
        'display': 'none'
    });
});

$(document).on('click', '.brandStore', function () {
    var type = null;
    if ($(".type:checked").val()) type = $(".type:checked").val();
    var request_by = $('.request_by').val();
    var category_id = $('.category_id').val();
    var title = $('.title').val();
    var title_en = $('.title_en').val();
    var description = $('.description').val();
    var link_zero = $('#link_zero').val();
    var link_one = $('#link_one').val();
    var registration_form = $('.registration_form_path').val();
    var logo = $('.logo_path').val();

    if (type !== null && request_by !== null && category_id !== null && title !== null && title_en !== null &&
        description !== null && link_zero !== null && link_one !== null && registration_form !== null && logo !== null) {
        $.ajax({
            type: "post",
            url: '/dashboard/site/brand',
            headers: {
                "Authorization": localStorage.getItem('token')
            },
            data: {
                request_by: request_by,
                category_id: category_id,
                title: title,
                title_en: title_en,
                description: description,
                type: type,
                registration_form: registration_form,
                link_zero: link_zero,
                link_one: link_one,
                logo: logo
            },
            success: function (result) {
                if (result['status'] === "true") {
                    toastr.success(result['message']);
                    $('#brandRequestModal').modal('toggle');
                    $('.type').prop('checked', false);
                    $('.request_by').val('');
                    $('#registration_form').html('');
                    $('#logo').html('');
                    $('.title').val('');
                    $('.title_en').val('');
                    $('.description').val('');
                    $('.link').val('');
                    $('.registration_form_path').val('');
                    $('.logo_path').val('');
                }
                if (result['status'] === "warning") toastr.warning(result['message']);
            }
        });
    } else {
        toastr.warning('لطفا تمامی موارد ستاره دار را پر کنید');
    }

});

$(document).ready(function () {
    $('.selectSearch').select2();
});

$(document).on('click', '.remove-tr', function () {
    $(this).parents('tr').remove();
});


$(document).on('click', '.removeImageCreated', function () {
    var index = $(this).attr('id');
    var image_name_good = $("#" + index + '.image_name_good').val();
    $.ajax({
        type: "post",
        url: '/dashboard/removeImageCreated',
        data: {
            image_name_good: image_name_good
        },
        success: function (result) {
            if (result['status'] == true) {
                $('#' + index + '.row_image').remove();
                if (!$('.imagesList tr').html()) {
                    $('.submitGood').css({
                        'display': 'none'
                    });
                    index_add_img = 0;
                }
            }
        }
    });
});

// work_sample
$(document).on('click', '.remove_work_sample', function () {
    var work_sample_id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgWorkSample',
        data: {
            work_sample_id: work_sample_id
        },
        success: function (result) {
            if (result['status'] == 'true') {
                $('#work_sample_' + work_sample_id).remove();
            }
        }
    });
});

$(document).ready(function () {
    //submit on create new user
    $("#create-user-form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            }
        });
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: formData,
        }).done(function (data) {
            if (data.status == 'success') {
                swal("عملیات موفق! ", data.message, "success");
                clearNewUserFormInputsData();
            } else if (data.status == 'error') {
                swal("توجه! ", data.message, "error");
                clearNewUserFormInputsData();
            } else if (data.status == 'validation_error') {
                swal("خطا! ", data.messages.join('\n'), "error");
            }
        });
        event.preventDefault();
    });
//------------------------------------------------------------------------------
//select province for create new user
    $('#new_user_province_id').on('change', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var id = $(this).val();
        $.ajax({
            url: '/city/select',
            type: 'post',
            dataType: 'json',
            data: {
                "_token": token,
                "id": id,
            },
            success: function (data) {
                $('#new_user_city_id')
                    .empty()
                    .append('<option value="all">انتخاب شهر</option>')
                ;
                $.each(data.cities, function (i, d) {
                    $('#new_user_city_id').append('<option value="' + d.id + '">' + d.name + '</option>');
                });
            }
        });
    });
//-----------------------------------------------------------------------------------------------------
});

function clearNewUserFormInputsData() {
    $('#new_user_name').val('');
    $('#new_user_family').val('');
    $('#new_user_gender').val('');
    $('#new_user_province_id').val('');
    $('#new_user_city_id').val('');
}
