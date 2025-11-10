<form id="sendLinkForm" action="{{ route('user.sellingAccount') }}" method="POST" class="my-4">
    @csrf
    <div class="row text-dark">
        <div class="col-12">
            <div class="card">
                <div class="card-body searchUser row">
                    <div class="col-12">
                        <p class="text-danger text-center error_user"></p>
                    </div>
                    <div class="col-12 mb-3 mt-3">
                        <label>شماره تماس : </label>
                        <input class="phone form-control my-2" type="text" name="phone" required
                            placeholder="شماره تماس خود را وارد کنید" value="{{ old('phone') }}"
                            oninput="searchUser(this.value)" id="phone">
                        @if ($errors->has('phone'))
                        <span class="d-block text-red text-left">{{
                            $errors->first('phone') }}</span>
                        @endif
                    </div>

                    <div class="col-6 mb-3 mt-3">
                        <label>نام : </label>
                        <input class="form-control my-2 name" id="name" disabled>
                    </div>

                    <div class="col-6 mb-3 mt-3">
                        <label>نام خانوادگی : </label>
                        <input class="form-control my-2 family" id="family" disabled>
                    </div>
                </div>
                <div class="loading_send_link position-absolute ajax-loading align-items-center flex-column pt-5 d-none text-center"
                    id="loading_send_link">
                    <div role="status" class="spinner-border text-primary">
                    </div>
                    <br>
                    <span>در حال جستجو کاربر...</span>
                </div>
                <div class="col-12 mb-3">
                    <input type="submit" value="ثبت" class="btn btn-success btn-block text-white font-14">
                </div>
            </div>
        </div>
    </div>

</form>
<script>
    function searchUser(search) {
    if (search.length === 11) {
        $('.loading_send_link').css('display', 'block');
        $.ajax({
            type: "GET",
            url: "/dashboard/callcenter/fetchUser",
            data: {
                phone: search,
            }
        }).done(function (data) {
            $(".name").attr('value', data.data.user.name ? data.data.user.name : '');
            $(".family").attr('value', data.data.user.family ? data.data.user.family : '');
            $('.loading_send_link').css('display', 'none');
            if (!data.data.user.name) {
                $('.error_user').text('کاربری با این شماره تماس دارای عضویت نمی باشد .');
            }
        });
    }
}
</script>