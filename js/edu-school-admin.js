jQuery(document).ready(function ($) {
    $('#edu_county').on('change', function () {
        var countyId = $(this).val();

        $('#edu_city').html('<option value="">Loading...</option>');

        $.post(edu_ajax.ajax_url, {
            action: 'edu_get_cities',
            county_id: countyId,
            nonce: edu_ajax.nonce
        }, function (response) {
            $('#edu_city').html(response);
        });
    });
});
