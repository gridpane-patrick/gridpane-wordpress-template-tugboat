jQuery(document).ready(function ($) {
  const id = $('#kashier-admin').attr('methodId');
  if ($(`#woocommerce_${id}_advanced_options:checked`).length > 0) {
    $('.form-table tr:nth-child(12)').show();
    $('.form-table tr:nth-child(13)').show();
    $('.form-table tr:nth-child(14)').show();
  } else {
    $('.form-table tr:nth-child(12)').hide();
    $('.form-table tr:nth-child(13)').hide();
    $('.form-table tr:nth-child(14)').hide();
  }
  $('#wpwrap').prepend(
    "<div id='kashier-error' style='position: fixed;right: -600px;transition: right 1.5s;top: 150px;background-color: #FF9494;line-height:30px;color: aliceblue; width:450px; height:30px; border: 1px solid #ccc9; border-radius:6px; padding:5px;box-shadow:0 3px 8px #33333361'></div>"
  );

  $('#mainform').submit(function (e) {
    if (
      $(`#woocommerce_${id}_advanced_options:checked`).length > 0 &&
      $(`.form-table tr:nth-child(12) input:checked`).length > 0
    ) {
      if ($('.form-table tr:nth-child(13) .input-text').val() <= 1) {
        $('#kashier-error').text('Exhange rates must be greater than 1');
        $('#kashier-error').css('right', '50px');

        setTimeout(() => {
          $('#kashier-error').css('right', '-600px');
        }, 5000);
        return false;
      }
      return true;
    }
  });

  $(`#woocommerce_${id}_advanced_options`).change(function () {
    if (this.checked) {
      $('.form-table tr:nth-child(12)').show();
      $('.form-table tr:nth-child(13)').show();
      $('.form-table tr:nth-child(14)').show();
    } else {
      $('.form-table tr:nth-child(12)').hide();
      $('.form-table tr:nth-child(13)').hide();
      $('.form-table tr:nth-child(14)').hide();
    }
  });
});
