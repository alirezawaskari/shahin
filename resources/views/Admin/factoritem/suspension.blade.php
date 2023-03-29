@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>میزکار</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">میزکار</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                اقدامات
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#service-modal">ثبت کاربر جدید</a>
            </div>
        </div>
    </div>
    <factoritem-suspension-data></factoritem-suspension-data>
@stop
