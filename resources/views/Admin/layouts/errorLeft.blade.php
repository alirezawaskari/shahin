<script>
    @if(session()->get('delete') == 'success')
    toastr.success('با موفقیت حذف شد!');
    @elseif(session()->get('create') == 'success')
    toastr.success('با موفقیت ایجاد شد!');
    @elseif(session()->get('update') == 'success')
    toastr.success('با موفقیت بروزرسانی شد!');

    @elseif(session()->get('failByMessage'))
    toastr.error('{{session()->get('failByMessage')}}');

    @elseif(session()->get('successByMessage'))
    toastr.success('{{session()->get('successByMessage')}}');

    @elseif(session()->get('update') == 'warning')
    toastr.error('خطا در اطلاعات ارسالی');

    @endif
</script>
