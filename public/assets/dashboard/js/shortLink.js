var clipboard = new ClipboardJS('.btn-copy');
clipboard.on('success', function (e) {
    alert('آدرس کوتاه کپی شد: ' + e.text);
    e.preventDefault()
});
$(document).ready(function () {
    $('#short-link-form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $('#short-link-form').attr('action'),
            data: $('#short-link-form').serialize(),
            success: function (data) {
                const new_created_link = `<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title mb-4">نتیجه</h4>
          <hr>
          <div class="form-group mb-4">
            <label for="original-link">لینک اصلی:</label>
            <input type="text" class="form-control" id="original-link" value="${data.original_link}" readonly>
          </div>
          <div class="form-group mb-4">
            <label for="short-link">لینک کوتاه شده:</label>
            <div class="input-group">
              <input type="text" class="form-control" id="new-created-short-link" value="${data.short_link}" readonly>
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" id="do-copy" type="button" onclick="copyToClipboard()">کپی کردن</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
`
                $('#new_short_link').empty();
                $('#new_short_link').append(new_created_link);
                $('#do-copy').click();
                swal({
                    text: "لینک کوتاه ایجاد و کپی گردید .",
                    icon: "success",
                    button: "تایید",
                    allowOutsideClick: true
                });

            },
            error: function (data) {
                swal({
                    title: "خطا ",
                    text: "خطایی رخ داده !",
                    icon: "error",
                    button: "باشه",
                });
            }
        });
    });

    //all section
    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel').prop('checked', false);
                //fetch content and render here
                if (!first_load && !do_filter_command){
                    fetchData(page);
                }
                first_load=false
                do_filter_command=false
            }
        });
    }


    /*    click on do filter */
    $("body").on('click','#do_filter',function (event) {

        $('#pagination-demo').remove();
        $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
        do_filter_command=true;
        fetchData(1)
        event.preventDefault();
    });


    //all section
    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel').prop('checked', false);
                //fetch content and render here
                if (!first_load && !do_filter_command){
                    fetchData(page);
                }
                first_load=false
                do_filter_command=false
            }
        });
    }
    //fetch data by filter
    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();
        $.ajax({
            url: "/dashboard/short-link/filter"+"/?page="+page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });

    }
    //crate table after fetch data
    function createTable(data) {
        pagination(data.meta.last_page);
        $('#datatable-json').DataTable().destroy();
        document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        $('#datatable-json tbody').empty();
        var table = $('#datatable-json').DataTable({
            paging: false,
            searching: false,
            retrieve: true,
            data: data.data,
            order:[[0,"desc"]],
            columns: [
                { "data": "sort_by" },
                { "data": "original_link" },
                { "data": "short_link" },
                { "data": "created_at" },
                {
                    "data": data,
                    "render": function (data){
                        return `<button class="btn btn-primary btn-copy"
                                    data-clipboard-text="${data.short_link}">کپی
                            </button>`
                    }
                }
            ],
        });
        //add column count
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = data.meta.from+i;
            } );
        } ).draw();
    }

    /*    click on do filter */
    $("body").on('click','#do_filter',function (event) {
        $('#pagination-demo').remove();
        $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
        do_filter_command=true;
        fetchData(1)
        event.preventDefault();
    });

});
// copy link in clipboard in table section
function copyToClipboard() {
    var text = $('#new-created-short-link').val();
    navigator.clipboard.writeText(text)
    // .then(function () {
    //     alert('لینک با موفقیت کپی شد!');
    // })
    // .catch(function () {
    //     alert('خطایی در کپی متن رخ داده است.');
    // });
}


