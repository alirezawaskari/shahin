<br/><br/>

<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
        </div>
        <div class="x_content">
            <div class="card-box table-responsive scrollBox">
                <table id="datatable-keytable" class="table table-striped">
                    <thead>
                    <tr>
                        <th>عکس</th>
                        <th>عنوان</th>
                        <th>گروه</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $item)
                        <tr>
                            <td>
                                @if($item->image)
                                    <img src="{{$item->image}}" class="border-radius5" alt="{{$item->title}}" width="80px">
                                @endif
                            </td>
                            <td><a id="{{$item->id}}" class="productName">{{$item->name}}</a></td>
                            <td>{{\App\Models\Category::findCategory($item->root_category_id,'title')}}</td>
                            <td>
                                <button data-title="{{$item->name}}" id="{{$item->id}}" type="button" class="btn btn-success btn-sm chooseProduct">انتخاب</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
