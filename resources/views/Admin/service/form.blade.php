@extends('Admin.master')
@section('content')
<div class="page-header">
    <div>
        <h3>لیست فیلد ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">لیست فیلد ها</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('service.before.form.create' , [ $service->slug]) }}"
            class="btn btn-primary">ایجاد فیلد جدید
        </a>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>name</th>
                    <th>placeholder</th>
                    <th>type</th>
                    <th>help</th>
                    <th>محل ذخیره فیلد</th>
                    <th>محل نمایش فیلد</th>
                    <th>دارای آیتم </th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>

                @foreach($forms as $form)
                <tr>
                    <td>{{ $form->name }}</td>
                    <td>{{ $form->placeholder }}</td>
                    <td>{{ \App\Models\BeforeForm::TYPE[$form->type]}}</td>
                    <td> {!! $form->help !!}</td>
                    <td>{{ $form->data_save_level }}</td>
                    <td>
{{--                        {{is_array($form->data_display_level)? implode( ' | ',$form->data_display_level) :''}}--}}
                        @if(is_array($form->data_display_level))
                        @foreach($form->data_display_level as $item)
                            {!! '('.\App\Models\BeforeForm::DATA_DISPLAY_LEVEL[$item].' | '.$item.')'.'<br>' !!}
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if($form->beforeformitems()->exists())
                            <i class="fa fa-check text-success"></i>
                        @else
                            <i class="fa fa-close text-warning"></i>
                        @endif
                    </td>
                    <td>
                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('before_form.edit' , [ $form->id]) }}"><button type="button" class="dropdown-item pointer  ">مشاهده</button></a>
                                <a href="{{ route('service.before.form.item' , [ $form->id]) }}"><button type="button" class="dropdown-item pointer "> آیتم ها</button></a>
                                <form class="delete_form" method="post"
                                action="{{ route('before_form.destroy',[$form->id]) }}">
                                @method('DELETE')
                                @csrf

                                <input type="button" class="dropdown-item pointer delete_submit "
                                    id="{{ $form->id }}" value="حذف" />
                            </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @include('Admin.layouts.pagination')
    </div>
</div>
@endsection

@section('js')
<script>
    $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            }
        });

        @if(session()->get('before_form_delete')=='success')
        $.Notification.autoHideNotify('custom', 'bottom left', 'حذف شد', 'با موفقیت حذف شد!');
        @elseif(session()->get('before_form_create')=='success')
        $.Notification.autoHideNotify('custom', 'bottom left', 'ایجاد شد', 'با موفقیت ایجاد شد!');
        @elseif(session()->get('before_form_update')=='success')
        $.Notification.autoHideNotify('custom', 'bottom left', 'آپدیت شد', 'با موفقیت آپدیت شد!');
        @endif
</script>
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection
