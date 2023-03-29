@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست مدارک قرارداد</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست مدارک قرارداد</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('contract-form-create')
            <a href="{{ route('contractForm.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فیلد جدید</span>
            </a>
            @endpermission
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>نوع</th>
                        <th>توضیح فیلد</th>
                        <th>نوع ضمانت</th>
                        <th>دسته بندی</th>
                        <th>وضعیت آیتم</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contract_forms as $key => $contract_form)
                        <tr class="active" id="item_{{$contract_form->id}}" style="cursor: move;">
                            <td>{{ $contract_form->name }}</td>
                            <td>{{ $contract_form::TYPE[$contract_form->type] }}</td>
                            <td>{{ $contract_form->help }}</td>
                            <td>{{ $contract_form::VALUE[$contract_form->value] }}</td>
                            <td>{{ $contract_form::LEVEL[$contract_form->level] }}</td>
                            <td>
                                @if($contract_form->beforeformitems()->exists())
                                <i class="fa fa-check text-success"></i>
                                @else
                                    <i class="fa fa-close text-warning"></i>
                                @endif
                            </td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                       class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a>
                                            <button  class="factor-show dropdown-item pointer">
                                                <a class="text-dark" href="{{ route('contractForm.edit' , [$contract_form->id]) }}"
                                                    >ویرایش
                                                </a>
                                            </button>
                                            <button  class="factor-show dropdown-item pointer">
                                                <a class="text-dark" href="{{route('contract.formItems',[$contract_form->id])}}"
                                                    >آیتم ها
                                                </a>
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('Admin.layouts.pagination')
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/pagination.js"></script>
@endsection
