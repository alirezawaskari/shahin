<div class="panel">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a href="#checks" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"> چک ها و سفته ها</a>
        </h5>
    </div>
    <div id="checks" class="panel-collapse collapse in">
        <a href="{{route('totalFactorItem.checks',[$item['id']])}}">
                  <span class="btn btn-outline-info">
                      <h4>
                          <i class="fa fa-hand-pointer-o"></i>
                        مدیریت چک های پرونده
                    </h4>
                  </span>
        </a>
        @include('Admin.totalfactoritem.layouts.checksList')
    </div>
    <div id="checks" class="panel-collapse collapse in">
        <a href="{{route('totalFactorItem.promissories',[$item['id']])}}">
                  <span class="btn btn-outline-info">
                      <h4>
                          <i class="fa fa-hand-pointer-o"></i>
                        مدیریت سفته های پرونده
                    </h4>
                  </span>
        </a>
        @include('Admin.totalfactoritem.layouts.promissoriesList')
    </div>


</div>
