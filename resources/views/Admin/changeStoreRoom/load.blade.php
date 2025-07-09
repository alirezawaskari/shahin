<div class="load" style="position: relative;">
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ردیف</th>
        <th>درخواست کننده</th>
        <th>تایید کننده</th>
        <th>تحویل دهنده</th>
        <th>کالا</th>
        <th>تعداد</th>
        <th>قفسه - سطر - ستون</th>
        <th>نوع</th>
        <th>تاریخ ثبت</th>
    </tr>
    </thead>
    <tbody>
    @foreach($change_store_room as $ind=>$item)
        <tr>
            <td>{{++$ind}}</td>
            <td>{{\App\Models\Request::requestByField($item->request_id,'user_id')}}</td>
            <td>{{\App\Models\Request::requestByField($item->request_id,'confirmator')}}</td>
            <td>{{\App\Models\Request::requestByField($item->request_id,'sender_id')}}</td>
            <td>{{\App\Helpers\Helper::product($item->product_id,'name')}}</td>
            <td>{{$item->number}} </td>
            <td>{{\App\Models\ChangeStoreRoom::storeRoomProductAddress($item)}}</td>
            <td>@if($item->type == 0)  خارج شد  @elseif($item->type == 1) وارد شد  @endif </td>
            <td>{{ verta($item->created_at)->format('%d %B ، %Y - H:i') }} </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{$change_store_room->links()}}
