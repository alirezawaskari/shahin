@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست آیتم ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست آیتم ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('service.before.form.item.create' , [$beforeForm->id]) }}">آیتم جدید</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>
                                <a href="{{ route('before_form_item.edit' , [$item->id]) }}"
                                   class="btn btn-primary">ویرایش
                                </a>
                            </td>

                            <td>
                                <form class="delete_form" method="post"
                                      action="{{ route('before_form_item.destroy',[$item->id]) }}">
                                    @method('DELETE')
                                    @csrf

                                    <input type="button" class="btn btn-danger delete_submit"
                                           id="{{ $item->id }}"
                                           value="حذف"/>
                                </form>
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
    <script src="/assets/dashboard/js/layouts.js"></script>
@endsection
