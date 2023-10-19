@if(auth()->user()->employee->is_accept_term != 1)
    <div class="alert alert-warning text-center">
        همکار گرامی جهت امکان ادامه فعالیت در سامانه لازم است فایل قوانین را دانلود و مطالعه و موافقت با قوانین را ثبت نمایید.
    </div>
@endif
