<br/><br/>

<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h5>لیست کالا ها</h5>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="card-box table-responsive">
                <table id="datatable-keytable" class="table table-striped">
                    <thead>
                    <tr>
                        <th></th>
                        <th>عنوان</th>
                        <th>گروه</th>
                        <th>تعداد تنوع فعال</th>
                        <th> کمترین قیمت روی سایت (ریال)</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $item)
                        <tr>
                            <td><img src="{{$item->image}}" class="border-radius5" alt="{{$item->title}}" width="80px"></td>
                            <td>{{$item->name}}</td>
                            <td>{{\App\Models\Category::findCategory($item->root_category_id,'title')}}</td>
                            <td>{{\App\Models\ProductAttribute::productAttributeActive($item->id)}}</td>
                            <td>{{number_format(\App\Models\ProductAttribute::minPriceProductAttributeActive($item->id))}}</td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                        data-target="#transferProductSeller{{$item->id}}">شما هم بفروشید
                                </button>
                                @include('Admin.modal.transferProductSeller',['product_id'=>$item->id])

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
