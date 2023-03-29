<div class="card border border-secondary rounded ">
    <div class="card-header">

        <h4>  <i class="fa fa-money  pr-5" style="color: green" aria-hidden="true"></i> پرداختی های موفق</h4>
    </div>
<table
    class="table table-bordered table-striped table-responsive-md">
    <tr>
        <th>شماره قبض</th>
        <th>مرحله پرداخت</th>
        <th>مبلغ واریز شده</th>
        <th>نوع پرداخت - موضوع پرداخت</th>
        <th>تاریخ و ساعت پرداخت</th>
        <th>وضعیت</th>
        <th>اقدام</th>
    </tr>
    <tr>
        <td>{{ $item['payment']->resnumber }}</td>
        <td>{{ $item['payment']->level }}</td>
        <td>{{ number_format($item['payment']->price) }} ریال</td>
        <td>{{ $item['payment']::TYPE[$item['payment']->type] }}
            - {{ $item['payment']::TITLE[$item['payment']->title] }}</td>
        <td>{{ verta($item['payment']->created_at)->format('%d %B ، %Y - H:i:s') }}</td>
        <td>{{ \App\Models\Factor::status($item['payment']->status)}}</td>
        <td>
            <div data-v-15965e3b="" class="dropdown">
                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                   class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu">
                    <a>
                        <button data-id="{{ $item['payment']->resnumber }}" type="button" class="factor-show dropdown-item pointer"  data-toggle="modal"
                                data-target="#factorShowModal">مشاهده قبض</button>
                    </a>
                </div>
            </div>
        </td>
    </tr>
    @foreach($item['payments'] as $payment)
        <tr>
            <td>{{ $payment->resnumber }}</td>
            <td>{{ $payment->level }}</td>
            <td>{{ number_format($payment->price) }} ریال</td>
            <td>{{ $payment::TYPE[$payment->type] }}
                - {{ $payment::TITLE[$payment->title] }}</td>
            <td>{{ verta($payment->created_at)->format('%d %B ، %Y - H:i:s') }}</td>
            <td>{{ App\Models\Factor::status($payment->status)}}</td>
            <td>
                <div data-v-15965e3b="" class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                       class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a>
                            <button data-id="{{ $payment->resnumber }}" type="button" class="factor-show dropdown-item pointer"  data-toggle="modal"
                                    data-target="#factorShowModal">مشاهده قبض</button>
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
</table>
<div class="col-12 mb-2">
    <div class="badge badge-primary font-size-13 mb-3 p-2">
        جمع مبلغ پرداختی
        :
        {{ number_format($item['payment']->price + $item['payments']->where('status' , 1)->sum('price') + $item['payments']->where('status' , 6)->sum('price')) }}
        ریال
    </div>
</div>
</div>
@include('Admin.factor.factorShowModal')
