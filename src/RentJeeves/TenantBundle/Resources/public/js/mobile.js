//mobile page init
var paymentForm = '#rentjeeves_checkoutbundle_paymenttype';
var paymentBalanceForm = '#rentjeeves_checkoutbundle_paymentbalanceonlytype';
var currentPaymentForm = paymentForm;
var prefix = currentPaymentForm + '_';
var accountPrefix = "rentjeeves_checkoutbundle_paymentaccounttype_";
var contractCollection = {};

//since we aren't using KO, list those visible= when card or bank and use Jquery to set
bankVisibleFields = [
    "PayorName",
    "RoutingNumber",
    "ACHDepositType",
    "ACHDepositType_0",
    "ACHDepositType_1",
    "ACHDepositType_2",
    "AccountNumber_AccountNumber",
    "AccountNumber_AccountNumberAgain",
    "ACHDepositType_box",
]
cardVisibleFields = [
    //"CardAccountName",
    "CardAccountNumber",
    "VerificationCode",
    //"ExpirationYear-button",
    "ExpirationMonth_box",
    "address_choice",
    "is_new_address_link",
    "address",
    "address_choice_box",
    //"address_area-button",
    //"address_zip",
    "CardNumber",
    "CardAccountName_box"
]

debug = false;

payAccounts = []

lastHistory = -1; //keep track of last history ID to be fetched. this tell us when we've finished loading

$(document).ready(function() {

    h = $.mobile.getScreenHeight();
    h -= 60;
    $(".ui-content").css('min-height', h + 'px');

    $.getJSON("/checkout/payment-accounts/list", function(d) {
        payAccounts = d
        init() //we need payAccounts first
        $.each(historyIds, function(key, value) {
            lastHistory = value;
            getHistory(value) //this eventually calls deposit date, which needs payAccounts
        })
    })

})

function init() {
    //load main page payments info

    loadPaymentTable()

    //check that we are at main page

    $.mobile.changePage('#main')

    //fix labels (avoid backend hacks)

    subs = [
        ["amount_label", "Rent Amount"],
        ["paidFor_label", "for month of*"],
        ["amountOther_label", "Other Amount (Late Fees, etc.)"],
        ["paymentAccount_label", "Payment Source"]
    ];

    $.each(subs, function(i, label) {
        $(prefix + label[0]).html(label[1])
    });

    //create event listener for cancel payment box

    $(document).delegate('#opendialog', 'click', function() {
        $('<div>').simpledialog2({
            mode: 'blank',
            headerText: false,
            headerButton: false,
            headerClose: false,
            blankContent: "<div style='padding: 5px;'>Are you sure you want to cancel this payment?</div>" +
            "<br><a href='' data-ajax='false' id='confirmCancel' style='text-decoration: none;'>" +
            "<button style='color: white; background: rgb(232,65,65);text-shadow:none; border-radius: 7px;'>Cancel Payment</button></a>" +
            "<a rel='close' href='#main' data-ajax='false' style='text-decoration: none;'><button style='text-shadow:none; border-radius: 7px;'>Back</button></a>"
        })
    });

    $(".selector").loader({
        disabled: true
    });

    //contract individual pages info filled out from JSON
    contractsArr = $.map(contractsJson, function(el) {
        contractCollection[el.id] = el;
        return el;
    });

    for (i = 0; i < contractsArr.length; i++) {
        var contract = contractsArr[i];
        $("#contractPayTo" + contract.id).html(contract.payToName);

        dueDate = getOrdinal(contract.dueDate);
        $("#contractDueNext" + contract.id).html(dueDate);
        if (contract.payment) {
            $("#contractTotal" + contract.id).html(contract.payment.total)
            $("#cancel" + contract.id).attr("onclick", "setTimeout(function(){cancelPayment(" + contract.payment.id + "); $('.ui-dialog').hide()},50)")
            //settimeout hide dialog fixes weird bug with simpledialog

            $.each(payAccounts, function(index, localPaymentAccount) {
                if (localPaymentAccount.id == contract.payment.paymentAccountId) {
                    $("#contractFromAcc" + contract.id).html(localPaymentAccount.name)
                }
            })
        } else {
            $("#contractFromAccLabel" + contract.id).hide()
            $("#contractTotal" + contract.id).html(contract.rent)

        }
        if(contract.groupSetting.is_integrated){
            $("#contractTotalLabel" + contract.id).html("BALANCE")
            $("#contractTotal" + contract.id).html(contract.integrated_balance)
        }
        /*
         contract object does not have payToName
         so we can just use the JSON object to put this in.
         */

    }


    //fix KO payment card/account
    $("#" + accountPrefix + "PayorName_row")


    //Misc. HTML fixes


    $("<b><span>Total to Pay: </span>$<span id='total'></span></b>").insertAfter($(prefix + "amountOther"))
    $(prefix + "total_row").hide()
    $(prefix + "amount").onchange = updateTotal;
    $(prefix + "amountOther").onchange = updateTotal
    $(prefix + "type_label").html("One-Time or Recurring<sup>*</sup>");


    //Add new payment source page*************

    //show card/bank depending on radio selection

    $("#" + accountPrefix + "type_1").bind("change", function(event, id) { //bank
        var cardVisibility = "none"
        var bankVisibility = "block"
        showHideBankCardFields(bankVisibility, cardVisibility)
    })

    $("#" + accountPrefix + "type_0").bind("change", function(event, id) { //credit
        var cardVisibility = "block"
        var bankVisibility = "none"
        showHideBankCardFields(bankVisibility, cardVisibility)
    })

    $("#" + accountPrefix + "type_2").bind("change", function(event, id) { //debit
        var cardVisibility = "block"
        var bankVisibility = "none"
        showHideBankCardFields(bankVisibility, cardVisibility)
    })

    $("#" + accountPrefix + "CardNumber_box").find("ul").hide()
    //remove card type helper
    $("#" + accountPrefix + "VerificationCode_box").find("i").hide()


    //remove csc helper
    $("#" + accountPrefix + "is_new_address_link_row").hide()

    //$("#"+accountPrefix+"type_box").last().hide()
    //delete tooltip

    $("input [name='rentjeeves_checkoutbundle_paymentaccounttype[address_choice]']").parent().hide()

    $("#" + accountPrefix + "RoutingNumber_box").children().last().hide()
    //remove routing number tooltip
    $("#" + accountPrefix + "address_choice_box").children().last().hide()

    //remove textbox from is_new_address_link, not applicable here
    $("#" + accountPrefix + "is_new_address_link").hide()
    $("#" + accountPrefix + "address_choice").parent().hide()

    $("#" + accountPrefix + "address_choice_row").append('<input type="button" value="Add new address" onclick="showAddNewAddress()" id="addNewAddressBttn">')
    $("#addNewAddressBttn").button()
    $("#" + accountPrefix + "type_box").children().last().hide() // remove tooltip
    $("#" + accountPrefix + "save").hide() //we always save payment info, so button is not relevant

    $("#" + accountPrefix + "CardNumber_box").show()

    //set to eCheck default
    $("#" + accountPrefix + "ExpirationYear").selectmenu() //fixes a bug where this does not self init
    showHideBankCardFields("block", "none")


    var ua = navigator.userAgent.toLowerCase();
    var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
    if(isAndroid) {
        $(prefix + "amount").attr("type","numberDecimal") //address weird bug on samsung devices where decimal point is missing
    }


    $("#" + accountPrefix + "CardNumber_box").show()
    $("#" + accountPrefix + "VerificationCode_box").show()

    $("input[name='rentjeeves_checkoutbundle_paymentaccounttype[address_choice]']").hide()

    $(document).on('pagebeforeshow', '#addNewPayAccount', function (event) {
        if ($('#payment-type-with-fee').length > 0) {
            renderFeeForPayment();
        }
        $('.payment-type-change-order .ui-radio label').first().click();
    });
}

function renderPayAccounts(contract) {

    if (contract.allowDebitCard) {
        $("#" + accountPrefix + "type_2")
            .show()
            .parent().show();

    } else {
        $("#" + accountPrefix + "type_2")
            .hide()
            .parent().hide();
    }
    if (contract.allowCreditCard) {
        $("#" + accountPrefix + "type_0")
            .show()
            .parent().show()
            .find('label').click();
    } else {
        $("#" + accountPrefix + "type_0")
            .hide()
            .parent().hide();
    }
    if (contract.allowBank) {
        $("#" + accountPrefix + "type_1")
            .show()
            .parent().show()
            .find('label').click();
    } else {
        $("#" + accountPrefix + "type_1")
            .hide()
            .parent().hide();
    }
    $(prefix + "paymentAccount").html("");
    $.each(payAccounts, function(index, payAccount){
        if((payAccount.type == "debit_card" && contract.allowDebitCard) ||
            (payAccount.type == "card" && contract.allowCreditCard) ||
            (payAccount.type == "bank" && contract.allowBank)
        ) {
            $(prefix + "paymentAccount").append("<option value='" + payAccount.id + "'>" + payAccount.name + "</option>");
        }
    });

    //add "Add new payment source"
    $(prefix + "paymentAccount").append("<option value='-2'>----</option>") //we need this as to bind something to onchange
    $(prefix + "paymentAccount").append("<option value='-1'>Add new payment source</option>")
    $(prefix+"paymentAccount").bind( "change", function(event, ui) {
        if (this.value == -1) {
            $("#" + accountPrefix + "group").val(contract.groupId)
            //get contractId
            $("#" + accountPrefix + "contract").val(contract.id)
            $(prefix + "contractId").val(contract.id)
            saving = false;
            $("#deleteSource").parent().hide()
            $.mobile.changePage('#addNewPayAccount')
        }
    });

    $("input[name='rentjeeves_checkoutbundle_paymentaccounttype[address_choice]']").bind("change", function(event, ui) {
        $("#" + accountPrefix + "address").parent().hide()
        $("#" + accountPrefix + "is_new_address").val("false")
    })
}

function showAddNewAddress() {
    $("#" + accountPrefix + "is_new_address").val("true")
    $("#" + accountPrefix + "address").parent().css("display", "block")
    //$("#"+accountPrefix+"address_choice").parent().css("display","none")
    $("input[name='rentjeeves_checkoutbundle_paymentaccounttype[address_choice]']").prop('checked', false).checkboxradio("refresh")
}


function showHideBankCardFields(bankVisibility, cardVisibility) {
    $.each(bankVisibleFields, function(index, fieldId) {
        var id = accountPrefix + fieldId
        //seperate these 2 our incase label doens't exist for input, so we have no jquery error
        $("#" + id).parent().css("display", bankVisibility)
        $("label[for=" + id + "]").parent().css("display", bankVisibility)
    })
    $.each(cardVisibleFields, function(index, fieldId) {
        var id = accountPrefix + fieldId
        $("#" + id).parent().css("display", cardVisibility)
        $("label[for=" + id + "]").parent().css("display", cardVisibility)
    })
    $("#" + accountPrefix + "address").parent().css("display", "none")
}


//edit/deleting payment sources

var saving = false; //is true when saving an edit, false when saving a new one
function editSource(name) {
    saving = true;
    $("#" + accountPrefix + "name").val(name);

    //fetch our ID
    $.each(payAccounts, function(i, localPaymentAccountId) {
        if (localPaymentAccountId.name == name) {
            id = localPaymentAccountId.id;
        }
    })

    $("#deleteSource").attr("onclick", "deleteSource(" + id + ")")
    $("#deleteSource").parent().show()

    //hit http://dev-nr.renttrack.com/sources/save with POST data
    $.mobile.changePage('#addNewPayAccount');

}

function deleteSource(id){
    //hit http://dev-nr.renttrack.com/sources/del/


    //check it we still need this source

    for(i=0;i<contractsArr.length;i++){
        contract = contractsArr[i]
        if(contract.payment) {
            if (contract.payment.paymentAccountId == id) {
                alert("Cannot delete this payment source. It is currently in use by an existing payment.")
                return;
            }
        }
    }

    if(!confirm("Are you sure you want to delete this payment source?"))
        return

    //use the ID to hit our source
    $.ajax({
        url: '/sources/del/' + id,
        type: 'post',
        async: 'true',

        success: function(result) {
            a = result
            if (debug) {
                console.log(result)
            }
            window.location = "?a=" + Math.random() * 10000000000 //refresh the page

        },
        error: function(request, error) { //ajax error!

        }
    });

}

//cancel payment function for dialog

function cancelPayment(i) {
    $("#confirmCancel").attr("href", "/checkout/cancel/" + i);
}

//init pay form when user edits contract

var globalContractId = -1; //hold current edited contract ID

function setupPayForm(id) {

    globalContractId = id;

    //make sure error message is hidden/cleared, in case we try to pay multiple contracts

    $("#errorMsg").html("").hide()
    clearRedFields()

    $(".fields-box").css('padding-bottom', '10px')

    $("#backToContract").attr("href", "#contract" + id)

    var today = new Date();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();


    if (mm < 10) {
        mm = '0' + mm
    }

    //reset 'other' amount

    $(prefix + "amountOther").val("0.00");
    contractsArr = $.map(contractsJson, function(el) {
        return el
    });
    for (i = 0; i < contractsArr.length; i++) {
        if (contractsArr[i].id == id) {

            var contract = contractsArr[i]

            var dueDate = parseInt(contract.startAt.substr(8,2))

            if (contract.groupSetting.pay_balance_only) {
                currentPaymentForm = paymentBalanceForm;
                $('#integratedBalanceBox').show();
                $('#integratedBalanceValue').html('$'+ contract.integrated_balance);
                $(paymentForm).hide();
                $(paymentBalanceForm).show();
            } else {
                currentPaymentForm = paymentForm;
                $('#integratedBalanceBox').hide();
                $(paymentBalanceForm).hide();
                $(paymentForm).show();
            }

            prefix = currentPaymentForm + '_';

            renderPayAccounts(contract);

            //check that user is verified

            if (isVerified != 'passed' && contract.isPidVerificationSkipped == false) {
                $.mobile.changePage('#notVerified')
                break;
            } else {
                $.mobile.changePage('#pay')
            }

            //read days contract can be paid and setup calendar widget accordingly


            var payableDays = []
            for (j = 0; j < contract.groupSetting.dueDays.length; j++) {
                payableDays.push([-1, -1, contract.groupSetting.dueDays[j]])
            }

            $(prefix + "start_date").datebox({
                mode: "calbox",
                afterToday: true,
                blackDatesRec: [payableDays],
                defaultValue: new Date(),
                useFocus: true,
                useButton: false
            })


            //css fix for calendar widget positioning

            $(document).on("click", prefix + "start_date", function() {
                var viewportwidth = $(window).width();
                var datepickerwidth = $("#ui-datepicker-div").width();
                var leftpos = (viewportwidth - datepickerwidth) / 2; //Standard centering method
                $("#ui-datepicker-div").css({
                    left: leftpos,
                    position: 'absolute'
                });
            });

            //input contract id into hidden field

            jQuery(prefix + "contractId").val(id);

            if (debug) {
                console.log(contract)
            }

            //get paid for array dates to setup select box

            var paidFor = paidForArr[contract.id]
            $(prefix + "paidFor").html("");
            $.each(paidFor, function(index, value) {
                if (contract.payment) {
                    var a = ""
                    if (index == contract.payment.paidFor) {
                        a = "selected"
                    }
                }
                $(prefix + "paidFor").append("<option value='" + index + "'" + a + ">" + value + "</option>")
            })

            //fill in payToName, rent amount, and address
            if (contract.payment) {
                $(prefix + "amount").val(contract.payment.amount);
                $(prefix + "amountOther").val(contract.payment.amountOther);
            } else {
                $(prefix + "amount").val(contract.rent)
            }
            $("#payTo").html(contract.payToName);
            $("#contractAddress").html((contract.property.propertyAddress.number + " " + contract.property.propertyAddress.street + " " + contract.property.propertyAddress.district).replace("undefined", "").replace(/  /g, ' '))


            //get due date

            $(prefix + "dueDate").html("")
            for (i = 1; i < 32; i++) {
                a = "";
                if (contract.payment) {
                    if (i == dueDate) {
                        a = " selected"
                    }
                } else if (i == (new Date()).getDate()) {
                    a = " selected"
                }
                $(prefix + "dueDate").append("<option value='" + i + "' " + a + ">" + i + "</option>")
            }


            if (contract.payment) {

                //different button text

                $(prefix + "id").val(contract.payment.id); //make sure we edit an existing payment

                $("#payRentBttn").html("SAVE CHANGES")

                $(prefix + "paymentAccount").val(contract.payment.paymentAccountId)

                if (contract.payment.status == "active") {
                    if (contract.payment.type == "recurring") {
                        $(prefix + "type").val("recurring")
                        formType(false)
                    } else {
                        $(prefix + "type").val("one_time")
                        formType(true);
                    }
                }

                $(prefix + "type").selectmenu("refresh")


                $(prefix + "startMonth").val(contract.payment.startMonth);
                $(prefix + "startYear").val(contract.payment.startYear);

                $(prefix + "ends_0").attr("checked", true)
                $(prefix + "ends_1").attr("checked", false)

                if (contract.payment.endMonth) {
                    $(prefix + "endMonth").val(contract.payment.endMonth);
                    $(prefix + "endYear").val(contract.payment.endYear);
                    whenCancelled(true)
                    $(prefix + "ends_0").attr("checked", false)
                    $(prefix + "ends_1").attr("checked", true)
                } else {
                    whenCancelled(false)
                    $(prefix + "ends_0").attr("checked", true)
                    $(prefix + "ends_1").attr("checked", false)
                }


                $(prefix + "start_date").val(mm + "/" + (contract.payment.dueDate) + "/" + yyyy)


            } else {

                $(prefix + "paymentAccount").val($(prefix + "paymentAccount option:first").val())

                $("#payRentBttn").html("PAY RENT")


                $(prefix + "start_date").val(mm + "/" + ((new Date()).getDate()) + "/" + yyyy)

                formType(true)
                $(prefix + "type").val("one_time")
                $(prefix + "startMonth").val((new Date()).getMonth() + 1)
                $(prefix + "startYear").val((new Date()).getFullYear())

                $(prefix + "endMonth").val((new Date()).getMonth() + 1)
                $(prefix + "endYear").val((new Date()).getFullYear())

                $(prefix + "ends_0").attr("checked", true)
                $(prefix + "ends_1").attr("checked", false)
                whenCancelled(false)

                if (payAccounts.length > 0) {
                    $(prefix + "paymentAccount").val(payAccounts[0].id)
                }
            }

            //refresh all form items since jQM doesn't do this automatically

            $(prefix + "type").selectmenu("refresh");
            $(prefix + "startYear").selectmenu("refresh");
            $(prefix + "startMonth").selectmenu("refresh");
            $(prefix + "paidFor").selectmenu("refresh")
            $(prefix + "endMonth").selectmenu("refresh");
            $(prefix + "endYear").selectmenu("refresh");
            $(prefix + "dueDate").selectmenu("refresh")
            $(prefix + "paymentAccount").selectmenu("refresh")
            $(prefix + "ends_0").checkboxradio("refresh");
            $(prefix + "ends_1").checkboxradio("refresh");


            $(prefix + "paidTo").val(contract.paidTo)


            $("#revEnds").html(contract.finishAt)

            break;
        }
    }

    updateTotal()

}

function reddenInput(o) {
    $(o).css("border", "4px solid rgb(255,155,155)")
}

function clearRedFields() {
    $.each(fields, function(index, value) {
        $(prefix + value).css("border", "0px")
    })
}

fields = ["amount", "amountOther"]

function validatePayment() {
    clearRedFields()
    var passed = true;
    var msg = "";
    if ($(prefix + fields[0]).val() <= 0) {
        passed = false;
        reddenInput($(prefix + "amount"))
        msg += "Amount should be greater than zero."
    }
    if ($(prefix + fields[1]).val() < 0) {
        passed = false;
        reddenInput($(prefix + "amountOther"))
        msg += "Other amount should not be less than zero."
    }
    //if($(prefix+"paymentAccount").val)
    if (!passed) {
        msg = "Please check that all fields are filled correctly. " + msg
        $("#errorMsg").html(msg)
        $("#errorMsg").show()
    }
    return passed;
}

//create review page
function createReview() {

    if (validatePayment()) {

        total = updateTotal();
        contractsArr = $.map(contractsJson, function(el) {
            return el
        });
        for (i = 0; i < contractsArr.length; i++) {
            if (contractsArr[i].id == globalContractId) {

                var contract = contractsArr[i];
                var method = "";
                var fee = 0.00;

                total = updateTotal(contract);

                $.each(payAccounts, function(i, localPaymentAccountId) {
                    if (localPaymentAccountId.id == parseInt($(prefix + "paymentAccount").val())) {
                        method = localPaymentAccountId.type;
                    }
                });

                if ('card' == method) {
                    fee = parseFloat(contract.groupSettings.feeCC) / 100 * total;
                    $("#revTechFeeCont").show();
                } else if ('bank' == method) {
                    if (contract.groupSettings.isPassedACH) {
                        fee = parseFloat(contract.groupSettings.feeACH);
                        $("#revTechFeeCont").show();
                    } else {
                        fee = parseFloat(0);
                        $("#revTechFeeCont").hide();
                    }
                } else if ('debit_card' == method) {
                    fee = parseFloat(contract.groupSettings.feeDC);
                    var feeType = contract.groupSettings.typeFeeDC;
                    if ('percentage' == feeType) {
                        fee = fee / 100 * total;
                    }
                    $("#revTechFeeCont").show();
                } else {
                    fee = parseFloat(0);
                    $("#revTechFeeCont").hide();
                }

                $("#revTechFee").html(accounting.formatNumber(fee, 2));
            }
        }

        $(prefix + "paymentAccountId").val($(prefix + "paymentAccount").val());
        $("#contract_id").val($(prefix + "contractId").val());
        if (currentPaymentForm === paymentBalanceForm) {
            $("#revRentAmountBox").hide();
            $("#revOtherAmountBox").hide();
        } else {
            $("#revRentAmount").html(accounting.formatNumber($(prefix + "amount").val(), 2));
            $("#revRentAmountBox").show();

            $("#revOtherAmount").html(accounting.formatNumber($(prefix + "amountOther").val(), 2));
            $("#revOtherAmountBox").show();
        }
        var sum = accounting.formatNumber((total + fee), 2);

        $("#revTotalAmount").html(sum);
        $("#revMethod").html($(prefix + "paymentAccount option:selected").html());

        if ($(prefix + "type").val() == "one_time") {
            var a = "One time"
            $('#revEndsLabel').hide()
            $('#revEnds').hide()

            //convert to readable date
            $("#revSendOn").html(new Date($(prefix + "start_date").val()).toDateString());

            //fix some things-- if one time, change start month/day to match startdate
            d = $(prefix + "start_date").val().split("/")
            $(prefix + "frequency").val("monthly")
            $(prefix + "dueDate").val(parseInt(d[1]))
            if (d[1].charAt(0) == "0" && d[1].length == 2) {
                $(prefix + "dueDate").val(d[1].charAt(1))
            }
            $(prefix + "startMonth").val(d[0].replace("0", ""))
            $(prefix + "startYear").val(d[2]);
            $(prefix + "startYear").selectmenu("refresh");


            $(prefix + "ends").val('cancelled');
        } else {


            if ($(prefix + "frequency").val() == "monthly") {
                $("#revSendOn").html("Day " + $(prefix + "dueDate").val() + " of Each Month");
            } else {
                $("#revSendOn").html("Last Day of Each Month");
                $(prefix + "dueDate").val(31)
            }

            d = $(prefix + "startMonth").val() + "/" + $(prefix + "dueDate").val() + "/" + $(prefix + "startYear").val();
            $(prefix + "start_date").val(d)

            if ($(prefix + "ends_0").is(':checked')) {
                $('#revEnds').html(" When Cancelled")
            } else {
                $('#revEnds').html($(prefix + "endMonth").val() + "/" + $(prefix + "endYear").val())
            }
            a = "Recurring"
        }

        $("#revRecurring").html(a);
        $(prefix + "paymentAccount").prop('disabled', true); //w/o submission will fail
        $.mobile.changePage('#review')
    }
}

//when recurring, show additional fields. when one_time, hide appropriately


recurringFormIds = [
    "frequency_row",
    "startMonth_row",
    "ends_row",
    "endMonth",
    "endYear"
];
onetimeFormIds=[
    "start_date_row"
];

function formType(isRecurring) {
    $.each(recurringFormIds, function(index, value) {
        if (isRecurring) {
            $(prefix + value).hide();
            $(prefix + value).prop("disabled", true);
        } else {
            $(prefix + value).show();
            $(prefix + value).prop("disabled", false);
        }
    });
    $.each(onetimeFormIds,function(index,value){
        if (!isRecurring) {
            $(prefix + value).hide();
            $(prefix + value).prop( "disabled", true);
        }else{
            $(prefix + value).show();
            $(prefix + value).prop( "disabled", false);
        }
    });

    $(prefix + "ends_0").attr("checked", true);
    $(prefix + "ends_1").attr("checked", false);
    whenCancelled(false);

    $(prefix + "ends_0").checkboxradio("refresh");
    $(prefix + "ends_1").checkboxradio("refresh");
}

//hide month/year selection if recurring payment set to complete when cancelled
cancelled=[
    "endMonth",
    "endYear"
];

function whenCancelled(isCancelled) { //isCancelled is recurring event set to when cancelled
    $.each(cancelled, function(index, value) {
        if (isCancelled) {
            $(prefix + value).selectmenu('enable');
        } else {
            $(prefix + value).selectmenu('disable');
        }
    })
}

//hide dueDate box

function freqHide(isOneTime) {
    if (!isOneTime) { //hide
        $(prefix + "dueDate_box").hide()
    } else {
        $(prefix + "dueDate_box").show()
    }
}


//update rent/other total

var total=0;
function updateTotal(contract) {
    if (currentPaymentForm === paymentBalanceForm && contract) {
        total = parseFloat(contract.integrated_balance);
    } else {
        var amount = parseFloat($(prefix + "amount").val())
        var other = parseFloat($(prefix + "amountOther").val())
        total = 0;
        if (!isNaN(amount)) {
            total += amount;
        }
        if (!isNaN(other)) {
            total += other;
        }
    }

    $("#total").html(accounting.formatNumber(total, 2));
    $(prefix + "total_view").html('$' + accounting.formatNumber(total, 2));
    $(prefix + "total").val(total);

    return total;
}

//submit form with ajax to display dialog when completed

errorMsgs = [];

function traverseErrorMsgs(o) { //traverse nested error messages
    for (i in o) {
        if (typeof(o[i]) == "object") {
            traverseErrorMsgs(o[i]);
        } else {
            errorMsgs.push(o[i]);
            //w(o[i])
        }
    }
}

function addNewPaymentSource(formObj) {

    if (!saving) { //it's a new account
        $('#' + accountPrefix + 'submit').prop('disabled', true);
        $("#loader").show();
        $.ajax({
            url: 'checkout/source',
            data: $(formObj).serialize(),
            type: 'post',
            success: function(result) {
                $("#loader").hide();
                $('#' + accountPrefix + 'submit').prop('disabled', false);
                a = result
                if (debug) {
                    console.log(result)
                }
                if (result.success != true) { //handle error message and take user back to pay/edit page
                    msg = "";
                    errorMsgs = [];
                    traverseErrorMsgs(result)
                    $.each(errorMsgs, function(i, k) {
                        msg += k + "<br>"
                    })

                    $("#sourceErrorMsg").html(msg)
                    $("#sourceErrorMsg").show()
                    $('body').animate({ scrollTop: '0' }, 0)
                } else { //we are successful! display dialog, refresh page to update information
                    $("#sourceErrorMsg").hide()
                    $.mobile.changePage('#pay')
                    $(prefix + "paymentAccount").append("<option value='" + result.paymentAccount.id + "' selected>" + result.paymentAccount.name + "</option>")
                    $(prefix + "paymentAccount").selectmenu("refresh")
                    updateLocalPaymentSource(result.paymentAccount)

                }
            },
            error: function(request, error) { //ajax error!
                $("#loader").hide();
                $('#' + accountPrefix + 'submit').prop('disabled', false);
                msg = "An error occurred. (" + error + ")"
                $("#sourceErrorMsg").html(msg)
                $("#sourceErrorMsg").show()
                $('body').animate({ scrollTop: '0' }, 0)
            }
        });

    } else { //overriding an existing one
        //   http://dev-nr.renttrack.com/sources/save
        $.ajax({
            url: 'sources/save',
            data: $(formObj).serialize(),
            type: 'post',
            async: 'true',

            success: function(result) {
                a = result
                if (debug) {
                    console.log(result)
                }
                if (result.success != true) { //handle error message and take user back to pay/edit page
                    msg = "";
                    /*
                     $.each(result, function (i, k) {
                     $.each(k, function (h, j) {
                     msg += j + "<br>"
                     })
                     })
                     $("#sourceErrorMsg").html(msg)
                     $("#sourceErrorMsg").show()
                     */
                } else { //we are successful! display dialog, refresh page to update information
                    // $("#sourceErrorMsg").hide()
                    $.mobile.changePage('#sources')
                    updateLocalPaymentSource(result.paymentAccount)
                }
            },
            error: function(request, error) { //ajax error!
                /*
                msg = "An error occurred. (" + error + ")"
                $("#sourceErrorMsg").html(msg)
                $("#sourceErrorMsg").show()
                */
                $('body').animate({ scrollTop: '0' }, 0)
                 msg = "An error occurred. (" + error + ")"
                 $("#sourceErrorMsg").html(msg)
                 $("#sourceErrorMsg").show()
                 $('body').animate({ scrollTop: '0' }, 0)
            }
        });


    }
}

function unlockPaymentSource() {
    $(prefix + 'paymentAccount').prop('disabled', false);
}

function updateLocalPaymentSource(entry) {
    i = 0;
    while (payAccounts[i] != undefined && payAccounts[i].id != entry.id) {
        i++;
    }
    payAccounts[i] = entry;
}

var submitting = false;

function submitForm() {
    if (submitting) {
        return;
    }
    submitting = true;
    var data =  $(currentPaymentForm).serializeArray();
    data.push({
        'name': 'contract_id',
        'value': $("#contract_id").val()
    });

    $('#payRentBttn').prop('disabled', true);

    $.ajax({
        url: 'checkout/exec',
        data: data,
        type: 'post',
        async: 'true',

        success: function(result) {
            a = result
            if (debug) {
                console.log(result)
            }
            if (result.success != true) { //handle error message and take user back to pay/edit page
                $.mobile.changePage('#pay')
                msg = "";
                errorMsgs = [];
                traverseErrorMsgs(result);
                $.each(errorMsgs, function (i, k) {
                    msg += k + "<br>"
                })
                $("#errorMsg").html(msg)
                $("#errorMsg").show()
            } else { //we are successful! display dialog, refresh page to update information
                $("#popupBasic").popup("open")
                $("#errorMsg").hide()
                setTimeout(function() {
                    window.location.reload(true)
                }, 500)
            }
            submitting = false;
            $('#payRentBttn').prop('disabled', false);
        },
        error: function(request, error) { //ajax error!
            $.mobile.changePage('#pay');
            msg = "An error occurred. (" + error + ")";
            $("#errorMsg").html(msg);
            $("#errorMsg").show();
            submitting = false;
            $('#payRentBttn').prop('disabled', false);
        }
    });
}

String.prototype.capitalizeFirstLetter = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}


function getHistory(historyId) {
    if (historyId) {
        $.getJSON("/ajax/tenant_payments/1/" + historyId + "/99999", function(data) {
            if (data.tenantPayments.length > 0) {
                var htmlStr = '<tr><td colspan="3" class="headingAddress"> <i class="fa fa-home"></i> <b>' + data.tenantPayments[0].property + '</b> </td> </tr>';
                $.each(data.tenantPayments, function(index, entry) {
                    htmlStr += "<tr><td>" + entry.date + "</td><td>$" + entry.total + "</td><td>" + entry.status.toString().capitalizeFirstLetter() + "</td></tr>";
                })
                //orderBox(desc,address,status,date,contractId,paymentType)
                loadOrderTable(data.tenantPayments, data.tenantPayments[0].property, historyId)

                $("#paymentHistoryTable" + historyId).append(htmlStr)
                $("#paymentHistoryTableI" + historyId).append(htmlStr)

            } else {
                //htmlStr='Nothing here yet!'
            }

            $(".loadingPaymentHistory").hide()

            if (historyId == lastHistory) {
                $("#loader").hide()
                $("#payments").show()
            }
        })
    }
}

function getOrdinal(n) {
    var s = ["th", "st", "nd", "rd"],
        v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
}

function loadOrderTable(tenantPayments, address, contractId) { //HISTORICAL ENTRIES
    $.each(tenantPayments, function(index, entry) {
        entryDate = new Date(entry.date)
        curDate = new Date()
        paymentType = "bank"
        if (entry.type == "credit-card") {
            paymentType = "card"
        }
        date = entry.date.split("/")
        date = date[2] + "-" + date[0] + "-" + date[1]
        if (new Date(curDate - entryDate).getTime() < 259200000) { //check for within 3 days
            if (entry.status.toString() == "pending") {
                orderBox("<b>Payment En Route</b> - $" + entry.total, address, entry.status.toString(), date, contractId, paymentType)
            }
            if (entry.status.toString() == "complete") {
                orderBox("Payment Received - $" + entry.total, address, entry.status.toString(), date, contractId, paymentType)
            }
            //orderBox(desc,address,status,date,contractId,paymentType)
        }
    })
}

function loadPaymentTable() { //CURRENT ENTRIES
    $.each(contractsJson, function(index, entry) {
        address = entry.property.propertyAddress.number + " " + entry.property.propertyAddress.street
        if (entry.payment) {
            payAccountType = "card";
            $.each(payAccounts, function(k, v) {
                if (v.id == entry.payment.paymentAccountId) {
                    payAccountType = v.type
                }
            })
            date = entry.payment.paidDate.split("/")
            date = date[2] + "-" + date[0] + "-" + date[1]
            orderBox("<b>" + entry.payment.type.replace("_", " ").replace("time", "Time Rent") + " Payment Scheduled</b> - $" + entry.payment.total, address, "", date, entry.id, payAccountType, true);
            //desc,address,status,date,contractId,paymentType
        }
        if (entry.customPayments) {
            $.each(entry.customPayments, function (k, customPayment) {
                orderBox(
                    "<b>" + customPayment.depositAccountType.replace('_', ' ') + " Payment Scheduled</b> - $" + customPayment.total,
                    address,
                    '',
                    customPayment.startYear + '-' + padZero(customPayment.startMonth) + '-' + padZero(customPayment.dueDate),
                    entry.id,
                    customPayment.paymentAccountType);
            });
        }
    })
}

function getDepositDate(contractId, date, paymentType, recId) {
    //ajax/deliveryDate/2/2015-10-29/card
    $.getJSON("/ajax/deliveryDate/" + contractId + "/" + date.replace(/\//g, "-") + "/" + paymentType, function(data) {
        s = data.date;
        ourDate = formatDate(s.split(" ")[0])
        $("#" + recId).html(ourDate); //substring without time and then change hyphens to non-breaking hyphens
    })
}

function formatDate(s) { //takes yyyy-dd-mm and converts to mm/dd/yyyy
    d = s.split("-")
    return d[1] + "/" + d[2] + "/" + d[0];
}

function orderBox(desc, address, status, date, contractId, paymentType, isPayment) { //status = complete, pending, error, "" (scheduled but no order), refund
    randId = "a" + Math.floor(Math.random() * 9999999)
    if (contractId != -1) {
        getDepositDate(contractId, date, paymentType, randId)
    }
    $("#intro").hide() // we don't need default box
    htmlStr = "";
    //htmlStr += "<tr><td>" + entry.date + "</td><td>$" + entry.total + "</td><td>" + entry.status.toString().capitalizeFirstLetter() + "</td></tr>";
    if(true === isPayment) {
        htmlStr += '<div class="paymentBox" onclick="navigateToContract(' + contractId + ')">';
    } else {
        htmlStr += '<div class="paymentBox">';
    }
    htmlStr += '<div class="addressBox"><i class="fa fa-home"></i> ' + address + '</div>'

    htmlStr += '<div class="paymentStatusBox">'
    htmlStr += '<div class="paymentStatusCont">'
    if (status != "") {
        htmlStr += '<span style="left: 0px; color: #6BAB1B;"><i class="fa fa-usd"></i><br>Charged<br>' + formatDate(date) + '</span>'
    } else {
        htmlStr += '<span style="left: 0px;"><i class="fa fa-usd"></i><br>Charge on<br>' + formatDate(date) + '<br></span>'
    }
    if (status == "pending" || status == "complete") {
        htmlStr += '<span style="left: 48px; color: #6BAB1B;">- - - - - - - - <i class="fa fa-arrow-right"></i>- - - - - - -</span>'
    } else if (status == "error") {
        htmlStr += '<span style="left: 48px; color: red;">- - - - - - - - <i class="fa fa-times">- - - - - - -</i></span>'
    } else {
        htmlStr += '<span style="left: 48px;">- - - - - - - - <i class="fa fa-arrow-right"></i>- - - - - - -</span>'
    }
    if (status == "complete") {
        htmlStr += '<span style="left: 178px; color: #6BAB1B; text-align: left;">- - - <i class="fa fa-check"></i><br>Received<br><span id="' + randId + '"></span></span>'
    } else {
        htmlStr += '<span style="left: 178px; text-align: left; width: 100px;">- - - <i class="fa fa-circle-o"></i><br>Receive on<br><span id="' + randId + '"></span></span>'
    }
    htmlStr += '</div>'
    htmlStr += '</div>'
    htmlStr += '<div style="color: #6BAB1B; padding: 5px; text-shadow: none; text-align: center;text-transform: capitalize;">'
    htmlStr += desc
    htmlStr += '</div>'
    htmlStr += '</div></div>'
    $("#payments").append(htmlStr);
}

function navigateToContract(contractId) {
    $.mobile.changePage("#contract" + contractId, {transition: 'slide'});
}

function renderFeeForPayment() {
    var contract = getContractById(globalContractId);

    $('#payment-type-with-fee').find('.payment-fee-value').each(function () {
        $(this).text(getFeeForContract($(this).attr('data-payment-type'), contract.groupSettings));
    });
}

function getFeeForContract(method, groupSettings) {
    if ('card' == method) {
        return parseFloat(groupSettings.feeCC ? groupSettings.feeCC : 0) + '%';
    } else if ('bank' == method) {
        return '$' + parseFloat(groupSettings.isPassedACH ? groupSettings.feeACH : 0.00).toFixed(2);
    } else if ('debit_card' == method) {
        if ('percentage' == groupSettings.typeFeeDC) {
            return parseFloat(groupSettings.feeDC ? groupSettings.feeDC : 0) + '%';
        } else {
            return '$' + parseFloat(groupSettings.feeDC ? groupSettings.feeDC : 0.00).toFixed(2);
        }
    } else {
        return parseFloat(0);
    }
}

function getContractById(id) {
    if (null !== contractCollection && undefined !== contractCollection[id]) {
        return contractCollection[id];
    }
    return null;
}

function padZero(n) {
    return n < 10 ? '0' + n : n
}