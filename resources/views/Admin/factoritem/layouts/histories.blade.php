<div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ثبت توضیح جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="factor-comment-form">
                <div class="modal-body">
                        <textarea required class="form-control" name="comment" id="comment-body" cols="30" rows="10"
                                  placeholder="توضیح خود را بنویسید..."></textarea>
                    <input type="hidden" value="{{ $factor->id }}" name="factor_id">
                </div>
                <div class="modal-footer">
                    <span id="submit-factor-comment" class="btn btn-primary">ثبت</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="tab-pane fade p-t-20" id="contact" role="tabpanel" aria-labelledby="contact-tab">
    <span>توضیحات همکاران</span>
    <span class="btn btn-success py-1 px-2" data-toggle="modal" data-target="#commentModal">ثبت توضیح
                    جدید</span>
    <div class="mt-4">
        <div class="comment-item"></div>
        @if(count($user_histories) > 0)
            <div class="modal-body table-responsive card-body width-auto">
                <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                       id="datatable-history-blade">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">کالسنتر</th>
                        <th scope="col">ثبت کننده</th>
                        <th scope="col">تاریخ ثبت</th>
                        <th scope="col">گزارش ها</th>
                    </tr>
                    </thead>
                    <tbody class="histories-table-body">
                    @php($i=1)
                    @foreach($user_histories as $user_history)
                        <tr>
                            <td>{{$i++}} </td>
                            <td>{{isset($user_history['callcenter']) ? $user_history['callcenter'] : 'ندارد'}}</td>
                            <td>{{$user_history['user']}}</td>
                            <td>{{$user_history['date']}}</td>
                            <td>{!!$user_history['description']!!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p>هیچ توضیحی ثبت نشده</p>
        @endif
    </div>
</div>
