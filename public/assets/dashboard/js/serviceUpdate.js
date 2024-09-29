$(document).ready(function () {

    $("body").on('click', '#basic-information-submit', function () {
        updateService('basic-information-form')
    })
    $("body").on('click', '#text-information-submit', function () {
        updateTextInformation()
    })
    $("body").on('click', '#site-information-submit', function () {
        updateService('site-information-form')
    })
    $("body").on('click', '#rules-submit', function () {

        updateRules()
    })
    $("body").on('click', '#question-information-submit', function () {
        updateService('question-information-form')
    })
    $("body").on('click', '#first-images-submit', function () {
        updateService('first-images-form')
    })
    $("body").on('click', '#portfolio-information-submit', function () {
        updateService('portfolio-information-form')
    })
    $("body").on('click', '#site-images-submit', function () {
        updateService('site-images-form')
    })
    $("body").on('click', '#insert-question-submit', function () {
        updateService('question-insert-form')
    })
    $("body").on('click', '#session-titles-submit', function () {
        updateServiceSessionTitles('session-titles-form')
    })
    $("body").on('click', '.update-asked-question', function () {
        updateAskedQuestion(this)
    })
    $("body").on('click', '.delete-asked-question', function () {
        deleteAskedQuestion(this)
    })

    CKEDITOR.replace( 'content',ck_uploader ,ck_uploader) ;
    CKEDITOR.replace( 'txt_box_1',ck_uploader) ;
    CKEDITOR.replace( 'left_content',ck_uploader) ;
    CKEDITOR.replace('content',ck_uploader) ;
    CKEDITOR.replace( 'service_rules', ck_uploader);

    // work_sample
    $(document).on('click', '.remove_work_sample', function () {
        var work_sample_id = $(this).attr('id');
        $.ajax({
            type: "post",
            url: '/dashboard/removeImgWorkSample',
            data: {work_sample_id: work_sample_id},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                if (result['status'] == 'true') {
                    $('#work_sample_' + work_sample_id).remove();
                }
            }
        });
    });
        // add new session title to list
    $('body').on('click','#add-session-title-input',function (){
        new_title =$('#new-session-title-input').val();
        if(new_title==''){
            swal('خطا','عنوان درج نشده است !','error')
        }else{
            $('#sortable').append(`
                <li class="list-group-item" style="cursor: pointer" data-name="${new_title}">
                    ${new_title}
                        <i class="fa fa-trash  text-danger trash-title float-right btn btn-sm btn-outline-warning"></i>
                    </li>
               `)
            $('#new-session-title-input').val('');
        }
    })

    //trash session title
    $('body').on('click','.trash-title',function (){

        swal({
            title: "آیا مطمئن هستید؟",
            text: "",
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(res => {
            if (res){
                $(this).parent().remove();
            }
        });
    })
//enter key press in input for add session title item
    $('#new-session-title-input').keypress((e) => {
        // Enter key corresponds to number 13
        if (e.which === 13) {
            $('#add-session-title-input').click();
        }
    })

});
//update service
function updateService(formId){
    $('.transfer_message_box').empty();
    var form = $('#'+formId);
    var formData = form.serialize();

    $.ajax({
        url: $('#basic-information-form').attr("action"),
        data: formData,
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                swal(response.messages[0], {
                    icon: "success",
                    button: "باشه"
                });

                if(response.data.type == 'asked_question'){
                    addNewAskedQuestionToList(response.data.data)
                }
            } else {
                $('.transfer_message_box').empty();
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//update texts of service
function updateTextInformation(){
    var content = CKEDITOR.instances.content.getData();
    var left_content = CKEDITOR.instances.left_content.getData();
    var txt_box_1 = CKEDITOR.instances.txt_box_1.getData();
    $.ajax({
        url: $('#basic-information-form').attr("action"),
        data: {
            update_type:'text_information',
            content:content,
            left_content:left_content,
            txt_box_1:txt_box_1
        },
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                swal(response.messages[0], {
                    icon: "success",
                    button: "باشه"
                });
            } else {
                $('.transfer_message_box').empty();
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//update rules of service
function updateRules(){
    // var rules = CKEDITOR.instances.rules.getData();
    const rules = CKEDITOR.instances.service_rules.getData();

    $.ajax({
        url: $('#rules-form').attr("action"),
        data: {
            update_type:'rules',
            rules:rules,
        },
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                swal(response.messages[0], {
                    icon: "success",
                    button: "باشه"
                });
            }
        }
    });
}
//set and update session titles of service
function updateServiceSessionTitles(){
    swal({
        title: "آیا مطمئن هستید؟",
        text: "",
        icon: "warning",
        buttons: {
            confirm: 'بله',
            cancel: 'خیر'
        },
        dangerMode: true
    }).then(res => {
        if (res) {
            var session_titles = $('#sortable li').map(function () {
                return $(this).data("name");
            }).get();
            $.ajax({
                url: $('#basic-information-form').attr("action"),
                data: {
                    update_type:'session_titles',
                    session_titles:session_titles,
                },
                type : 'PATCH',
                success: function (response) {
                    if (response.status == 'success') {
                        swal(response.messages[0], {
                            icon: "success",
                            button: "باشه"
                        });
                    }
                }
            });
        }
        })


}
// update list of asked question
function addNewAskedQuestionToList(asked_question){
    $('#asked-questions-tbody').prepend(`
    <tr>
                        <td><input type="text" id="question${asked_question.id}" name="asked_question_question" value="${asked_question.question}"
                                   placeholder="لطفا سوال را وارد کنید" class="form-control"/></td>
                        <td>
                            <input type="text" id="answer${asked_question.id}" name="asked_question_answer" value="${asked_question.answer}"
                                   placeholder="لطفا جواب را وارد کنید" class="form-control"/></td>
                        <td>
                            <button data-id="${asked_question.id}" id="asked-question-delete-${asked_question.id}" class="btn btn-danger delete-asked-question remove-tr"><i class="fa fa-remove"></i></button>
                            <button data-id="${asked_question.id}" id="asked-question-update-${asked_question.id}" class="btn btn-success update-asked-question update-tr"><i class="fa fa-edit"></i></button>
                        </td>
                    </tr>
    `)
}
//insert update asked question
function updateAskedQuestion(input){
     $(input).data('id')
    $.ajax({
        url: $('#basic-information-form').attr("action"),
        data: {
            asked_question_id: $(input).data('id'),
            question:$('#question'+$(input).data('id')).val(),
            answer:$('#answer'+$(input).data('id')).val(),
            update_type:'update_single_question'
        },
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                swal(response.messages[0], {
                    icon: "success",
                    button: "باشه"
                });
            } else {
                $('.transfer_message_box').empty();
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//delete asked question
function deleteAskedQuestion(input){
     $(input).data('id')
    $.ajax({
        url: $('#basic-information-form').attr("action"),
        data: {
            asked_question_id: $(input).data('id'),
            update_type:'delete_single_question'
        },
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                swal(response.messages[0], {
                    icon: "success",
                    button: "باشه"
                });
            } else {
                $('.transfer_message_box').empty();
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//bind sortable to ul
$(function () {
    $('#sortable').sortable().bind('sortupdate');
});
