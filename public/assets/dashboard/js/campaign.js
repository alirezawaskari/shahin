$(document).ready(function () {
    var companyCounter = 0;
    $("body").on('click', '#addCompany', function () {
        companyCounter++;
        var company = $('#company').clone();
        company.find('input').each(function () {
            var element = $(this);
            var id = element.attr('id');
            var name = element.attr('name');
            var newId = id + '_' + companyCounter;
            var newName = name.replace('[]', '[' + companyCounter + ']');
            element.attr('id', newId);
            element.attr('name', newName);
            if (element.is('input[type="file"]')) {
                element.val(null);
            } else {
                element.val('');
            }
        });
        company.find('label[for]').each(function () {
            var forAttr = $(this).attr('for');
            var newForAttr = forAttr + '_' + companyCounter;
            $(this).attr('for', newForAttr);
        });
        $('#companies').append(company);
    });
});
