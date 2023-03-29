@if( \App\Models\Product::showErrorProduct($field,$product_validation) != null)
    <div class="alert alert-warning">
        <a>{{ \App\Models\Product::showErrorProduct($field,$product_validation) }}</a>
    </div>
@endif