window.i18n = (function() {
  this.culture = 'en';
  var container = {
    "global": {
      "internal-error": "Internal error",
      "learn-more": "Learn more",
      "estimated-fico": "Estimated FICO",
      "first": "First",
      "last": "Last",
      "street": "Street",
      "unit-number": "Unit #",
      "city": "City",
      "zip-code": "ZIP",
      "month": "Month",
      "year": "Year",
      "address": "Address",
      "updated-%DATE%": "Updated %DATE%",
      "applicant": "Applicant",
      "dealership": "Dealership"
    },
    "simulation": {
      "title": "Take Action to Reach Your Target Score",
      "simulator_best_use_of_cash_label": "I have this much cash available:",
      "reached-score-title-message": "You have reached your target score.",
      "reached-score-title-sub-message": "You can still simulate how much higher you can reach by entering a cash value to the right.",
      "score-reach-title-message-%CASH_USED%": "You may be able to reach your dealer&#039;s target score. Cash required: $%CASH_USED%",
      "score-reach-title-sub-message-%POINTS_INCREASE%": "The steps below could increase your score by %POINTS_INCREASE% points.",
      "score-search-reach-title-message-%POINTS_INCREASE%": "By taking the following steps, you could potentially increase your score by %POINTS_INCREASE% points.",
      "score-search-reach-title-sub-message-%CASH_USED%": "Apply $%CASH_USED% in cash by following the steps below.",
      "score-not-reach-title-message": "We were unable to meaningfully affect your score in the short term.",
      "score-not-reach-title-sub-message": "Review the potentially negative items below to see what is affecting your score.",
      "cash-reach-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%": "By using $%CASH_USED% in cash, you could potentially reach your target score.",
      "cash-reach-title-sub-message": "Follow the steps below to optimize your credit score.",
      "cash-increase-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%": "By using $%CASH_USED% in cash, you could potentially increase your score by %POINTS_INCREASE% points.",
      "cash-increase-title-sub-message": "Follow the steps below to optimize your credit score.",
      "cash-not-reach-title-message-%CASH%": "We were unable to meaningfully effect your score with $%CASH% cash.",
      "step-%STEP%": "Step %STEP%",
      "re-score": "Re-Score",
      "group-10x": "Make Payment",
      "group-20x": "Establish credit",
      "group-30x": "Conaolidate",
      "group-40x": "New Loan",
      "10x-message-%BANK%-%ACCOUNT%-%CASH_DIFF%-%BALANCE%": "Pay your %BANK% account %ACCOUNT% down by $%CASH_DIFF% to a balance $%BALANCE% or less.",
      "10x-sub-message-%BANK%-%BANK_PHONE%": "Call %BANK% at %BANK_PHONE% to arrange payment. This will lower your overall utilization",
      "20x-message-%AMOUNT1%": "Open a new credit card with a limit of $%AMOUNT1% and maintain a $0 balance.",
      "20x-sub-message": "Establish more revolving credit by opening a new credit card or secured card.",
      "30x-message-%BALANCE%": "Consolidate some accounts to a new credit card, and pay the overall balance down to less then $%BALANCE%.",
      "30x-sub-message-%BANKS%": "Accounts to consolidate: %BANKS%.",
      "40x-message-%BALANCE%": "Consolidate some accounts into a new revolving or home equity loan. Consolidated balance: $%BALANCE%.",
      "40x-sub-message-%BANKS%": "Accounts to consolidate: %BANKS%.",
      "tier-%TIER%": "Tier %TIER%",
      "dealer-score-reach-title-sub-message-%TIER%": "The steps below could help you reach Tier %TIER%.",
      "dealer-score-search-reach-title-message-%TIER%": "By taking the following steps, you could potentially reach tier %TIER%.",
      "dealer-cash-reach-title-message-%TIER%-%STEPS%-%CASH_USED%-%SCORE_BEST%": "By using $%CASH_USED% in cash, you could potentially reach tier %TIER%."
    }
  };

  function replaceArgs(str, args) {
    if (!args) return str;
    for (var val in args) {
      str = str.replace(new RegExp('%' + val + '%', "g"), args[val]);
    }
    return str;
  }

  this.__ = function(str, args, catalogue) {
    var str = jQuery.trim(str);
    var returnVal = str;
    if (!catalogue) catalogue = 'messages';
    if (container[catalogue] && container[catalogue][str]) {
      returnVal = container[catalogue][str];
    }
    return replaceArgs(returnVal, args);
  };
  return this;
})();
