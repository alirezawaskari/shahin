<div class="card border border-secondary rounded box">
    <div class="card-header">
        <h4>جلسات</h4>
    </div>
    <div class="header-body">
        <table class="table table-bordered table-striped table-responsive-md">
            <tr>
                <th>تاریخ و ساعت</th>
                <th>شماره دندان</th>
                <th>درمان</th>
                <th>توضیحات</th>
                <th>پزشک</th>
                <th>دستیار</th>
                <th>هزینه لابراتوار (ریال)</th>
                <th>وضعیت</th>
                <th>ثبت کننده</th>
                <th>تایید کننده</th>
            </tr>
            @foreach($sessions as $session)
                <tr>
                    <td>{{ verta($session->date)->format('%d %B ، %Y - H:i') }}</td>
                    <td>
                        {{ $session->factorItemServiceTariff ? $session->factorItemServiceTariff->value: 'ندارد'}}
                    </td>
                    <td>
                        {{ $session->factorItemServiceTariff && $session->factorItemServiceTariff->serviceTariff ? $session->factorItemServiceTariff->serviceTariff->name : 'ندارد' }}
                    </td>
                    <td>{{ $session->description }}</td>
                    <td>
                        {{ $session->agent ? $session->agent->name .' '. $session->agent->family : 'ندارد'}}
                    </td>
                    <td>
                        {{ $session->assistant ? $session->assistant->name .' '. $session->assistant->family : 'ندارد' }}
                    </td>
                    <td>
                        {{ number_format($session->laboratory_price) }}
                    </td>
                    <td>
                        {{ App\Models\Session::status($session->status) }}
                    </td>
                    <td>
                        {{ $session->user->family }}
                    </td>
                    <td>
                        {{ $session->confirmer ? $session->confirmer->family : 'ندارد' }}
                    </td>
                </tr>
            @endforeach
        </table>
        <div class="col-12 mb-2">
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                جمع مبلغ جلسات
                :
                {{ number_format($sessions->where('status' , '!=' , false)->sum('price')) }}
                ریال
            </div>
        </div>
    </div>
</div>
