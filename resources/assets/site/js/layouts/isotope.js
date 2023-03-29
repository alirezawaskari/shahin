$(document).ready(function () {
    // external js: isotope.pkgd.js
// init Isotope
    var $grid = $('.grid').isotope({
        itemSelector: '.element-item',
        masonry: {
            // use outer width of grid-sizer for columnWidth
            columnWidth: 100,
            horizontalOrder: true
        }

    });

// filter functions
    var filterFns = {
        // show if number is greater than 50
        numberGreaterThan50: function () {
            var number = $(this).find('.number').text();
            return parseInt(number, 10) > 50;
        },
        // show if name ends with -ium
        ium: function () {
            var name = $(this).find('.name').text();
            return name.match(/ium$/);
        }
    };
// bind filter button click
    $('.button-group').on('click', 'li', function () {
        var filterValue = $(this).attr('data-filter');
        // use filterFn if matches value
        filterValue = filterFns[filterValue] || filterValue;
        $grid.isotope({filter: filterValue});
    });
// change is-checked class on buttons
    $('.button-group').each(function (i, buttonGroup) {
        var $buttonGroup = $(buttonGroup);
        $buttonGroup.on('click', 'li', function () {
            $buttonGroup.find('.active').removeClass('active');
            $(this).addClass('active');
        });
    });


    var page = 1;

    $('.ajax-load').click(function () {
        page++;
        loadMoreData(page);
    });


    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $("meta[name='token']").attr("content")
        }
    });

    function loadMoreData(page) {
        $.ajax(
            {
                url: '?page=' + page,
                type: "get",
                beforeSend: function () {
                    $('.ajax-load').show();
                }
            })
            .done(function (data) {
                if (data.html == "") {
                    $('.ajax-load').hide();
                }
                $grid.prepend( data.html)
                    .isotope( 'reloadItems' ).isotope({ sortBy: 'original-order' });
            })

            .fail(function (jqXHR, ajaxOptions, thrownError) {
                console.log('server not responding...');
            });
    }
});