jQuery(document).ready(function ($) {
  // FORM ȘCOLI — orașe și sate/comune
  $('#edu_county').on('change', function () {
    const countyId = $(this).val();

    $('#edu_city').html('<option value="">Se încarcă...</option>');
    $('#edu_village').html('<option value="">Selectează comună/sat (dacă este cazul)</option>');

    $.post(edu_ajax.ajax_url, {
      action: 'edu_get_cities',
      nonce: edu_ajax.nonce,
      county_id: countyId
    }, function (response) {
      $('#edu_city').html(response);
    });

    // dropdown localitate părinte (în cities.php)
    if ($('#parent_city_id').length > 0) {
      $('#parent_city_id').html('<option value="">Se încarcă...</option>');
      $.post(edu_ajax.ajax_url, {
        action: 'edu_get_parent_cities',
        nonce: edu_ajax.nonce,
        county_id: countyId
      }, function (response) {
        $('#parent_city_id').html(response);
      });
    }
  });

  $('#edu_city').on('change', function () {
    const cityId = $(this).val();
    $('#edu_village').html('<option value="">Se încarcă...</option>');

    $.post(edu_ajax.ajax_url, {
      action: 'edu_get_villages',
      nonce: edu_ajax.nonce,
      city_id: cityId
    }, function (response) {
      $('#edu_village').html(response);
    });
  });
});
