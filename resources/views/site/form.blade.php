@extends('site.master')

@section('title')
    تکمیل پرداخت
@endsection

@section('content')

    <div class="login">
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5">
                    <div class="row">
                        <div class="col-12 mx-auto d-flex justify-content-center flex-column">
                            <h4 class="text-orange my-3">{{ $factor->service()->withTrashed()->first()->title }}</h4>
                            <p class="text-black-50 font-16">لطفا جهت نهایی کردن روند پرداخت و استفاده از خدمات اطلاعات زیر
                                را وارد فرمایید </p>

                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger my-3" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach
                            <form action="{{ route('form.store') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                @foreach($factor->service()->withTrashed()->first()->after_fields as $form)
                                    @switch($form['type'])
                                        @case('number')
                                        <p class="my-1 font-14 text-black-50">{{ array_key_exists('title' , $form) ? $form['title'] : ''}}</p>
                                        <input type="number" class="{{ $form['className'] }} my-2" {{ array_key_exists('required' , $form) ? 'required' : '' }} placeholder="{{ array_key_exists('placeholder' , $form) ? $form['placeholder'] : ''}}" name="{{ $form['name'] }}" value="{{ array_key_exists('value' , $form) ? $form['value'] : '' }}">
                                        @break
                                        @case('select')
                                        <p class="my-1 font-14 text-black-50">{{ array_key_exists('title' , $form) ? $form['title'] : ''}}</p>
                                        <select name="{{ $form['name']}}" class="{{ $form['className'] }} my-2" {{ array_key_exists('required' , $form) ? 'required' : '' }}>
                                            <option value="">{{ array_key_exists('placeholder' , $form) ? $form['placeholder'] : ''}}</option>
                                            @foreach($form['values'] as $item)
                                                <option value="{{ $item['label'] }}">{{ $item['value'] }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                        @case('text')
                                        <p class="my-1 font-14 text-black-50">{{ array_key_exists('title' , $form) ? $form['title'] : ''}}</p>
                                        @if($form['name'] == 'date')
                                            <input type="text" class="{{ $form['className'] }} my-2" {{ array_key_exists('required' , $form) ? 'required' : '' }} name="{{ $form['name'] }}" id="{{ $form['name'] }}"
                                            value="{{ !is_null($values) ? $values[$form['name']]['value'] : '' }}">
                                        @else
                                            <input type="text" class="{{ $form['className'] }} my-2" {{ array_key_exists('required' , $form) ? 'required' : '' }} placeholder="{{ array_key_exists('placeholder' , $form) ? $form['placeholder'] : ''}}" name="{{ $form['name'] }}" value="{{ array_key_exists('value' , $form) ? $form['value'] : '' }}">
                                        @endif

                                        @break
                                        @case('textarea')
                                        <p class="my-1 font-14 text-black-50">{{ array_key_exists('title' , $form) ? $form['title'] : ''}}</p>
                                        <textarea class="{{ $form['className'] }} my-2" {{ array_key_exists('required' , $form) ? 'required' : '' }} placeholder="{{ array_key_exists('placeholder' , $form) ? $form['placeholder'] : ''}}" name="{{ $form['name'] }}">{{ array_key_exists('value' , $form) ? $form['value'] : '' }}</textarea>
                                        @break
                                        @case('file')
                                        <p class="my-1 font-14 text-black-50">{{ array_key_exists('title' , $form) ? $form['title'] : ''}}</p>
                                        <input type="file" class="{{ $form['className'] }}" name="{{ $form['name'] }}" {{ array_key_exists('required' , $form) ? 'required' : '' }}>
                                        @break
                                        @default
                                    @endswitch
                                @endforeach
                                <input type="hidden" name="factor_id" value="{{ $factor->id }}">
                                <input type="submit" class="btn btn-orange px-4 py-1 my-3 text-white" value="ثبت اطلاعات">
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('footer')
    <script>
        $('#date').persianDatepicker({
            format: 'YYYY/MM/D',
            autoClose: true
        });
    </script>
@endsection

