@if($user->vip()->exists())
    <div class="badge badge-warning" style="position: absolute;top: 0px;left: 125px">
        <span id="vip-alert" >
            {{strtoupper($user->vip->type ) }}
            <i class="fa fa-bell"></i>
        </span>
    </div>
@endif
