@foreach($galleries as $gallery)
    <figure class="element-item px-2 gallery{{ $gallery->cate_id }} my-2"
            style="width: 266px; position: absolute; left: 15px; top: 0px;"
            data-src="{{ $gallery->image }}"
            data-sub-html="<h6>{{ $gallery->title }}</h6>"
            data-pinterest-text="" data-tweet-text="">
        <a href="">
            <img class="img-responsive img-fluid rounded"
                 src="{{ $gallery->image }}">
        </a>
    </figure>
@endforeach