@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>قوانین پرسنل</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">قوانین پرسنل</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
                <table class="table table-responsive">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">عنوان</th>
                        <th scope="col">فایل قوانین</th>
                        <th scope="col">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($i=1)
                    @foreach($role_categories_with_terms as $item)
                        <tr>
                            <th scope="row">{{$i++}}</th>
                            <td>{{$item['role_category_fa_title']??''}}</td>
                            <td>
                               @if($item['role_category_terms_file_path']!='')
                                    <a href="{{$item['role_category_terms_file_path']}}" target="_blank" rel="noopener">
                                        <div class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-download" style="margin-left: 2px"></i>
                                            <span> دانلود</span>
                                        </div>
                                    </a>
                                    @if($item['is_legacy'])
                                    <small class="d-block text-warning mt-1">فایل قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                    @endif
                                @endif
                            </td>

                            <td>
                                {{-- ajax -> temp upload -> hidden url_terms_file (temp media id);
                                     EmployeeTermsController@store attachTo()s it into the EmployeeTerm's
                                     Spatie `terms_file` collection. Unique ids per row (the old shared
                                     id="terms-file" broke the label link). --}}
                                <form action="{{ route('employee.terms.store')}}" method="POST">
                                    @csrf
                                    <label for="terms-file-{{ $loop->index }}"
                                           class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب فایل
                                    </label>
                                    <input type="file" id="terms-file-{{ $loop->index }}" name="terms_file"
                                           class="filestyle file-upload file_upload_ajax file-up"
                                           data-buttonname="btn-white" accept=".pdf">
                                    <div id="terms-file-{{ $loop->index }}_src" class="show"></div>
                                    <input type="hidden" id="terms-file-{{ $loop->index }}_path" class="path"
                                           name="url_terms_file" value="">
                                    <input type="hidden" value="{{$item['role_category_title']}}" name="role_category">
                                    <button type="submit" class="btn btn-success mt-1">ثبت</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
    </div>
@stop

