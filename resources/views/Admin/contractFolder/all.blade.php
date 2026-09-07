@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>پوشه های قرارداد</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="/dashboard/contract">قرارداد ها</a></li>
                    <li class="breadcrumb-item active" aria-current="page">پوشه ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary dropdown-toggle">اقدامات</button>
            <div aria-labelledby="btnGroupDrop1" class="dropdown-menu dropdown-menu-list">
                <a href="/dashboard/contractFolder/create" class="dropdown-item"> ایجاد پوشه </a>
                <a href="/dashboard/contract" class="dropdown-item"> بانک قرارداد </a>
            </div>
        </div>
    </div>

    @if($errors->has('folder'))
        <div class="alert alert-danger">{{ $errors->first('folder') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="get" action="/dashboard/contractFolder">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" name="title" class="form-control" placeholder="عنوان پوشه"
                               value="{{ $queries['title'] ?? '' }}">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="is_active" class="custom-select" title="وضعیت">
                            <option value="all" @if(($queries['is_active'] ?? '') === 'all') selected @endif>وضعیت</option>
                            <option value="1" @if(($queries['is_active'] ?? null) === '1') selected @endif>فعال</option>
                            <option value="0" @if(($queries['is_active'] ?? null) === '0') selected @endif>غیرفعال</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عنوان</th>
                    <th>پوشه والد</th>
                    <th>تعداد قرارداد</th>
                    <th>ترتیب</th>
                    <th>وضعیت</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($folders as $folder)
                    <tr>
                        <td>{{ $folder->title }}</td>
                        <td>{{ optional($folder->parent)->title ?: '—' }}</td>
                        <td>
                            <a href="/dashboard/contract?contract_folder_id={{ $folder->id }}">
                                {{ $folder->contracts_count }}
                            </a>
                        </td>
                        <td>{{ $folder->position }}</td>
                        <td>
                            @if($folder->is_active)
                                <span class="badge badge-success">فعال</span>
                            @else
                                <span class="badge badge-secondary">غیرفعال</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="/dashboard/contractFolder/{{ $folder->id }}/edit">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                    <a href="/dashboard/contract?contract_folder_id={{ $folder->id }}">
                                        <button type="button" class="dropdown-item pointer">قراردادهای این پوشه</button>
                                    </a>
                                    <form method="post" action="{{ route('contractFolder.destroy', [$folder->id]) }}">
                                        {{ method_field('DELETE') }}
                                        {{ csrf_field() }}
                                        <button type="submit" class="dropdown-item pointer"
                                                onclick="return confirm('آیا مطمئن هستید؟ قراردادهای این پوشه حذف نمی شوند و بدون دسته بندی خواهند شد.')">حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">هنوز پوشه ای ایجاد نشده است.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {{ $folders->links() }}
        </div>
    </div>
@stop
@section('js')
@stop
