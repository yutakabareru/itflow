$(document).ready(function() {
  var defaultConfirmMessage = $("#confirmationModal .modal-body").text().trim() || "Are you sure?";
  var assetUnlinkMessage = "Are you sure you want to unlink this asset?";
  var documentUnlinkMessage = "Are you sure you want to unlink this document?";
  var itemUnlinkMessage = "Are you sure you want to unlink this item?";

  var linkFieldNames = [
    "connected_to",
    "assets[]",
    "additional_assets[]",
    "documents[]"
  ];

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

  function isUnlinkHref(href) {
    if (!href) {
      return false;
    }

    return href.indexOf("unlink_") !== -1
      || href.indexOf("delete_ticket_additional_asset=") !== -1;
  }

  function isDocumentUnlinkHref(href) {
    if (!href) {
      return false;
    }

    return href.indexOf("_from_document") !== -1
      || (href.indexOf("unlink_") !== -1 && href.indexOf("document") !== -1);
  }

  function isAssetUnlinkHref(href) {
    if (!href) {
      return false;
    }

    return href.indexOf("delete_ticket_additional_asset=") !== -1
      || (href.indexOf("unlink_") !== -1 && href.indexOf("asset") !== -1);
  }

  function getConfirmMessageForHref(href) {
    if (isDocumentUnlinkHref(href)) {
      return documentUnlinkMessage;
    }
    if (isAssetUnlinkHref(href)) {
      return assetUnlinkMessage;
    }
    if (href.indexOf("unlink_") !== -1) {
      return itemUnlinkMessage;
    }
    return defaultConfirmMessage;
  }

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

  function captureFormLinkSnapshot($form) {
    if (!$form || !$form.length) {
      return;
    }

    var snapshot = {};

    linkFieldNames.forEach(function(fieldName) {
      if ($form.find('[name="' + fieldName + '"]').length) {
        snapshot[fieldName] = getSelectedValues($form, fieldName);
      }
    });

    if (Object.keys(snapshot).length) {
      $form.data("initial-link-snapshot", snapshot);
    }
  }

  window.captureFormAssetLinks = captureFormLinkSnapshot;
  window.captureFormLinks = captureFormLinkSnapshot;

  function getRemovedLinkFieldNames($form) {
    var initial = $form.data("initial-link-snapshot");
    var removed = [];

    if (!initial) {
      return removed;
    }

    linkFieldNames.forEach(function(fieldName) {
      if (!Object.prototype.hasOwnProperty.call(initial, fieldName)) {
        return;
      }

      var initialValues = initial[fieldName] || [];
      var currentValues = getSelectedValues($form, fieldName);

      for (var i = 0; i < initialValues.length; i++) {
        if (currentValues.indexOf(String(initialValues[i])) === -1) {
          removed.push(fieldName);
          break;
        }
      }
    });

    return removed;
  }

  function getConfirmMessageForRemovedLinks(removedFieldNames) {
    if (removedFieldNames.indexOf("documents[]") !== -1) {
      return documentUnlinkMessage;
    }
    if (removedFieldNames.indexOf("assets[]") !== -1 || removedFieldNames.indexOf("additional_assets[]") !== -1 || removedFieldNames.indexOf("connected_to") !== -1) {
      return assetUnlinkMessage;
    }
    return itemUnlinkMessage;
  }

  function formIsRemovingLinks($form) {
    return getRemovedLinkFieldNames($form).length > 0;
  }

  document.addEventListener("click", function(e) {
    var anchor = e.target.closest("a[href]");
    if (!anchor) {
      return;
    }

    var href = anchor.getAttribute("href") || "";
    if (!href || href === "#") {
      return;
    }

    var needsConfirm = anchor.classList.contains("confirm-link") || isUnlinkHref(href);
    if (!needsConfirm) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    showConfirmationModal(getConfirmMessageForHref(href), function() {
      window.location.href = href;
    });
  }, true);

  var formSelector = "form:has([name=\"assets[]\"], [name=\"additional_assets[]\"], [name=\"connected_to\"], [name=\"documents[]\"])";

  $(document).on("focusin", formSelector, function() {
    var $form = $(this);
    if (!$form.data("initial-link-snapshot")) {
      captureFormLinkSnapshot($form);
    }
  });

  $(document).on("submit", formSelector, function(e) {
    var $form = $(this);

    if ($form.data("link-removal-confirmed")) {
      $form.removeData("link-removal-confirmed");
      return true;
    }

    if (!$form.data("initial-link-snapshot")) {
      captureFormLinkSnapshot($form);
    }

    var removedFieldNames = getRemovedLinkFieldNames($form);
    if (removedFieldNames.length) {
      e.preventDefault();

      showConfirmationModal(getConfirmMessageForRemovedLinks(removedFieldNames), function() {
        $form.data("link-removal-confirmed", true);
        $form.trigger("submit");
      });
    }
  });
});
