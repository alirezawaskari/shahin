$(document).ready(function () {

    $("body").on('click', '#basic-information-submit', function () {
        updateService('basic-information-form')
    })
    $("body").on('click', '#text-information-submit', function () {
        updateTextInformation()
    })
    $("body").on('click', '.seo-content-submit', function () {
        updateSeoContent()
    })
    $("body").on('click', '#save-all-tabs', function () {
        saveAllServiceTabs()
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

    // The متن form renders a different subset of these textareas for ghorfe vs non-ghorfe
    // services (ghorfe: only `content`). Replace an editor ONLY when its textarea is on the
    // page and it isn't already an editor — the old code replaced all of them (and `content`
    // twice), which threw "instance already exists" and left phantom instances.
    var __hasEl = function (name) {
        return document.getElementById(name) || document.getElementsByName(name).length > 0;
    };
    ['content', 'txt_box_1', 'left_content', 'service_rules'].forEach(function (name) {
        if (__hasEl(name) && !CKEDITOR.instances[name]) {
            CKEDITOR.replace(name, ck_uploader);
        }
    });

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
// When true (during "Save All"), individual tab saves skip their own success popup so
// only one consolidated result is shown. Errors still render in .transfer_message_box.
var suppressSaveSwal = false;

//update service
function updateService(formId){
    if (!suppressSaveSwal) $('.transfer_message_box').empty();
    var form = $('#'+formId);
    var formData = form.serialize();

    return $.ajax({
        url: $('#basic-information-form').attr("action"),
        data: formData,
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                if (!suppressSaveSwal) {
                    swal(response.messages[0], {
                        icon: "success",
                        button: "باشه"
                    });
                }

                // in-place media update for image / gallery saves (no reload)
                if (response.media && typeof applySavedMedia === 'function') {
                    applySavedMedia(response.media);
                }

                if(response.data && response.data.type == 'asked_question'){
                    addNewAskedQuestionToList(response.data.data)
                }
            } else {
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//update texts of service
function updateTextInformation(){
    // Only read editors that actually exist. The ghorfe متن form has just `content`; reading
    // a missing instance's .getData() threw a TypeError that silently aborted the whole save.
    var getData = function (name) {
        return (window.CKEDITOR && CKEDITOR.instances[name]) ? CKEDITOR.instances[name].getData() : null;
    };
    var data = { update_type: 'text_information' };
    var content = getData('content');
    var left_content = getData('left_content');
    var txt_box_1 = getData('txt_box_1');
    var is_complete_content = document.getElementById("is_complete_content").value;
    if (content !== null) data.content = content;
    if (left_content !== null) data.left_content = left_content;
    if (txt_box_1 !== null) data.txt_box_1 = txt_box_1;
    data.is_complete_content = is_complete_content;
    return $.ajax({
        url: $('#text-information-form').attr("action"),
        data: data,
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                if (!suppressSaveSwal) {
                    swal(response.messages[0], {
                        icon: "success",
                        button: "باشه"
                    });
                }
            } else {
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//update seo content
function updateSeoContent(){
    return $.ajax({
        url: $('#seo-content-form').attr("action"),
        data: $('form').serialize(),
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                if (!suppressSaveSwal) {
                    swal(response.messages[0], {
                        icon: "success",
                        button: "باشه"
                    });
                }
            } else {
                $.each(response.messages, function (i, val) {
                    $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                });
            }
        }
    });
}
//update rules of service
function updateRules(){
    // Guard: the service_rules editor may not exist on every service form.
    if (!(window.CKEDITOR && CKEDITOR.instances.service_rules)) return;
    const rules = CKEDITOR.instances.service_rules.getData();

    return $.ajax({
        url: $('#rules-form').attr("action"),
        data: {
            update_type:'rules',
            rules:rules,
        },
        type : 'PATCH',
        success: function (response) {
            if (response.status == 'success') {
                if (!suppressSaveSwal) {
                    swal(response.messages[0], {
                        icon: "success",
                        button: "باشه"
                    });
                }
            }
        }
    });
}
// Save every content tab in one go (triggered by the header "ثبت همه" button). Fires each
// present tab's save, suppresses the per-tab popups, and shows one consolidated result.
// Deliberately excludes row/action operations (insert/update/delete question, session-title
// reorder) — those are explicit actions, not "save the current content".
function saveAllServiceTabs(){
    suppressSaveSwal = true;
    $('.transfer_message_box').empty();

    var jobs = [];
    var add = function (present, fn) {
        if (!present) return;
        try { var p = fn(); if (p) jobs.push(p); }
        catch (e) { console.error('save-all: a tab failed to save', e); }
    };

    add(document.getElementById('basic-information-form'),     function(){ return updateService('basic-information-form'); });
    add(document.getElementById('text-information-form'),      function(){ return updateTextInformation(); });
    add(document.getElementById('site-information-form'),      function(){ return updateService('site-information-form'); });
    add(document.getElementById('rules-form'),                 function(){ return updateRules(); });
    add(document.getElementById('question-information-form'),  function(){ return updateService('question-information-form'); });
    add(document.getElementById('first-images-form'),          function(){ return updateService('first-images-form'); });
    add(document.getElementById('portfolio-information-form'), function(){ return updateService('portfolio-information-form'); });
    add(document.getElementById('site-images-form'),           function(){ return updateService('site-images-form'); });
    add(document.getElementById('seo-content-form'),      function(){ return updateSeoContent(); });

    $.when.apply($, jobs).always(function () {
        suppressSaveSwal = false;
        if ($('.transfer_message_box').children().length) {
            swal('برخی بخش‌ها ذخیره نشد', 'خطاهای نمایش داده شده را بررسی کنید.', 'warning');
        } else {
            swal('همه بخش‌ها با موفقیت ذخیره شد', { icon: 'success', button: 'باشه' });
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
