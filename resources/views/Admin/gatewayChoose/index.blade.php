@extends('Admin.master')
@section('title')
    {{ $title }}
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h3>{{ $title }}</h3>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body text-end">
            <div class="row justify-content-between align-items-center px-3">
                <form method="GET" action="{{ route('gatewaysChoose.index') }}" class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                               placeholder="جستجو بر اساس نام درگاه">
                    </div>
                    <div class="col-md-6 mb-2">
                        <button type="submit" class="btn btn-primary">جستجو</button>
                        <a href="{{ route('gatewaysChoose.index') }}" class="btn btn-danger">حذف فیلتر</a>
                    </div>
                </form>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">افزودن درگاه
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام درگاه</th>
                    <th>وضعیت</th>
                    <th>ویرایش</th>
                    <th>حذف</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($gateways as $gateway)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $gateway->name }}</td>
                        <td>{{ $gateway->status_label }}</td>
                        <td>
                            <button class="btn btn-primary btn-floating"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="{{ $gateway->id }}"
                                    data-name="{{ $gateway->name }}"
                                    data-status="{{ $gateway->status }}"
                            ><i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('gatewaysChoose.destroy', $gateway->id) }}"
                                  class="d-inline" onsubmit="return confirm('آیا مطمئنی؟')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-floating"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if ($gateways->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center">درگاهی یافت نشد</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal ایجاد -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('gatewaysChoose.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">افزودن درگاه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نام درگاه</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-control">
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">ثبت</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal ویرایش -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">ویرایش درگاه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نام درگاه</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وضعیت</label>
                        <select name="status" id="edit-status" class="form-control">
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">بروزرسانی</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('js')
    <script>
        const editModal = document.getElementById('editModal')
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const status = button.getAttribute('data-status');

            const form = document.getElementById('editForm');
            form.action = `/dashboard/gatewaysChoose/${id}`; // یا route('gatewaysChoose.update', id)

            document.getElementById('edit-name').value = name;
            document.getElementById('edit-status').value = status;
        });
    </script>
@endsection
