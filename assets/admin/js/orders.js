/**
 * Infixs Correios Automático - Orders JS.
 *
 * @global
 * @name infixsCorreiosAutomaticoOrdersParams
 * @type {string}
 */
jQuery(function ($) {
  /**
   * Admin class.
   */
  const InfixsCorreiosAutomaticoOrders = {
    /**
     * Initialize the class.
     */
    init() {
      $(document.body).on(
        "click",
        ".infixs-correios-automatico-tracking-box .infixs-correios-automatico-add-tracking-code",
        this.addTrackingCode.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-tracking-box .infixs-correios-automatico-remove-code",
        this.removeTrackingCode.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-print-orders",
        this.printOrders.bind(this)
      );

      $(document.body).on(
        "click",
        "#infixs-correios-automatico-create-prepost-declaration",
        this.createPrepostDeclaration.bind(this)
      );

      $(document.body).on(
        "click",
        "#infixs-correios-automatico-print-label",
        this.printLabel.bind(this)
      );

      $(document.body).on(
        "click",
        "#infixs-correios-automatico-create-prepost-invoice",
        this.choosePrepostInvoice.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-create-prepost-invoice-cancel",
        this.cancelPrepostInvoice.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-create-prepost-invoice-create",
        this.createPrepostInvoice.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-tracking-update-button",
        this.showUpdateTrackingInput.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-tracking-cancel-button",
        this.cancelUpdateTracking.bind(this)
      );

      $(document.body).on(
        "click",
        ".infixs-correios-automatico-tracking-edit-form",
        this.updateTrackingForm.bind(this)
      );
      $(document.body).on(
        "click",
        ".column-infixs-correios-automatico-actions-column",
        this.updateTrackingForm.bind(this)
      );
      $(document.body).on(
        "click",
        ".infixs-correios-automatico-tracking-confirm-button",
        this.confirmTrackingCodeForm.bind(this)
      );
    },

    /**
     * Block meta boxes.
     *
     * @param {string} element Element.
     */
    block(element) {
      $(element).block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });
    },

    /**
     * Unblock meta boxes.
     *
     * @param {string} element Element.
     */
    unblock(element) {
      $(element).unblock();
    },

    /**
     * Add tracking code.
     *
     * @param {Event} event Event object.
     */
    addTrackingCode(event) {
      event.preventDefault();
      const orderId = $("#infixs-correios-automatico-order-id").val();
      const sendMail = $(
        "#infixs-correios-automatico-tracking-code-email-sendmail"
      ).is(":checked");
      const trackingCode = $(
        "#infixs-correios-automatico-tracking-code-input"
      ).val();

      if (!orderId || !trackingCode) return;

      InfixsCorreiosAutomaticoOrders.block(
        "#infixs-correios-automatico-tracking-code"
      );

      this.postTrackingCode(
        trackingCode,
        orderId,
        sendMail,
        (response) => {
          InfixsCorreiosAutomaticoOrders.addTableLine(
            trackingCode,
            response.data.id
          );
        },
        null,
        () => {
          InfixsCorreiosAutomaticoOrders.unblock(
            "#infixs-correios-automatico-tracking-code"
          );
        }
      );
    },

    postTrackingCode(
      code,
      orderId,
      sendmail = true,
      successCallback = null,
      errorCallback = null,
      completeCallback = null
    ) {
      const restUrl = infixsCorreiosAutomaticoOrdersParams.restUrl;
      const nonce = infixsCorreiosAutomaticoOrdersParams.nonce;

      const data = {
        code: code,
        order_id: orderId,
        sendmail: sendmail,
      };

      $.ajax({
        url: `${restUrl}/trackings`,
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json",
        headers: {
          "X-WP-Nonce": nonce,
        },
        success: function (response) {
          if (typeof successCallback === "function") {
            successCallback(response);
          }
        },
        complete: function (data) {
          if (typeof completeCallback === "function") {
            completeCallback(data);
          }
        },
        error: function (response) {
          if (typeof errorCallback === "function") {
            errorCallback(response);
          }
        },
      });
    },

    /**
     * Add table line.
     *
     * @param {string} trackingCode Tracking code.
     * @param {number} trackingId Tracking ID.
     */
    addTableLine(trackingCode, trackingId) {
      const headerAction = $(
        ".infixs-correios-automatico-header-action-column"
      );

      const trackingHtml = $("<a>", {
        href: "https://www.linkcorreios.com.br/?id=" + trackingCode,
        target: "_blank",
        "data-id": trackingId,
      }).text(trackingCode);

      $("<div>", {
        class: "infixs-correios-automatico-action-column",
      })
        .append(
          $("<a>", {
            href: "https://www.linkcorreios.com.br/?id=" + trackingCode,
            target: "_blank",
            "data-id": trackingId,
          }).html(
            '<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 32 32"><circle cx="16" cy="16" r="4" /><path d="M30.94 15.66A16.69 16.69 0 0 0 16 5A16.69 16.69 0 0 0 1.06 15.66a1 1 0 0 0 0 .68A16.69 16.69 0 0 0 16 27a16.69 16.69 0 0 0 14.94-10.66a1 1 0 0 0 0-.68M16 22.5a6.5 6.5 0 1 1 6.5-6.5a6.51 6.51 0 0 1-6.5 6.5" /></svg>'
          )
        )
        .append(
          $("<a>", {
            href: "#",
            class: "infixs-correios-automatico-remove-code",
            "data-id": trackingId,
          }).html(
            '<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24"><path d="M20 6a1 1 0 0 1 .117 1.993L20 8h-.081L19 19a3 3 0 0 1-2.824 2.995L16 22H8c-1.598 0-2.904-1.249-2.992-2.75l-.005-.167L4.08 8H4a1 1 0 0 1-.117-1.993L4 6zm-6-4a2 2 0 0 1 2 2a1 1 0 0 1-1.993.117L14 4h-4l-.007.117A1 1 0 0 1 8 4a2 2 0 0 1 1.85-1.995L10 2z" /></svg>'
          )
        )
        .insertAfter(headerAction);
      $("<div>").html(trackingHtml).insertAfter(headerAction);
    },

    /**
     * Remove tracking code.
     *
     * @param {Event} event Event object.
     */
    removeTrackingCode(event) {
      event.preventDefault();

      const element = $(event.currentTarget);
      const restUrl = infixsCorreiosAutomaticoOrdersParams.restUrl;
      const nonce = infixsCorreiosAutomaticoOrdersParams.nonce;
      const orderId = $("#infixs-correios-automatico-order-id").val();
      const trackingCodeId = element.data("id");

      if (!orderId || !nonce || !trackingCodeId) return;

      InfixsCorreiosAutomaticoOrders.block(
        "#infixs-correios-automatico-tracking-code"
      );

      $.ajax({
        url: `${restUrl}/trackings/${trackingCodeId}`,
        type: "DELETE",
        headers: {
          "X-WP-Nonce": nonce,
        },
        success: function () {
          $(
            `.infixs-correios-automatico-action-column a[data-id=${trackingCodeId}]`
          )
            .parent()
            .prev()
            .remove();
          $(
            `.infixs-correios-automatico-action-column a[data-id=${trackingCodeId}]`
          )
            .parent()
            .remove();
        },
        complete: function () {
          InfixsCorreiosAutomaticoOrders.unblock(
            "#infixs-correios-automatico-tracking-code"
          );
        },
      });
    },
    /**
     * Print orders.
     *
     * @param {Event} event Event object.
     */
    printOrders(event) {
      event.preventDefault();
      const selectedOrders = [];
      $(".wc-orders-list-table tbody tr th input[type=checkbox]").each(
        function () {
          if ($(this).is(":checked")) {
            const orderId = $(this).val();
            selectedOrders.push(orderId);
          }
        }
      );

      window.open(
        `${
          infixsCorreiosAutomaticoOrdersParams.adminUrl
        }&path=/print&orders=${selectedOrders.join(",")}`,
        "_blank"
      );
    },

    choosePrepostInvoice(event) {
      event.preventDefault();
      $(".infixs-correios-automatico-prepost-invoice-box").css(
        "display",
        "flex"
      );
      $(".infixs-correios-automatico-prepost-box").css("display", "none");
    },

    cancelPrepostInvoice(event) {
      event.preventDefault();
      $(".infixs-correios-automatico-prepost-invoice-box").css(
        "display",
        "none"
      );
      $(".infixs-correios-automatico-prepost-box").css("display", "flex");
    },

    createPrepostInvoice(event) {
      event.preventDefault();

      const restUrl = infixsCorreiosAutomaticoOrdersParams.restUrl;
      const nonce = infixsCorreiosAutomaticoOrdersParams.nonce;
      const orderId = $("#infixs-correios-automatico-order-id").val();
      const invoiceNumber = $(
        "#infixs-correios-automatico-prepost-invoice-number"
      ).val();
      const invoiceKey = $(
        "#infixs-correios-automatico-prepost-invoice-key"
      ).val();

      if (invoiceNumber.trim().length === 0) {
        alert("O número da nota fiscal é obrigatório.");
        return;
      }

      if (invoiceKey.trim().length !== 44) {
        alert("A chave da nota fiscal deve conter 44 caracteres.");
        return;
      }

      if (!orderId || !nonce) return;

      InfixsCorreiosAutomaticoOrders.block(
        "#infixs-correios-automatico-prepost"
      );

      $.ajax({
        url: `${restUrl}/preposts`,
        type: "POST",
        data: JSON.stringify({
          order_id: orderId,
          invoice_number: invoiceNumber,
          invoice_key: invoiceKey,
        }),
        contentType: "application/json",
        headers: {
          "X-WP-Nonce": nonce,
        },
        success: function () {
          window.location.reload();
        },
        error: function (response) {
          InfixsCorreiosAutomaticoOrders.unblock(
            "#infixs-correios-automatico-prepost"
          );
          alert(response.responseJSON.message);
        },
      });
    },

    /**
     * Create prepost declaration.
     *
     * @param {Event} event Event object.
     */
    createPrepostDeclaration(event) {
      event.preventDefault();
      InfixsCorreiosAutomaticoOrders.confirmAlert(
        "#infixs-correios-automatico-prepost",
        "Deseja realmente criar a Pré-Postagem com declaração de conteúdo?",
        () => {
          const restUrl = infixsCorreiosAutomaticoOrdersParams.restUrl;
          const nonce = infixsCorreiosAutomaticoOrdersParams.nonce;
          const orderId = $("#infixs-correios-automatico-order-id").val();

          if (!orderId || !nonce) return;

          InfixsCorreiosAutomaticoOrders.block(
            "#infixs-correios-automatico-prepost"
          );

          $.ajax({
            url: `${restUrl}/preposts`,
            type: "POST",
            data: JSON.stringify({ order_id: orderId }),
            contentType: "application/json",
            headers: {
              "X-WP-Nonce": nonce,
            },
            success: function () {
              window.location.reload();
            },
            error: function (response) {
              InfixsCorreiosAutomaticoOrders.unblock(
                "#infixs-correios-automatico-prepost"
              );
              alert(response.responseJSON.message);
            },
          });
        }
      );
    },

    /**
     * Confirm alert.
     *
     * @param {HTMLElement} element Element.
     * @param {string} message Message.
     */
    confirmAlert(element, message, callback) {
      const overlay = $("<div>")
        .addClass("infixs-correios-automatico-alert-overlay")
        .html(message);

      const buttons = $("<div>").addClass(
        "infixs-correios-automatico-alert-buttons"
      );

      const confirmButton = $("<button>")
        .addClass("button button-primary")
        .text("Sim");

      confirmButton.on("click", function () {
        overlay.remove();
        if (typeof callback === "function") {
          callback();
        }
      });

      const cancelButton = $("<button>").addClass("button").text("Não");

      cancelButton.on("click", function () {
        overlay.remove();
      });

      buttons.append(confirmButton, cancelButton);

      overlay.append(buttons);

      $(element).append(overlay);
    },

    printLabel(event) {
      event.preventDefault();
      const orderId = $("#infixs-correios-automatico-order-id").val();
      window.open(
        `${infixsCorreiosAutomaticoOrdersParams.adminUrl}&path=/print&orders=${orderId}`,
        "_blank"
      );
    },

    /** Update Tracking Column */

    getTrackingElements(event) {
      const element = $(event.target);
      const wrapper = element.closest(
        ".infixs-correios-automatico-tracking-column-wrapper"
      );
      const form = wrapper.find(
        ".infixs-correios-automatico-tracking-edit-form"
      );
      const confirmButton = form.find(
        ".infixs-correios-automatico-tracking-confirm-button"
      );

      const cancelButton = form.find(
        ".infixs-correios-automatico-tracking-cancel-button"
      );

      const loading = form.find(".infixs-correios-automatico-spin-animation");

      const orderId = wrapper.data("order-id");
      return {
        element,
        wrapper,
        form,
        orderId,
        confirmButton,
        cancelButton,
        loading,
      };
    },

    showUpdateTrackingInput(event) {
      event.preventDefault();
      const { form } = this.getTrackingElements(event);
      form.show();
    },

    cancelUpdateTracking(event) {
      event.preventDefault();
      const { form } = this.getTrackingElements(event);
      form.hide();
    },

    confirmTrackingCodeForm(event) {
      event.preventDefault();
      const { form, orderId, confirmButton, cancelButton, loading } =
        this.getTrackingElements(event);
      const trackingCode = form.find("input").val();
      if (!trackingCode) return;

      confirmButton.attr("disabled", true);
      cancelButton.attr("disabled", true);
      loading.css("display", "flex");

      this.postTrackingCode(
        trackingCode,
        orderId,
        true,
        (response) => {
          const trackingCodeElement = $("<a>")
            .attr("href", `https://www.linkcorreios.com.br/?id=${trackingCode}`)
            .attr("target", "_blank")
            .text(trackingCode);

          const maybeTrackingElement = form
            .closest("tr")
            .find(".infixs-correios-automatico-tracking-code-link");
          if (maybeTrackingElement.length) {
            maybeTrackingElement.html(trackingCodeElement);
          }
        },
        null,
        () => {
          form.hide();
          confirmButton.attr("disabled", false);
          cancelButton.attr("disabled", false);
          loading.hide();
        }
      );
    },

    /**
     * Update tracking form.
     *
     * @param {Event} event Event object.
     */
    updateTrackingForm(event) {
      event.stopPropagation();
    },
  };

  InfixsCorreiosAutomaticoOrders.init();
});
