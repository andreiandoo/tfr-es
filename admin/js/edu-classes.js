//admin/js/edu-classes.js
jQuery(function ($) {
  const schoolSelect = $("#class_school");
  const displayCity = $("#class_city_display");
  const displayCounty = $("#class_county_display");

  // Toggle Add Class form
  $("#toggleAddClassForm").on("click", function (e) {
    e.preventDefault();
    $("#classFormContainer").toggle();
  });

  // Cancel button hides form
  $(document).on("click", "#cancelClassForm", function (e) {
    e.preventDefault();
    $("#classFormContainer").hide();
  });

  function initSchools(search = "") {
    schoolSelect.html("<option>Se încarcă…</option>");
    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_search_schools",
        nonce: edu_ajax.nonce,
        q: search,
      },
      function (data) {
        schoolSelect
          .empty()
          .append('<option value="">Selectează școala…</option>');

        data.forEach((s) => {
          // s.text already = "cod – name – city – county"
          $("<option>")
            .val(s.id)
            .text(s.text)
            // store city/county on the <option> for your display below
            .attr("data-city", s.city)
            .attr("data-county", s.county)
            .appendTo(schoolSelect);
        });

        // re-select & fire change if editing
        const sel = schoolSelect.data("selected");
        if (sel) schoolSelect.val(sel).trigger("change");
      }
    );
  }

  // initial load
  initSchools();

  // Select2 AJAX search
  if ($.fn.select2) {
    schoolSelect.select2({
      ajax: {
        url: edu_ajax.ajax_url,
        dataType: "json",
        delay: 300,
        data: (params) => ({
          action: "edu_search_schools",
          nonce: edu_ajax.nonce,
          q: params.term,
        }),
        processResults: (data) => ({ results: data }),
        cache: true,
      },
      placeholder: "Caută școală…",
      minimumInputLength: 2,
      width: "100%",
    });
  }

  // update city/county below the select
  schoolSelect.on("change", function () {
    const opt = $(this).find("option:selected");
    displayCity.html("Localitate: <b>" + (opt.data("city") || "-") + "</b>");
    displayCounty.html("Județ: <b>" + (opt.data("county") || "-") + "</b>");
  });
});

jQuery(function ($) {
  // — Initialize Select2 on the professor dropdown —
  const $teacher = $("#class_teacher");
  const $classLvl = $("#class_level");

  if ($teacher.length && $.fn.select2) {
    $teacher.select2({
      ajax: {
        url: edu_ajax.ajax_url,
        dataType: "json",
        delay: 250,
        data: (params) => ({
          action: "edu_search_teachers",
          nonce: edu_ajax.nonce,
          q: params.term,
        }),
        processResults: (data) => ({ results: data }),
        cache: true,
      },
      placeholder: "Caută profesor…",
      minimumInputLength: 2,
      width: "100%",
    });

    // If editing, keep the selected value visible
    const initial = $teacher.data("selected");
    if (initial) {
      // trigger a dummy AJAX query so Select2 will show it
      $.ajax({
        url: edu_ajax.ajax_url,
        method: "POST",
        data: {
          action: "edu_search_teachers",
          nonce: edu_ajax.nonce,
          q: "", // empty to fetch first page
        },
        success(res) {
          // nothing to do—Select2 will render the existing <option selected>
        },
      });
    }
  }

  // — When a professor is chosen, fetch their nivel_predare —
  $teacher.on("change", function () {
    const tid = $(this).val();
    if (!tid) {
      // clear if none
      $classLvl.empty().append('<option value="">Selectează nivel…</option>');
      return;
    }

    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_get_teacher_meta",
        nonce: edu_ajax.nonce,
        teacher_id: tid,
      },
      function (res) {
        if (!res.success) return;
        let nivelStr = res.data.nivel || "";
        // split on comma or ampersand
        let parts = nivelStr.split(/\s*(?:,|&)\s*/).filter(Boolean);

        // rebuild the Nivel dropdown
        $classLvl.empty();
        parts.forEach((val, idx) => {
          $classLvl.append(
            $("<option>")
              .val(val)
              .text(val)
              .prop("selected", idx === 0)
          );
        });
      }
    );
  });

  // trigger on load if editing
  $teacher.trigger("change");
});
