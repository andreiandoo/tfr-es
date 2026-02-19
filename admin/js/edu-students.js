jQuery(function ($) {
  let studentIndex = 0;

  function addStudentRow(idx) {
    // determine class level from selected <option>
    const level = $("#student_class option:selected").data("level") || "";

    const row = $(`
      <tr data-idx="${idx}">
        <td><input type="text"  name="students[${idx}][first_name]" class="widefat" required></td>
        <td><input type="text"  name="students[${idx}][last_name]"  class="widefat" required></td>
        <td><input type="number" name="students[${idx}][age]"        class="widefat" min="1" required></td>
        <td>
          <select name="students[${idx}][gender]" class="widefat" required>
            <option value=""></option>
            <option value="M">M</option>
            <option value="F">F</option>
          </select>
        </td>
        <td>
          <select name="students[${idx}][observation]" class="widefat obs-select">
            <option value=""></option>
            <option value="transfer">Transfer</option>
            <option value="abandon">Abandon</option>
          </select>
        </td>
        <td>
          <select name="students[${idx}][sit_abs]" class="widefat">
            <option value=""></option>
            <option value="Deloc">Deloc</option>
            <option value="Uneori/Rar">Uneori/Rar</option>
            <option value="Des">Des</option>
            <option value="Foarte Des">Foarte Des</option>
          </select>
        </td>
        <td class="frecventa_col">
          <select name="students[${idx}][frecventa]" class="widefat">
            <option value=""></option>
            <option value="Nu">Nu</option>
            <option value="Da (1an)">Da (1an)</option>
            <option value="Da (2ani)">Da (2ani)</option>
            <option value="Da (3ani)">Da (3ani)</option>
          </select>
        </td>
        <td class="bursa_col">
          <select name="students[${idx}][bursa]" class="widefat">
            <option value=""></option>
            <option value="Nu">Nu</option>
            <option value="Da">Da</option>
          </select>
        </td>
        <td>
          <select name="students[${idx}][dif_limba]" class="widefat">
            <option value=""></option>
            <option value="Nu">Nu</option>
            <option value="Da">Da</option>
          </select>
        </td>
        <td class="notes-cell">
          <textarea name="students[${idx}][notes]" class="widefat" rows="2" style="display:none;"></textarea>
        </td>
        <td>
          <button type="button" class="remove-student-row button button-secondary">
            Șterge
          </button>
        </td>
      </tr>
    `);

    // hide/show the conditional columns based on class level
    if (!["Prescolar", "Primar Mic", "Primar Mare"].includes(level)) {
      row.find(".frecventa_col").hide();
    }
    if (!["Primar Mic", "Primar Mare", "Gimnazial"].includes(level)) {
      row.find(".bursa_col").hide();
    }

    $("#studentsContainer").append(row);
  }

  // 1) disable controls until class chosen
  $("#studentControls, #studentsTable").hide();

  $("#student_class").on("change", function () {
    if ($(this).val()) {
      $("#studentControls, #studentsTable").show();
      $("#studentCount").trigger("change");
    } else {
      $("#studentControls, #studentsTable").hide();
      $("#studentsContainer").empty();
      studentIndex = 0;
      $("#studentCount").val(1);
    }
  });

  // 2) regenerate rows on count change
  $(document).on("input change", "#studentCount", function () {
    const cnt = parseInt($(this).val(), 10) || 0;
    $("#studentsContainer").empty();
    studentIndex = 0;
    for (let i = 0; i < cnt; i++) {
      addStudentRow(studentIndex++);
    }
  });

  // 3) remove row
  $(document).on("click", ".remove-student-row", function () {
    $(this).closest("tr").remove();
  });

  // 4) toggle notes textarea
  $(document).on("change", ".obs-select", function () {
    const ta = $(this).closest("tr").find("textarea");
    if ($(this).val()) ta.show();
    else ta.hide().val("");
  });
});
