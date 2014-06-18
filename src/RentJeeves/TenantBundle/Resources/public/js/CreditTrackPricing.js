/**
 * CreditTrackPricing simply displays pricing information for CreditTrack and
 * launches CreditTrackPayDialog if the users chooses to sign up
 */
function CreditTrackPricing(options){
  this.options = options;
  this.pricingDialog = $("#pricing-popup");

  var self = this;

  /**
   * Multiple links from outside the context of this signup flow can launch
   * this module.
   */
  $('.show-credittrack-pricing-popup').click(function(){
    self.pricing.call(self);
    return false;
  });

  /**
    * Instantiate the dialog
    */
  this.pricing = function(){
    this.pricingDialog.dialog({
      width:660,
      modal:true
    });
  };

  /**
  * Close the pricing dialog and launch the pay dialog
  */
  this.pay = function(){
    this.pricingDialog.dialog('close');
    this.payDialog = new CreditTrackPayDialog(this.options);
  };
};
