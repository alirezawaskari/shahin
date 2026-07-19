<div class="load" style="position: relative;">
    <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
        <thead>
        <tr>
            <th>شناسه</th>
            <th>عکس</th>
            <th>عنوان</th>
            <th>دسته بندی</th>
            <th>قیمت</th>
            <th>نوع خرید</th>
            <th>نوع پرداخت</th>
            <th>نمایش در سایت</th>
            <th>وضعیت</th>
            <th>اقدام</th>
        </tr>
        </thead>
        <tbody>
        @foreach($services as $item)
            <tr>
                <td>{{$item->id}}</td>
                <td>
                    @if(File::exists(public_path() .$item->image))
                        <img src="{{$item->image}}" alt="" class="border-radius5" width="80px">
                    @endif
                </td>
                <td>{{$item->title}} </td>
                <td>{{\App\Models\Category::findCategory($item->category_id,'title')}} </td>
                <td>{{number_format($item->price)}} </td>
                <td>
                    @if($item->purchase_type == 0) آنلاین @elseif($item->purchase_type == 1)
                        حضوری @elseif($item->purchase_type == 2) هر دو @endif
                </td>
                <td>@if($item->pay_type == 0) نقدی @elseif($item->pay_type == 1) اقساط @elseif($item->pay_type == 2) هر
                    دو @endif</td>
                <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->isShow) !!} </td>
                <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->status) !!} </td>
                <td>
                    <div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                           class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('service.edit' , [ $item->slug]) }}" class="dropdown-item pointer">ویرایش</a>
                            {{-- <a href="{{route('service.financialPalnsTypes', [ $item->slug])}}">
                                <button type="button" class="dropdown-item pointer">طرح های مالی</button>
                            </a> --}}
                            <a href="{{route('service.serviceItem.all', [ $item->slug])}}">
                                <button type="button" class="dropdown-item pointer">لیست آیتم ها</button>
                            </a>
                            <a href="/dashboard/installment/service/{{$item->id}}">
                                <button type="button" class="dropdown-item pointer">اقساط</button>
                            </a>
                            <a href="{{ route('service.before.form' , [$item->slug]) }}" class="dropdown-item pointer">فیلد های بعد پرداخت
                            </a>
                        </div>
                    </div>

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{$services->links()}}
