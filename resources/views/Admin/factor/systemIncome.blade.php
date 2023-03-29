@extends('Admin.master')

@section('content')
        <div class="container card text-center" >
            <h1> درآمد سیستم </h1>
        </div>
        <div class="container">
            <div class="row">
                @foreach($factors as $index => $value )
                    <div class="card border border-light col-md-3">
                        <div class="card-header">
                            {{$index}}
                        </div>
                        <div class="card-body">
                            <table class="table">
                                @php
                                    $numItems=count($value);
                                    $i=0;
                                     $year_income=0;
                                @endphp
                            @foreach($value as $index => $value )
                                @php
                                        $year_income+=$value->sum('price');
                                @endphp
                                    <tr>
                                        <td>
                                            {{$index}}
                                        </td>
                                        <td>
                                            {{number_format($value->sum('price'))}}
                                        </td>
                                    </tr>
                                       @if(++$i === $numItems)
                                           <tr class="alert alert-secondary">
                                               <td>
                                                   درآمد سالانه
                                               </td>
                                               <td>
                                                   {{number_format($year_income).' ریال '}}
                                               </td>
                                           </tr>
                                       @endif

                                @endforeach
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
@stop
