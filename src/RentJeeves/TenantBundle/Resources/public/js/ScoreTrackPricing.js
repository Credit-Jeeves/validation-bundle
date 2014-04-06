/**
 * ScoreTrackPricing simply displays pricing information for ScoreTrack and
 * launches ScoreTrackPayDialog if the users chooses to sign up
 */
function ScoreTrackPricing(options){
  this.options = options;
  this.pricingDialog = $("#pricing-popup");

  var self = this;

  /**
   * Multiple links from outside the context of this signup flow can launch
   * this module.
   */
  $('.show-scoretrack-pricing-popup').click(function(){
    self.pricing.call(self);
    return false;
  });
};

/**
  * Instantiate the dialog
  */
ScoreTrackPricing.prototype.pricing = function(){
  this.pricingDialog.dialog({
    width:660,
    modal:true
  });
};

/**
 * Close the pricing dialog and launch the pay dialog
 */
ScoreTrackPricing.prototype.pay = function(){
  this.pricingDialog.dialog('close');
  this.payDialog = new ScoreTrackPayDialog(this.options);
};
