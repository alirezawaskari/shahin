@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست خزانه</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست خزانه</li>
            </ol>
        </nav>
    </div>
    @include('Admin.safebox.layouts')
</div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام</th>
                        <th>نمایندگی</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($safe_boxes as $key => $safe_box)
                        <tr class="active" id="item_{{$store_room->id}}" style="cursor: move;">
                            <td>{{ $safe_box->code }}</td>
                            <td>{{ $safe_box->name }}</td>
                            <td>{{ $safe_box->representation ? $safe_box->representation()->withTrashed()->first()->fa_name : 'م تهران' }}</td>
                            <td>{{ $safe_box->address }}</td>
                            <td>
                                <a href="{{ route('storeRoom.edit' , [$safe_box->id]) }}"
                                   class="btn btn-primary">ویرایش
                                </a>
                            </td>
                            <td>
                                <a href="{{ url('dashboard/create-product-storeRoom' , [ $safe_box->id]) }}"
                                   class="btn btn-primary">کالا ها
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(".article_submit").each(function () {
            $(this).on("click", function () {
                var id = $(this).attr("id");
                submit(id);

                function submit() {
                    swal({
                        title: "آیا مطمئن هستید؟",
                        text: "",
                        icon: "warning",
                        buttons: {
                            confirm: 'بله',
                            cancel: 'خیر'
                        },
                        dangerMode: true
                    }).then(res => {
                        if (res) {
                            $('input[id="' + id + '"]').parent(".article_form").submit();
                        }
                    });
                }
            });
        });
    </script>
@endsection
