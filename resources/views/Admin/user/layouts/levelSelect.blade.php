<select class="form-control" name="level">
    <option value="user" {{ (isset($user) && $user->level == 'user') ? 'selected' : '' }}>
        کاربر فعال
    </option>
    <option value="blockUser" {{ (isset($user) && $user->level == 'blockUser') ? 'selected' : '' }}>
        کاربر غیرفعال(لیست سیاه)
    </option>
    @if(in_array(auth()->user()->level , ['admin' , 'superAdmin']) )
    <option value="admin" {{ (isset($user) && $user->level == 'admin') ? 'selected' : '' }}>
        همکار
    </option>
    @endif
    <option value="ghorfeAdmin" {{ (isset($user) && $user->level == 'ghorfeAdmin') ? 'selected' : '' }}>
        همکار غرفه
    </option>
    @if(auth()->user()->level == 'superAdmin')
    <option value="superAdmin" {{ (isset($user) && $user->level == 'superAdmin') ? 'selected' : '' }}>
        مدیرسایت
    </option>
    @endif 
</select>
