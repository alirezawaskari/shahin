<div class="container">
    <div class="row">
        <div class="card col-md-12 m-5">
            <div class="card-header">
                <h3>چک های پرونده</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered bg-check-light">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">مبلغ (ریال)</th>
                        <th scope="col">تاریخ وصول </th>
                        <th scope="col">متقاضی </th>
                        <th scope="col">صادر کننده </th>
                        <th scope="col">در وجه </th>
                        <th scope="col">شماره سریال</th>
                        <th scope="col">موضوع</th>
                        <th scope="col">تصاویر</th>
                        <th scope="col">توضیحات</th>
                        <th scope="col">در اختیار</th>
                        <th scope="col">وضعیت</th>
                    </tr>
                    </thead>
                    <tbody id="checks-tbody">
                    @php($i=1)
                    @foreach($checks as $check)
                        <tr>
                            <th scope="row">{{$i++}}</th>
                            <td>{{number_format($check->amount)}}</td>
                            <td>{{verta($check->date)->format('Y/m/d')}}</td>
                            <td>{{$check->applicantable->name . ' '.$check->applicantable->family}}
                                <br>
                                {{$check->applicantable->phone}}
                            </td>
                            <td>{{$check->exportable->name . ' '.$check->exportable->family}}
                                <br>
                                {{$check->exportable->phone}}
                            </td>
                            <td>{{$check->paytoable->name . ' '.$check->paytoable->family}}
                                <br>
                                {{$check->paytoable->phone}}
                            </td>
                            <td>{{$check->serial_code}}</td>
                            <td>
                                @switch($check->title)
                                    @case('installment')
                                    <span class="badge badge-primary"> قسط</span>
                                    @break
                                    @case('guaranty')
                                    <span class="badge badge-info"> ضمانت</span>
                                    @break
                                    @default
                                    <span>{{$check->title}}</span>
                                @endswitch
                            </td>
                            <td>
                                @foreach($check->files as $file)
                                    <a href="{{$file->path}}" target="_blank"> <img src="{{$file->path}}" width="70px" alt=""></a>
                                @endforeach
                            </td>
                            <td>{{$check->histories->count()>0?$check->histories->last()->description :''}}</td>
                            <td>
                                {{$check->assetTransactions()->exists()?$check->assetTransactions->last()->receiverable->name.' '.$check->assetTransactions->last()->receiverable->family:'لاله'}}
                                <br>
                                {{$check->assetTransactions()->exists()?$check->assetTransactions->last()->receiverable->phone:''}}
                            </td>
                            <td>
                                @switch ($check->status)
                                @case(0)
                                <span class="badge badge-warning">در انتظار وصول</span>
                                @break
                                @case(1)
                                <span class="badge badge-success">وصول شده</span>
                                @break
                                @case(2)
                                <span class="badge badge-danger">برگشت خورده</span>
                                @break
                                @case(3)
                                <span class="badge badge-info">عودت داده شده</span>
                                @break
                                @default
                                <span class="badge badge-info">{{$check->status}}</span>
                                @break
                            @endswitch
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

