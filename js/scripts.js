jQuery(document).ready(function ($) {

    $('#magicsuggest').magicSuggest({
        data: ajaxurl,
        dataUrlParams: {action: 'getpostpages'},
        ajaxConfig: {
            xhrFields: {
                withCredentials: true,
            }
        }
    });



    $(document).tooltip({
        content: function () {
            var element = $(this);
            if (element.is("[widthhelp]")) {
                return 'Width is only for inline annotation. Please add at least 120px. If you don\'t know width just leave this field blank. ';
            }
        }
    });

    $('[name="width"],[name="position"],[name="size"],[name="annotation"]').change(function () {
        generateGP();
    });
    jQuery("[data-toggle='switch']").bootstrapSwitch();

    if ($('[data-toggle="select"]').length) {
        $('[data-toggle="select"]').select2();
    }

    $('[data-toggle="checkbox"]').radiocheck();
    $('[data-toggle="radio"]').radiocheck();

    jQuery('[name="status"]').on('switchChange.bootstrapSwitch', function (event, state) {
        $.post(ajaxurl, {action: "clgpactive", status: state}, function (data) {
            if (data.status === "1") {
                jQuery('#clgp_circ').css("background", "#0f0");
            } else {
                jQuery('#clgp_circ').css("background", "#f00");
            }
        }, "json");
    });
    
    $(function () {
        $('[data-toggle=tooltip]').tooltip();
      });
    generateGP();
});

function generateGP() {
    var width = parseInt(jQuery('[name="width"]').val()) > 0 ? parseInt(jQuery('[name="width"]').val()) : '';
    var code = '<div style="width:100%; text-align:' + jQuery('[name="position"]:checked').val() + ';"><div class="g-plusone" data-size="' + jQuery('[name="size"]:checked').val() + '" data-annotation="' + jQuery('[name="annotation"]').val() + '" data-width="' + width + '"></div></div>';
    jQuery('.previewgp').html(code);
    if (typeof gapi !== 'undefined'){
        gapi.plusone.go("previewgp");
    }
}