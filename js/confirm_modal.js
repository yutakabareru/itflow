$(document).ready(function() {
  var defaultConfirmMessage = $("#confirmationModal .modal-body").text().trim() || "Are you sure?";
  var assetUnlinkMessage = "Are you sure you want to unlink this asset?";
  var itemUnlinkMessage = "Are you sure you want to unlink this item?";

  function showConfirmationModal(message, onConfirm) {
    $("#confirmationModal .modal-body").text(message || defaultConfirmMessage);
    $("#confirmationModal").modal("show");

    $("#confirmSubmitBtn").off("click").on("click", function() {
      $("#confirmationModal").modal("hide");
      onConfirm();
    });
  }

  $("#confirmationModal").on("hidden.bs.modal", function() {
    $("#confirmationModal .modal-body").text(defaultConfirmMessage);
  });

  window.captureFormAssetLinks = function($form) {
    if (!$form || !$form.length) {
      return;
    }

    var snapshot = {};

    if ($form.find('[name="connected_to"]').length) {
      snapshot.connected_to = getSelectedValues($form, "connected_to");
    }
    if ($form.find('[name="assets[]"]').length) {
      snapshot["assets[]"] = getSelectedValues($form, "assets[]");
    }
    if ($form.find('[name="additional_assets[]"]').length) {
      snapshot["additional_assets[]"] = getSelectedValues($form, "additional_assets[]");
    }

    if (Object.keys(snapshot).length) {
      $form.data("initial-asset-links", snapshot);
    }
  };

  function getSelectedValues($form, fieldName) {
    var values = [];

    if (fieldName === "connected_to") {
      var connectedTo = $form.find('[name="connected_to"]').val();
      if (connectedTo) {
        values.push(String(connectedTo));
      }
      return values;
    }

    $form.find('[name="' + fieldName + '"]:checked').each(function() {
      values.push(String($(this).val()));
    });

    $form.find('select[name="' + fieldName + '"]').each(function() {
      var selected = $(this).val();
      if (Array.isArray(selected)) {
        selected.forEach(function(value) {
          if (value) {
            values.push(String(value));
          }
        });
      } else if (selected) {
        values.push(String(selected));
      }
    });

    return values.filter(function(value, index, array) {
      return array.indexOf(value) === index;
    });
  }

  function formIsRemovingAssetLinks($form) {
    var initial = $form.data("initial-asset-links");
    if (!initial) {
      return false;
    }

    for (var fieldName in initial) {
      if (!Object.prototype.hasOwnProperty.call(initial, fieldName)) {
        continue;
      }

      var initialValues = initial[fieldName] || [];
      var currentValues = getSelectedValues($form, fieldName);

      for (var i = 0; i < initialValues.length; i++) {
        if (currentValues.indexOf(String(initialValues[i])) === -1) {
          return true;
        }
      }
    }

    return false;
  }

  function isAssetUnlinkHref(href) {
    if (!href) {
      return false;
    }

    return href.indexOf("delete_ticket_additional_asset=") !== -1
      || (href.indexOf("unlink_") !== -1 && href.indexOf("asset") !== -1);
  }

  function getConfirmMessageForHref(href) {
    if (isAssetUnlinkHref(href)) {
      return assetUnlinkMessage;
    }
    if (href.indexOf("unlink_") !== -1) {
      return itemUnlinkMessage;
    }
    return defaultConfirmMessage;
  }

  $(document).on("click", "a.confirm-link, a[href*=\"unlink_\"], a[href*=\"delete_ticket_additional_asset=\"]", function(e) {
    e.preventDefault();

    var linkReference = this;
    var href = $(linkReference).attr("href") || "";

    showConfirmationModal(getConfirmMessageForHref(href), function() {
      window.location.href = href;
    });
  });

  $(document).on("focusin", "form:has([name=\"assets[]\"], [name=\"additional_assets[]\"], [name=\"connected_to\"])", function() {
    var $form = $(this);
    if (!$form.data("initial-asset-links")) {
      window.captureFormAssetLinks($form);
    }
  });

  $(document).on("submit", "form:has([name=\"assets[]\"], [name=\"additional_assets[]\"], [name=\"connected_to\"])", function(e) {
    var $form = $(this);

    if ($form.data("asset-unlink-confirmed")) {
      $form.removeData("asset-unlink-confirmed");
      return true;
    }

    if (!$form.data("initial-asset-links")) {
      window.captureFormAssetLinks($form);
    }

    if (formIsRemovingAssetLinks($form)) {
      e.preventDefault();

      showConfirmationModal(assetUnlinkMessage, function() {
        $form.data("asset-unlink-confirmed", true);
        $form.trigger("submit");
      });
    }
  });
});
