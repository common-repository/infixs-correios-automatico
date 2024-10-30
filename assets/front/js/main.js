/**
 * Infixs Correios Automático - Main JS Front-End.
 *
 * @version 1.0.0
 * @since   1.0.0
 */

/**
 * @global {Object} infxsCorreiosAutomatico - Global object for Infixs Correios Automático.
 * @property {string} product_id - The product ID.
 * @property {string} nonce - The nonce for AJAX requests.
 */

jQuery(function ($) {
  /**
   * Infixs Correios Automático Input Text.
   *
   * @since 1.0.0
   * @version 1.0.0
   *
   * @param {JQuery<HTMLElement>} element
   */
  const InfixsCorreiosAutomaticoInputText = (inputSelector) => {
    const element = $(inputSelector);

    return {
      element: element,
      input: element.find("input"),
      /**
       * Error element.
       * @type {JQuery<HTMLElement>|null}
       * @default null
       */
      error: null,
      getValue() {
        return this.input.val();
      },
      setValue(value) {
        this.input.val(value);
      },
      setLoading() {
        this.element.find(".infixs-correios-automatico-loading").show();
      },
      unsetLoading() {
        this.element.find(".infixs-correios-automatico-loading").hide();
      },
      /**
       * Set the input as invalid.
       *
       * @param {string} message
       * @param {JQuery<HTMLElement>|null} element
       */
      setError(message, element = null) {
        this.unsetError();
        this.element.addClass("infixs-correios-automatico-invalid");
        $("<div>")
          .addClass("infixs-correios-automatico-error-message")
          .text(message)
          .css({
            opacity: "0",
            "font-size": "12px",
            "max-height": "0",
            color: "#ff0000",
          })
          .insertAfter(element ?? this.element)
          .animate(
            {
              opacity: "1",
              "max-height": "100px",
            },
            300
          );
      },
      unsetError() {
        this.element.removeClass("infixs-correios-automatico-invalid");
        $(".infixs-correios-automatico-error-message").remove();
      },
    };
  };

  function postcodeMask() {
    let value = $(this).val().replace(/\D/g, "");
    if (value.length > 8) {
      value = value.substring(0, 8);
    }
    if (value.length > 5) {
      value = value.replace(/^(\d{5})(\d)/, "$1-$2");
    }
    $(this).val(value);
  }

  /**
   * Admin class.
   */
  const InfixsCorreiosAutomaticoFront = {
    /**
     * Initialize the class.
     */
    init() {
      this.applyListners();
      this.applyPostcodeMask();
    },

    /**
     * Apply listners.
     *
     * @since 1.0.0
     * @version 1.0.0
     *
     * @return {void}
     */
    applyListners() {
      $(document.body).on(
        "submit",
        "#infixs-correios-automatico-calculator",
        this.calculateShipping.bind(this)
      );
    },

    /**
     * Apply PostCode mask to inputs with the class 'infixs-correios-automatico-postcode-mask'.
     */
    applyPostcodeMask() {
      $(document.body).on(
        "input",
        ".infixs-correios-automatico-postcode-mask",
        postcodeMask
      );

      this.bindPostcodeMask("input#billing_postcode");
      this.bindPostcodeMask("input#shipping_postcode");
      //this.bindPostcodeMask("input#shipping-postcode");
      //this.bindPostcodeMask("input#0-postcode");
    },

    bindPostcodeMask(selector) {
      $(document.body).on("input", selector, postcodeMask);
      $(document.body).on("blur", selector, postcodeMask);
    },

    /**
     * Submit and Calculate the shipping.
     *
     * @since 1.0.0
     * @version 1.0.0
     *
     * @param {Event} event
     *
     * @return {void}
     */
    calculateShipping(event) {
      event.preventDefault();

      if (typeof woocommerce_params === "undefined") {
        console.error("woocommerce_params.ajax_url is undefined.");
        return;
      }

      const postcodeInput = InfixsCorreiosAutomaticoInputText(
        ".infixs-correios-automatico-input-text"
      );

      const submitButton = $(".infixs-correios-automatico-calculate-submit");

      const postcode = postcodeInput.getValue().replace(/\D/g, "");
      if (postcode.length !== 8) {
        postcodeInput.setError(
          "CEP inválido, tente novamente.",
          ".infixs-correios-automatico-calculate-box"
        );
        return;
      }

      postcodeInput.unsetError();

      postcodeInput.setLoading();
      submitButton.prop("disabled", true);

      const variationId = this.getVariantion();

      $.ajax({
        url: woocommerce_params.ajax_url,
        type: "POST",
        data: {
          action: "infixs_correios_automatico_calculate_shipping",
          postcode: postcode,
          product_id: infxsCorreiosAutomatico.productId,
          ...(variationId && { variation_id: variationId }),
          nonce: infxsCorreiosAutomatico.nonce,
        },
        success: (response) => {
          $("#infixs-correios-automatico-calculate-results").html(response);
        },
        error: (error) => {},
        complete: () => {
          submitButton.prop("disabled", false);
          postcodeInput.unsetLoading();
        },
      });
    },

    getVariantion() {
      const variationInput = $('input[name="variation_id"]');
      if (variationInput.length === 0) return false;
      const variationId = parseInt(variationInput.val(), 10);
      return isNaN(variationId) ? false : variationId;
    },

    getInput(element) {},
  };

  // Initialize the class.
  InfixsCorreiosAutomaticoFront.init();
});
