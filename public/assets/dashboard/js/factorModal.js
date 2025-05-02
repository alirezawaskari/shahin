$(document).ready(function () {
    /*  submit factor show btn modal*/
    $("body").on('click', '.factor-show', function () {
        var factorId= $(this).data('id');
        $.ajax(
            {
                url: `/dashboard/factor/${factorId}/edit`,
                type: 'get',
                success: function (response) {
                    let view = response.data.view;
                    $('.modal-body-factor-show').html(view)
                }
            });
    })
    /*  submit comment for factor */
    $("body").on('click', '#submit-factor-comment', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#factor-comment-form');
        var formData = form.serialize();
        document.getElementById('loading').style.display = 'block';
        swal({
            title: "اطلاعات ارسال گردید !",
            timer: 2000
        });
        $.ajax(
            {
                url: "/dashboard/factor-comment",
                type: 'post',
                "_token": token,
                data: formData,
                success: function (response) {
					swal("عملیات موفق", "", "success")
                    $('.comment-item').prepend(`
                     <div class=" rounded p-3 mb-3 border border-success" style="background: rgb(235,248,235)">
                     <span class="badge badge-success">ثبت گردید  <i class="fa fa-check"></i></span>
                     <hr>
                        <div class="panel-heading">
                            <span class="d-inline-block">${response.data.user.name + ' ' + response.data.user.family}</span>
                            <span class="text-black-50 " >${response.data.comment.jalali_created_at}</span>
                        </div>
                        <p class="mt-4 mr-5">
                            ${response.data.comment.comment}
                        </p>
                    </div>
                    `)
                    $('#comment-body').val('');
                    $('#commentModal').modal('hide');
                    $('#commentModal').on('hidden.bs.modal', function () {
                        $('body').addClass('modal-open');
                    });
                    $('#do_filter').click();

                }
            });
    })

    /*  submit comment for factor */
    $("body").on('click', '.factor-update-submit', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var file= $('#bill_image_input_edit_factor').prop('files')[0];
        var url = $('#form-status-mali').attr('action');

          var form = $('#form-status-mali');
          var formData = form.serializeArray();
          var fdObject =new FormData();
          $.each(formData, function( index, value ) {
              fdObject.append(value.name, value.value);
        });
        fdObject.append('bill_image', file);
        $.ajax(
            {
                url: "/dashboard/factor-update",
                type: 'post',
                data: fdObject,
                contentType: false,
                processData: false,
                '_token': token,
                success: function (response,page) {
                    swal({
                        title: 'عملیات موفق !',
                        icon: "success",
                        button: "باشه",
                    });
                     location.reload();
                    // fetchData(page)
                    // $("#bill_image_td").empty();
                    // $("#bill_image_td").append(`
                    //    <a href="${response.data.bill_image_link}" class="btn btn-primary" target="_blank">مشاهده</a>
                    // `)
                    // $('#factorShowModal').modal('hide');
                }
            });
    })

    /*  submit comment for factor */
    $("body").on('click', '#submit-factor-comment-modal', function () {

        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#factor-comment-form-modal');
        var formData = form.serialize();

        // document.getElementById('loading').style.display = 'block';
        swal({
            title: "اطلاعات ارسال گردید !",
            timer: 2000
        });
        $.ajax(
            {
                url: "/dashboard/factor-comment",
                type: 'post',
                "_token": token,
                data: formData,
                success: function (response) {
                    swal("عملیات موفق", "", "success")
                    $('.comment-item').prepend(`
                     <div class=" rounded p-3 mb-3 border border-success" style="background: rgb(235,248,235)">
                     <span class="badge badge-success">ثبت گردید  <i class="fa fa-check"></i></span>
                     <hr>
                        <div class="panel-heading">
                            <span class="d-inline-block">${response.data.user.name + ' ' + response.data.user.family}</span>
                            <span class="text-black-50 " >${response.data.comment.jalali_created_at}</span>
                        </div>
                        <p class="mt-4 mr-5">
                            ${response.data.comment.comment}
                        </p>
                    </div>
                    `)
                    $('#comment-body').val('');
                    $('#commentModal').modal('hide');
                    $('#commentModal').on('hidden.bs.modal', function () {
                        $('body').addClass('modal-open');
                    });
                    $('#do_filter').click();

                }
            });
    })

});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#blah').attr('src', e.target.result).width(300).height(400)
        };
        reader.readAsDataURL(input.files[0]);
    }
}


