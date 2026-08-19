/**
 * جزئیات سفارش کالا - address book interactions on the order detail page.
 *
 * The page renders one add modal plus one edit modal per address, so the province -> city
 * cascade cannot use the single #province_id / #city_id pair layouts.js binds. Each pair is
 * matched by class inside its own form instead.
 */
$(document).ready(function () {

    var token = $('meta[name="csrf-token"]').attr('content');

    //province -> city, scoped to the form the changed select lives in
    $('body').on('change', '.address-province', function () {
        var city = $(this).closest('form').find('.address-city');
        var provinceId = $(this).val();

        city.empty().append('<option value="">انتخاب شهر</option>');

        if (!provinceId) {
            return;
        }

        $.ajax({
            url: '/city/select',
            type: 'post',
            dataType: 'json',
            data: {
                "_token": token,
                "id": provinceId
            },
            success: function (data) {
                $.each(data.cities, function (i, d) {
                    city.append('<option value="' + d.id + '">' + d.name + '</option>');
                });
            }
        });
    });

    //deleting an address never touches an already shipped order - its address is stored on
    //the order itself - but it does clear the pending order's selection, so warn about that
    $('body').on('click', '.address-delete', function () {
        var url = $(this).data('url');

        swal({
            title: 'آیا از حذف این آدرس مطمئن هستید؟',
            text: 'آدرس سفارش های قبلی تغییری نمی کند. اگر این آدرس برای همین سفارش انتخاب شده باشد، باید دوباره آدرس ارسال را مشخص کنید.',
            icon: 'warning',
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(function (res) {
            if (!res) {
                return;
            }
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {"_token": token},
                success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    } else {
                        swal('خطا', response.message || 'خطایی رخ داده است.', 'error');
                    }
                },
                error: function () {
                    swal('خطا', 'خطایی رخ داده است.', 'error');
                }
            });
        });
    });

    //shipping closes the order, so make it a deliberate click
    $('body').on('submit', '#ship_form', function (event) {
        if ($(this).data('confirmed')) {
            return true;
        }
        event.preventDefault();
        var form = this;

        swal({
            title: 'سفارش ارسال شود؟',
            text: 'پس از ارسال، وضعیت سفارش به «ارسال شده» تغییر می کند و آدرس، هزینه ارسال و کد رهگیری دیگر قابل ویرایش نخواهند بود.',
            icon: 'warning',
            buttons: {
                confirm: 'بله، ارسال شود',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(function (res) {
            if (res) {
                $(form).data('confirmed', true);
                form.submit();
            }
        });
    });
});
