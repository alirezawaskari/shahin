<div class="col-12 col-md-12 mb-3">
    <label >سوالات متداول </label>

        <table class="table table-bordered" id="askedQuestion">
            <tr>
                <th>سوال</th>
                <th>جواب</th>
                <th>اقدام</th>
            </tr>
            <tbody id="asked-questions-tbody">
                @foreach($service->askedQuestions()->get() as  $asked_question)
                    <tr>
                        <td><input required type="text" id="question{{$asked_question->id}}" name="asked_question_question" value="{{$asked_question->question}}"
                                   placeholder="لطفا سوال را وارد کنید" class="form-control"/></td>
                        <td>
                            <input required type="text" id="answer{{$asked_question->id}}" name="asked_question_answer" value="{{$asked_question->answer}}"
                                   placeholder="لطفا جواب را وارد کنید" class="form-control"/></td>
                        <td>
                            <button data-id="{{$asked_question->id}}" id="asked-question-delete-{{$asked_question->id}}" class="btn btn-danger delete-asked-question remove-tr"><i class="fa fa-remove"></i></button>
                            <button data-id="{{$asked_question->id}}" id="asked-question-update-{{$asked_question->id}}" class="btn btn-success update-asked-question update-tr"><i class="fa fa-edit"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</div>

