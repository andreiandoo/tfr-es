// admin/js/edu-users.js
jQuery(function ($) {
  $(document).on("change", "#user_role", function () {
    const val = $(this).val();
    $(".profesor-only").toggle(val === "profesor");
    $(".general-fields").toggle(val !== "alumni");
    $(".alumni-only").toggle(val === "alumni");
  });

  // Initializează Select2 pentru câmpul școli (ajax)
  $(document).on("focus", "#school_select_ajax", function () {
    const $sel = $(this);
    if (!$sel.hasClass("select2-hidden-accessible")) {
      $sel.select2({
        ajax: {
          url: edu_ajax.ajax_url,
          dataType: "json",
          delay: 250,
          data: (params) => ({
            action: "edu_search_schools",
            nonce: edu_ajax.nonce,
            q: params.term,
          }),
          processResults: (data) => ({
            results: data.map((item) => ({
              id: item.id,
              text: item.text,
            })),
          }),
          cache: true,
        },
        placeholder: "Caută și alege școli…",
        minimumInputLength: 1,
        width: "100%",
      });
    }
  });
  // Toggle form și încărcare AJAX
  $("#toggleAddUserForm").on("click", function (e) {
    e.preventDefault();
    $("#addUserForm").toggle();
    $("#addUserFormContainer").html("Se încarcă...");
    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_get_user_form",
        nonce: edu_ajax.nonce,
      },
      function (res) {
        if (res.success) {
          $("#addUserFormContainer").html(res.data.form_html);
          // Show/hide fields based on role
          $("#user_role").trigger("change");
          // Re-init Select2 on our schools dropdown
          $("#school_select_ajax").trigger("focus");
        } else {
          $("#addUserFormContainer").html(
            '<div class="notice notice-error">Eroare la încărcarea formularului.</div>'
          );
        }
      }
    );
  });

  // Edit User — load existing user into the form
  $(document).on("click", ".edit-user", function (e) {
    e.preventDefault();
    const userId = $(this).data("user-id");
    $("#addUserForm").show();
    $("#addUserFormContainer").html("Se încarcă...");
    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_get_user_form",
        nonce: edu_ajax.nonce,
        user_id: userId,
      },
      function (res) {
        if (res.success) {
          $("#addUserFormContainer").html(res.data.form_html);
          // Show/hide fields based on role
          $("#user_role").trigger("change");
          // Re-init Select2 on our schools dropdown
          $("#school_select_ajax").trigger("focus");
        } else {
          alert("Eroare la încărcarea formularului.");
        }
      }
    );
  });

  // Cancel
  $(document).on("click", "#cancelUserForm", function (e) {
    e.preventDefault();
    $("#addUserForm").hide().find("#addUserFormContainer").empty();
  });

  // Submit AJAX Add/Edit
  $(document).on("submit", "#eduAddUserForm", function (e) {
    e.preventDefault();
    let fd = new FormData(this);
    fd.append("action", "edu_save_user_form");
    fd.append("nonce", edu_ajax.nonce);

    $.ajax({
      url: edu_ajax.ajax_url,
      method: "POST",
      data: fd,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.success) {
          const id = res.data.user_id;
          // replace or prepend row
          if ($("#user-row-" + id).length) {
            $("#user-row-" + id).replaceWith(res.data.row_html);
          } else {
            $("#eduUserTableBody").prepend(res.data.row_html);
          }
          // success notice
          $(
            "<div class='notice notice-success is-dismissible mt-2'><p>Utilizator salvat cu succes.</p></div>"
          )
            .insertBefore("#eduUserTableBody")
            .delay(3000)
            .fadeOut();
          $("#addUserForm").hide().find("#addUserFormContainer").empty();
        } else {
          alert("Eroare: " + (res.data.message || "Nu s-a putut salva."));
        }
      },
    });
  });

  // Delete AJAX
  $(document).on("click", ".delete-user", function (e) {
    e.preventDefault();
    if (!confirm("Sigur vrei să ștergi acest utilizator?")) return;
    const uid = $(this).data("user-id");
    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_delete_user",
        nonce: edu_ajax.nonce,
        user_id: uid,
      },
      function (res) {
        if (res.success) {
          $("#user-row-" + uid).remove();
        } else {
          alert("Eroare la ștergere.");
        }
      }
    );
  });

  $(document).on("click", ".send-reset-link", function (e) {
    e.preventDefault();
    const uid = $(this).data("user-id");
    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_send_reset_link",
        nonce: edu_ajax.nonce,
        user_id: uid,
      },
      function (res) {
        if (res.success) {
          $(`#reset-msg-${uid}`).show();
        } else {
          alert(
            "Eroare: " + (res.data.message || "Nu s-a putut trimite linkul.")
          );
        }
      }
    );
  });

  // Select All
  $("#check_all").on("change", function () {
    $("input[name='selected_users[]']").prop("checked", this.checked);
  });

  // Filter select2
  $("#filter_roles_select").select2({
    placeholder: "Selectează roluri",
    width: "200px",
  });

  $(document).on("click", ".view-details", function (e) {
    e.preventDefault();
    const uid = $(this).data("user-id");
    $("#userDetailsContent").text("Se încarcă…");
    $("#userDetailsModal").fadeIn();
    $.post(
      edu_ajax.ajax_url,
      {
        action: "edu_get_user_details",
        nonce: edu_ajax.nonce,
        user_id: uid,
      },
      function (res) {
        if (res.success) {
          $("#userDetailsContent").html(res.data.html);
        } else {
          $("#userDetailsContent").text("Eroare la încărcare.");
        }
      }
    );
  });

  // close modal
  $("#closeUserDetails").on("click", function () {
    $("#userDetailsModal").fadeOut();
  });
});
