jQuery(document).ready(function($) {
  if ($("#contact-form.cf").length) {
    $("#contact-form.cf form").ajaxForm({
      dataType: "json",
      success: function(data) {
        successMessage(data.message);
      },
      error: function({ responseJSON }) {
        const { code, data, message } = responseJSON;

        switch (code) {
          case "invalid_nonce":
          case "invalid_name":
          case "invalid_email":
          case "empty_message":
          case "long_message":
          case "unknown_error":
            errorMessage(message);
            break;

          default:
            window.location.reload();
            break;
        }
      },
    });
  }
});

function errorMessage(message) {
  $messageDiv = jQuery("#contact-form.cf form .cf-message");
  if ($messageDiv.length) {
    $messageDiv.text(message);
    return;
  }

  var $form = jQuery("#contact-form.cf form");
  $form.append('<div class="cf-row cf-message">' + message + "</div>");
}

function successMessage(message) {
  $messageDiv = jQuery("#contact-form.cf form .cf-message");
  if ($messageDiv.length) {
    $messageDiv.addClass("cf-success-message").text(message);
    return;
  }

  var $form = jQuery("#contact-form.cf form");
  $form.append(
    '<div class="cf-row cf-message cf-success-message">' + message + "</div>"
  );
}
