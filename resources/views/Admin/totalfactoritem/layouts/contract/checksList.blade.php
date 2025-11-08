<table class="table table-bordered bg-check-light">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">مبلغ (ریال)</th>
            <th scope="col">تاریخ وصول </th>
            <th scope="col">متقاضی </th>
            <th scope="col">صادر کننده </th>
            <th scope="col">در وجه </th>
            <th scope="col">شناسه صیادی</th>
            <th scope="col">موضوع</th>
        </tr>
    </thead>
    <tbody id="checks-tbody">
        @php($i=1)
        @foreach($checks as $check)
        <tr>
            <th scope="row">{{$i++}}</th>
            <td>{{number_format($check->amount)}}</td>
            <td>{{verta($check->date)->format('Y/m/d')}}</td>
            <td>{{$check->applicantable ? $check->applicantable->name . ' '.$check->applicantable->family : 'ثبت نشده'}}
                <br>
                {{$check->applicantable ? $check->applicantable->phone : 'ثبت نشده'}}
            </td>
            <td>{{$check->exportable ? $check->exportable->name . ' '.$check->exportable->family : 'ثبت نشده'}}
                <br>
                {{$check->exportable ? $check->exportable->phone : 'ثبت نشده'}}
            </td>
            <td>{{ $check->paytoable ? $check->paytoable->name . ' '.$check->paytoable->family : 'ثبت نشده'}}
                <br>
                {{$check->paytoable ? $check->paytoable->phone : 'ثبت نشده'}}
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
        </tr>
        @endforeach
    </tbody>
</table>