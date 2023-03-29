<div class="load" style="position: relative;">
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ردیف</th>
        @permission('request-detail')
        <th>کاربر</th>
        <th>واحد</th>
        @endpermission
        <th>انبار</th>
        <th>وضعیت</th>
        <th>تاریخ ثبت</th>
        <th>اقدام</th>
    </tr>
    </thead>
    <tbody>
    @foreach($request_lists as $ind=>$item)
        <tr>
            <td>{{++$ind}}</td>
            @permission('request-detail')
            <td>{{\App\Helpers\Helper::user($item->user_id,'name')}}   {{\App\Helpers\Helper::user($item->user_id,'family')}} </td>
            <td> {{$item->card_name}} </td>
            @endpermission
            <td> {{$item->store_room_name}} </td>
            <td>{{$item::STATUS[$item->status]}}
                @if($item->status == 4)
                    (   {{$item->description}}   )
                @endif
            </td>
            <td>{{ verta($item->created_at)->format('%d %B ، %Y - H:i') }} </td>
            <td>
                <div data-v-15965e3b="" class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                       class="btn btn-dark btn-floating btn-icon btn-sm">
                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu">

                        @permission('request-detail')
                        <a href="{{route('request.show',[$item->id])}}" target="_blank">
                            <button type="button" class="dropdown-item pointer">مشاهده</button>
                        </a>
                        @endpermission

                        {{--@permission('request')--}}
                        @if($item->status == 5)
                            <a href="/dashboard/changeStatusRequest/{{$item->id}}/6">
                                <button type="button" class="dropdown-item pointer">دریافت شده</button>
                            </a>
                        @endif
                        {{--@endpermission--}}

                        @if($item->status == 1)
                            <a href="{{route('request.edit',[$item->id])}}" target="_blank">
                                <button type="button" class="dropdown-item pointer"> ویرایش</button>
                            </a>
                        @endif
                        @permission('request-delete')
                        <form method="post" action="{{route('request.destroy',[$item->id])}}">
                            {{ method_field('DELETE') }}  {{csrf_field()}}
                            <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                            </button>
                        </form>
                        @endpermission
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{$request_lists->links()}}