jQuery(document).ready(function ($) {
  $("#run-functionality").on("click", function (e) {
    e.preventDefault();
    $(this).prop("disabled", true); // Disable the button
    $("#article-removal-feedback").empty(); // Clear previous feedback

    var userId = $("#user-select").val();
    var month = $("#month-select").val();

    // AJAX request to run functionality
    $.ajax({
      type: "POST",
      url: article_removal_tool.ajaxurl,
      data: {
        action: "run_functionality",
        user_id: userId,
        month: month,
      },
      success: function (response) {
        $("#article-removal-feedback").html(
          '<div class="notice notice-success"><p>' +
            response.data +
            "</p></div>"
        );
      },
      error: function (xhr, textStatus, errorThrown) {
        $("#article-removal-feedback").html(
          '<div class="notice notice-error"><p>' +
            xhr.responseText +
            "</p></div>"
        );
      },
      complete: function () {
        $("#run-functionality").prop("disabled", false); // Re-enable the button
      },
    });
  });
});
