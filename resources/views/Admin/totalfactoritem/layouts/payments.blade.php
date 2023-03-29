<div class="card col-12 mt-2 pt-4 col-l main border">
    <h4 class="mb-3 card-header"><i class="fa fa-money" aria-hidden="true"></i> پرداخت های انجام شده </h4>
    <div class="card-body">
        <table class="table table-bordered table-striped table-responsive-md">
            <tr>
                <th>شماره قبض</th>
                <th>مرحله پرداخت</th>
                <th>مبلغ واریز شده</th>
                <th>موضوع پرداخت</th>
                <th>نوع پرداخت</th>
                <th>تاریخ و ساعت پرداخت</th>
                <th>وضعیت</th>
                <th>اقدام</th>
            </tr>
            <tr>
                <td>{{ $item['payment']->resnumber }}</td>
                <td>{{ $item['payment']->level }}</td>
                <td>{{ number_format($item['payment']->price) }} ریال</td>
                <td>{{ $item['payment']::TITLE[$item['payment']->title] }}</td>
                <td>{{ $item['payment']::TYPE[$item['payment']->type] }}</td>
                <td>{{ verta($item['payment']->created_at)->format('%d %B ، %Y - H:i:s') }}</td>
                <td>
                    @if ($item['payment']->status == 0)
                    <span class="badge badge-warning">{{ $item['payment']::status($item['payment']->status)
                        }}</span>
                    @elseif($item['payment']->status == 1)
                    <span class="badge badge-success">{{ $item['payment']::status($item['payment']->status)
                        }}</span>
                    @elseif($item['payment']->status == 2 || $item['payment']->status == 3 || $item['payment']->status
                    == 5)
                    <span class="badge badge-danger">{{ $item['payment']::status($item['payment']->status)
                        }}</span>
                    @endif
                </td>
                <td>
                    <div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a >
                                <button  data-id="{{ $item['payment']->resnumber}}" type="button" class="factor-show dropdown-item pointer"  data-toggle="modal"
                                        data-target="#factorShowModal">مشاهده قبض</button>
                            </a>
                            @permission('factor-delete')
                                <form class="delete_form" method="post"
                                    action="{{ route('factor.destroy', [$item['payment']->resnumber]) }}">
                                    @method('DELETE')
                                    @csrf
                                    <input type="button" class="dropdown-item pointer delete_submit"
                                        id="{{ $item['payment']->id }}" value="حذف">
                                </form>
                            @endpermission
                        </div>
                    </div>
                </td>
            </tr>
            @foreach($item['payments'] as $payment)
            <tr>
                <td>{{ $payment->resnumber }}</td>
                <td>{{ $payment->level }}</td>
                <td>{{ number_format($payment->price) }} ریال</td>
                <td>{{ $payment::TITLE[$payment->title] }}</td>
                <td>{{ $payment::TYPE[$payment->type] }}</td>
                <td>{{ verta($payment->created_at)->format('%d %B ، %Y - H:i:s') }}</td>
                <td>
                    @if ($payment->status == 0)
                    <span class="badge badge-warning">{{ $payment::status($payment->status)
                        }}</span>
                    @elseif($payment->status == 1)
                    <span class="badge badge-success">{{ $payment::status($payment->status)
                        }}</span>
                    @elseif($payment->status == 2 || $payment->status == 3 || $payment->status == 5)
                    <span class="badge badge-danger">{{ $payment::status($payment->status)
                        }}</span>
                    @endif
                </td>
                <td>
                    <div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a >
                                <button data-id="{{ $payment->resnumber }}" type="button" class="factor-show dropdown-item pointer"  data-toggle="modal"
                                        data-target="#factorShowModal">مشاهده قبض</button>
                            </a>
                            @permission('factor-delete')
                                <form class="delete_form" method="post"
                                    action="{{ route('factor.destroy', [$payment->resnumber]) }}">
                                    @method('DELETE')
                                    @csrf
                                    <input type="button" class="dropdown-item pointer delete_submit"
                                        id="{{ $payment->id }}" value="حذف">
                                </form>
                            @endpermission
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <div class="col-12 mb-2">
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                مجموع تراز مالی پرداختی :
                {{ number_format($item['payment']->price + $item['payments']->where('status' , 1)->sum('price') -
                $item['payments']->where('status' , 2)->sum('price')) }}
                ریال
            </div>
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                مجموع پرداختی :
                {{ number_format($item['payments']->where('status' , 1)->sum('price')+$item['payment']->price)}}
                ریال
            </div>
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                مجموع دریافتی :
                {{ number_format($item['payments']->where('status' , 2)->sum('price')) }}
                ریال
            </div>
        </div>
    </div>
</div>
@include('Admin.factor.factorShowModal')

