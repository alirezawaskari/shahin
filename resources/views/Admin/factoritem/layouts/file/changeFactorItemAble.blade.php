<div class="modal fade" id="changeFactoritemableModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">تغییر خدمت پرونده</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('factorItem.changeFactoritemable') }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="form-row">
                        <div class="col-12 mb-3">
                              خدمات <input type="radio" name="cat_type{{ $factorItem['id'] }}" value="service"
                                onclick="catType(this.value , {{ $factorItem['id'] }})" checked>
                            کالا <input type="radio" name="cat_type{{ $factorItem['id'] }}" value="category"
                                onclick="catType(this.value , {{ $factorItem['id'] }})">
                          
                        </div>
                        <div class="col-12 mb-5">
                            <select name="service{{ $factorItem['id'] }}" class="form-control"
                                id="service{{ $factorItem['id'] }}">
                                @foreach (App\Models\Service::filter()->get() as $service)
                                <option value="{{ $service->id }}">{{ $service->title}}</option>
                                @endforeach
                            </select>
                            <select name="category{{ $factorItem['id'] }}" class="form-control d-none"
                                id="category{{ $factorItem['id'] }}">

                                @foreach(App\Models\Category::filter()->where('parent_id' , 1)->get() as $category)
                                <option value="{{ $category->id }}">{{ $category->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" value="{{ $factorItem['id'] }}" name="factor_item">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>