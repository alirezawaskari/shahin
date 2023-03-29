@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست دسترسی ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست دسترسی ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('permission.create') }}">دسترسی جدید</a>
            <a class="btn btn-warning" href="{{ route('category-permission.index') }}">دسته بندی ها</a>
        </div>
    </div>
    <div class="col-12">
        <div class="card" >
            <div class="card-header">
                دسته بندی ها
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($categories as $cat_item)
                        <div class="col-md-2">   <a href="#cat-{{$cat_item->id}}" class="text-dark text-center">
                            <div class=" border border-secondary rounded shadow-sm">
                             {{$cat_item->name}}
                            </div></a>
                        </div>

                    @endforeach
                </div>
            </div>
        </div>
        @foreach($categories as $cat)
            <div class="" style="position: relative">
                <div style="position: absolute;top: -100px" id="cat-{{$cat->id}}"></div>
            </div>
            <div class="col-12 mb-3 border border-gray p-4 card" >
                <h5 class="border-bottom pb-2 mb-4 alert alert-primary text-center">{{ $cat->name }}</h5>
                <div class="row">
                    @foreach(\App\Models\Permission::get()->where('cat_id', $cat->id) as $per)
                        <div class="col-12 col-md-2 mb-3 text-center">
                            <div class="border border-info rounded " style="padding: 5px">
                                <span class="text-dark">
                                    {{$per->name}}
                                    <hr>
                                    {{$per->display_name}}
                                  <a href="{{route('permission.edit',[$per->id])}}">
                                    <i class="fa fa-edit btn btn-small btn-outline-primary"></i>
                                </a>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
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

        @if(session()->get('permission_delete')=='success')
        toastr.success('با موفقیت حذف شد!');
        @elseif(session()->get('permission_create')=='success')
        toastr.success('با موفقیت ایجاد شد!');
        @elseif(session()->get('permission_update')=='success')
        toastr.success('با موفقیت آپدیت شد!');
        @endif

    </script>
@endsection
