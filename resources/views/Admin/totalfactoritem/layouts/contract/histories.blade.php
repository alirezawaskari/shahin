<table class="vuetable table table-bordered table-striped table-responsive-md w-100" id="datatable-history-blade">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">ثبت کننده</th>
            <th scope="col">توضیحات</th>
            <th scope="col">تاریخ ثبت</th>
        </tr>
    </thead>
    <tbody class="histories-table-body">
        @php($i=1)
        @foreach($item['contract_histories'] as $contract_history)
        <tr>
            <td>{{$i++}} </td>
            <td>{{$contract_history->user->name.' '.$contract_history->user->family}}</td>
            <td>{{$contract_history->description}}</td>
            <td>{{verta( $contract_history->created_at )->format( '%d %B ، %Y - H:i' )}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
