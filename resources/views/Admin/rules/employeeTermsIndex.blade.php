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
                                    <a href=" {{$item['role_category_terms_file_path']}}">
                                        <div class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-download" style="margin-left: 2px"></i>
                                            <span > دانلود</span>
                                        </div>
                                    </a>

                                @endif
                            </td>

                            <td>
                                <form action="{{ route('employee.terms.store')}}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <input name="terms_file" type="file" id="terms-file" class="btn btn-outline-primary"
                                           accept=".pdf">
                                    <input type="hidden" value="{{$item['role_category_title']}}" name="role_category">
                                    <button type="submit" class="btn btn-primary">ثبت</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
    </div>
@stop

