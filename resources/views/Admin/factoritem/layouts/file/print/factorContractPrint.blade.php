@extends('site.master')

@section('content')

<div class="col-12">
    <button class="btn btn-success py-1 px-2 text-white mt-4" onclick="pdfSection()">ذخیره به صورت PDF</button>
    <button onclick="window.print();" class="printSection">پرینت قرارداد</button>
    <div id="pdf-section" class="printSection">
        <div class="p-4 d-none border">
            <div class="section position-relative">
                <div class="overlay position-absolute"></div>
                <div class="container col-10">
                    <div class="row">
                        <div class="col-12 my-auto">
                            @if($item['type'] == 1 && $totalFactorItem)
                            <div class="card" style="background-color: #fafafa;">
                                <div class="col-12">
                                    @if(!is_null($item['centers']) || !is_null($item['forms']))
                                    <div class="row p-3 position-relative">
                                        @if(!is_null($item['forms']))
                                        @foreach($item['forms'] as $form)
                                        @if (in_array('show_factor_site' , $form->data_display_level))
                                        {{-- print view: no form, no save --}}
                                        @include('Admin.factoritem.layouts.formItem', ['formItemUploads' => false])
                                        @endif
                                        @endforeach
                                        @endif
                                    </div>
                                    @else
                                    <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                        <div>
                                            @if(!is_null($item['service_item']))
                                            <span>{{ $item['service']->title . '-' . $item['service_item']->title
                                                }}</span>
                                            @else
                                            <span>{{ $item['service']->title }}</span>
                                            @endif
                                        </div>
                                        <i class="fa fa-check font-18 text-success"></i>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="card" style="background-color: #fafafa;">
                                <div class="col-12">
                                    @if(!is_null($item['centers']) || !is_null($item['forms']))
                                    <div class="row p-3 position-relative">
                                        @if(!is_null($item['forms']))
                                        @foreach($item['forms'] as $form)
                                        @if (in_array('show_factor_site' , $form->data_display_level))
                                        {{-- print view: no form, no save --}}
                                        @include('Admin.factoritem.layouts.formItem', ['formItemUploads' => false])
                                        @endif
                                        @endforeach
                                        @endif
                                    </div>
                                    @else
                                    <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                        <div>
                                            @if(!is_null($item['service_item']))
                                            <span>{{ $item['service']->title . '-' . $item['service_item']->title
                                                }}</span>
                                            @else
                                            <span>{{ $item['service']->title }}</span>
                                            @endif
                                        </div>
                                        <i class="fa fa-check font-18 text-success"></i>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function pdfSection() {
    const element = document.getElementById('pdf-section');
    html2pdf()
        .from(element)
        .save('contract.pdf');
    } 
</script>
@stop