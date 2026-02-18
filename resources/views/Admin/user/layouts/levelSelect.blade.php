<select class="form-control" name="level">
    <option value="user" {{ (isset($user) && $user->level == 'user') ? 'selected' : '' }}>
        کاربر فعال
    </option>
    <option value="blockUser" {{ (isset($user) && $user->level == 'blockUser') ? 'selected' : '' }}>
        کاربر غیرفعال(لیست سیاه)
    </option>
{{ in_array(trim($product->id) , $pack->products()->pluck('products.id')->toArray()) ? 'selected' : '' }}>

    @if(in_array(auth()->user()->level , ['admin' , 'superAdmin']) )
    <option value="admin" {{ (isset($user) && $user->level == 'admin') ? 'selected' : '' }}>
        همکار
    </option>
    @endif
    <option value="ghorfeAdmin" {{ (isset($user) && $user->level == 'ghorfeAdmin') ? 'selected' : '' }}>
        همکار غرفه
    </option>
    <option value="siteAdmin" {{ (isset($user) && $user->level == 'siteAdmin') ? 'selected' : '' }}>
        مدیرسایت
    </option>
</select>
