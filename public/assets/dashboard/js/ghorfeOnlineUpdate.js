$(document).ready(function () {
    $("body").on('click', '#insert-ghorfe-online-question-submit', function () {
        insertAskedQuestion('question-insert-form')
    })
    $("body").on('click', '.update-asked-question', function () {
        console.log('test');
        updateAskedQuestion(this)
    })
    $("body").on('click', '.delete-asked-question', function () {
        console.log('test');
        deleteAskedQuestion(this)
    })
});

function insertAskedQuestion(formId) {
    $('.transfer_message_box').empty();
    var form = $('#' + formId);
    var formData = form.serialize();
    $.ajax({
        url: $('#question-insert-form').attr("action"),
        data: formData,
        type: 'POST',
        success: function (response) {
            if (response.status == 'success') {
                swal(response.messages[0], {
                    icon: "success",
                    button: "باشه"
                });
                if (response.data.type == 'asked_question') {
                    $('#question').val('');
                    $('#answer').val('');
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

function updateAskedQuestion(input) {
    $(input).data('id')
    $.ajax({
        url: $('#question-insert-form').attr("action"),
        data: {
            asked_question_id: $(input).data('id'),
            question: $('#question' + $(input).data('id')).val(),
            answer: $('#answer' + $(input).data('id')).val(),
            update_type: 'update_question'
        },
        type: 'POST',
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

function deleteAskedQuestion(input) {
    $(input).data('id')
    $.ajax({
        url: $('#question-insert-form').attr("action"),
        data: {
            asked_question_id: $(input).data('id'),
            update_type: 'delete_question'
        },
        type: 'POST',
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

function addNewAskedQuestionToList(asked_question) {
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
