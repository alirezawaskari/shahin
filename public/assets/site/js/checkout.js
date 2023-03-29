$(document).ready(function () {

    $(function () {
        $("#installment").click(async function () {
            document.getElementById('guarantee_type').style.display = 'block';
        });
    });

    $(function () {
        $("#cash").click(async function () {
            document.getElementById('guarantee_type').style.display = 'none';
        });
    });

    $("#date").pDatepicker({
        initialValueType: "gregorian",
        format: "YYYY/MM/DD H:m:s",
        onSelect: "year",
        timePicker: {
            enabled: true,
            meridiem: {
                enabled: true
            }
        }
    });

    if ($('#cash').is(':checked')) {
        $('#guarantee_type').hide();
    }

    $('body').on('click', '#previous', function () {
        document.documentElement.scrollTop = 0;
        $('#first-data').show();
        $('#file-content').hide();
    })

    $('body').on('click', '#next', function () {
        document.documentElement.scrollTop = 0;
        $('#first-data').hide();
        $('#file-content').show();
    })
});
