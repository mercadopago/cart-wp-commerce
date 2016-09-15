(function() {

  var MPv1Ticket = {
    site_id: '',
    selectors: {
      paymentMethodId: "#paymentMethodId",
      amount: "#amountTicket",
      site_id: "#site_id",
      box_loading: "#mp-box-loading",
      submit: "#submit",
      form: "#mercadopago-form",
      utilities_fields: "#mercadopago-utilities"
    },
    paths: {
      loading: "images/loading.gif"
    }
  }

  /*
   *
   *
   * Initialization function
   *
   */
  MPv1Ticket.addListenerEvent = function(el, eventName, handler) {
    if (el != null) {
      if (el.addEventListener) {
        el.addEventListener(eventName, handler);
      } else {
        el.attachEvent('on' + eventName, function() {
          handler.call(el);
        });
      }
    }
  };

  MPv1Ticket.Initialize = function(site_id) {
    //sets
    MPv1Ticket.site_id = site_id
    return;
  }

  this.MPv1Ticket = MPv1Ticket;

}).call();
