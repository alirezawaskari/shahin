<div class="container">
    <div class="row">
        <div class="card col-md-12 m-5">
            <div class="card-header">
                <h3>سفته های پرونده</h3>
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
                    @foreach($promissories as $promissory)
                        <tr>
                            <th scope="row">{{$i++}}</th>
                            <td>{{number_format($promissory->amount)}}</td>
                            <td>{{verta($promissory->date)->format('Y/m/d')}}</td>
                            <td>{{$promissory->applicantable->name . ' '.$promissory->applicantable->family}}
                                <br>
                                {{$promissory->applicantable->phone}}
                            </td>
                            <td>{{$promissory->exportable->name . ' '.$promissory->exportable->family}}
                                <br>
                                {{$promissory->exportable->phone}}
                            </td>
                            <td>{{$promissory->paytoable->name . ' '.$promissory->paytoable->family}}
                                <br>
                                {{$promissory->paytoable->phone}}
                            </td>
                            <td>{{$promissory->serial_code}}</td>
                            <td>
                                @switch($promissory->title)
                                    @case('installment')
                                    <span class="badge badge-primary"> قسط</span>
                                    @break
                                    @case('guaranty')
                                    <span class="badge badge-info"> ضمانت</span>
                                    @break
                                    @default
                                    <span>{{$promissory->title}}</span>
                                @endswitch
                            </td>
                            <td>
                                @foreach($promissory->files as $file)
                                    <a href="{{$file->path}}" target="_blank"> <img src="{{$file->path}}" width="70px" alt=""></a>
                                @endforeach
                            </td>
                            <td>{{$promissory->histories->count()>0?$promissory->histories->last()->description :''}}</td>
                            <td>
                                {{$promissory->assetTransactions()->exists()?$promissory->assetTransactions->last()->receiverable->name.' '.$promissory->assetTransactions->last()->receiverable->family:'لاله'}}
                                <br>
                                {{$promissory->assetTransactions()->exists()?$promissory->assetTransactions->last()->receiverable->phone:''}}
                            </td>
                            <td>
                                @switch ($promissory->status)
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
                                <span class="badge badge-info">{{$promissory->status}}</span>
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

