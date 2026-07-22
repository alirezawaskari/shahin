$(document).ready(function () {
    var companyCounter = 0;

    // The ajax upload widget derives sibling ids from the file input's id:
    // #<id>_path (hidden temp media id) and #<id>_src.show (preview target).
    // So a cloned row must suffix the BASE id, not the whole thing —
    // `company_logo_path` + '_1' gives `company_logo_path_1`, which the widget
    // never looks for, so the logo would be silently dropped on submit.
    function suffixId(id, n) {
        var m = /^(.*?)(_path|_src)$/.exec(id);
        return m ? m[1] + '_' + n + m[2] : id + '_' + n;
    }

    $("body").on('click', '#addCompany', function () {
        companyCounter++;
        var company = $('#company').clone();

        company.find('input').each(function () {
            var element = $(this);
            var id = element.attr('id');
            var name = element.attr('name');

            if (id) {
                element.attr('id', suffixId(id, companyCounter));
            }
            if (name) {
                // logo_company[] -> logo_company[1]; an already-indexed name (from a
                // row cloned earlier) is re-indexed rather than left duplicated.
                element.attr('name', name.replace(/\[\d*\]/, '[' + companyCounter + ']'));
            }

            if (element.is('input[type="file"]')) {
                element.val(null);
            } else {
                element.val('');
            }
        });

        // the preview container is a div, not an input — suffix it too, and reset it to
        // the empty-state placeholder so the clone doesn't show the source row's image.
        company.find('div[id]').each(function () {
            $(this).attr('id', suffixId($(this).attr('id'), companyCounter));
        });
        company.find('#company_logo_' + companyCounter + '_src')
            .html('<div class="card-img-top d-flex align-items-center justify-content-center text-muted"' +
                  ' style="height:120px;background:#f8f9fa;">بدون تصویر</div>');

        company.find('label[for]').each(function () {
            $(this).attr('for', suffixId($(this).attr('for'), companyCounter));
        });

        company.removeAttr('id'); // only the source row keeps #company
        $('#companies').append(company);
    });
});
