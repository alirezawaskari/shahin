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
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">عنوان</th>
                    <th scope="col">فایل قوانین</th>
                    <th scope="col">عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($role_categories_with_rules as $item)
                    <tr>
                        <th scope="row">1</th>
                        <td>{{$item['role_category_fa_title']??''}}</td>
                        <td>
                            <a href="{{$item['role_category_terms_file_path']}}">دانلود</a>
                            </td>
                        <td>
                            <form action="{{ route('employee.rule.store')}}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <input name="terms_file" type="file" id="rule-file" class="btn btn-outline-primary"
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

