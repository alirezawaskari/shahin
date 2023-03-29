
<div id="packProducts{{$product_id}}" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div id="{{$product_id}}" class="modal-content content1">
            <div class="modal-header">
                <h4 class="modal-title">محصولات پک</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body modal-lg">
                <div class="row">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>محصول</th></tr></thead>
                        <tbody>
                        @if($products)
                            @foreach($products as $item)
                                <tr>
                                    <td>{{$item->name}}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                {{--<button type="button" class="btn btn-success">بستن</button>--}}
            </div>
        </div>

    </div>
</div>
