<select class="form-control" name="level">
    <option value="user" {{ (isset($user) && $user->level == 'user') ? 'selected' : '' }}>
        {{ App\Models\User::LEVEL['user'] }}
    </option>
    <option value="blockUser" {{ (isset($user) && $user->level == 'blockUser') ? 'selected' : '' }}>
        {{ App\Models\User::LEVEL['blockUser'] }}
    </option>
    @if(in_array(auth()->user()->level , ['admin' , 'superAdmin']) )
    <option value="admin" {{ (isset($user) && $user->level == 'admin') ? 'selected' : '' }}>
        {{ App\Models\User::LEVEL['admin'] }}
    </option>
    @endif
    <option value="ghorfeAdmin" {{ (isset($user) && $user->level == 'ghorfeAdmin') ? 'selected' : '' }}>
        {{ App\Models\User::LEVEL['ghorfeAdmin'] }}
    </option>
    @if(auth()->user()->level == 'superAdmin')
    <option value="superAdmin" {{ (isset($user) && $user->level == 'superAdmin') ? 'selected' : '' }}>
        {{ App\Models\User::LEVEL['superAdmin'] }}
    </option>
    @endif 
</select>
