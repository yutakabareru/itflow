$(document).ready(function() {
  var defaultConfirmMessage = $("#confirmationModal .modal-body").text().trim() || "Are you sure?";

  function showConfirmationModal(message, onConfirm) {
    $("#confirmationModal .modal-body").text(message || defaultConfirmMessage);
    $("#confirmationModal").modal('show');

    $("#confirmSubmitBtn").off('click').on('click', function() {
      $("#confirmationModal").modal('hide');
      onConfirm();
    });
  }

  $("#confirmationModal").on('hidden.bs.modal', function() {
    $("#confirmationModal .modal-body").text(defaultConfirmMessage);
  });

  $(document).on('click', 'a.confirm-link, a[href*="unlink_"]', function(e) {
    e.preventDefault();

    var linkReference = this;
    var message = $(linkReference).attr('href').indexOf('unlink_') !== -1
      ? 'Are you sure you want to unlink this item?'
      : defaultConfirmMessage;

    showConfirmationModal(message, function() {
      window.location.href = $(linkReference).attr('href');
    });
  });

  $(document).on('submit', 'form:has(input[name="edit_asset_interface"])', function(e) {
    var $form = $(this);

    if ($form.data('unlink-confirmed')) {
      $form.removeData('unlink-confirmed');
      return true;
    }

    var linkedId = parseInt($form.attr('data-linked-interface-id') || '0', 10);
    var connectedTo = $form.find('[name="connected_to"]').val();

    if (linkedId > 0 && !connectedTo) {
      e.preventDefault();

      showConfirmationModal('Are you sure you want to unlink this interface connection?', function() {
        $form.data('unlink-confirmed', true);
        $form.trigger('submit');
      });
    }
  });
});
