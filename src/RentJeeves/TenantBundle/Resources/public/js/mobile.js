$(document).ready(function(){

    $.mobile.changePage('#main')    //users should always start at #main

    prefix="rentjeeves_checkoutbundle_paymenttype_";
    subs=[
        ["amount_label","Rent Amount"], ["paidFor_label","for month of*"],
        ["amountOther_label","Other Amount (Late Fees, etc.)"],
        ["paymentAccount_label","Payment Source"]
    ];

    $.each(subs,function(i,k){
        $("#"+prefix+k[0]).html(k[1])
    })



    $(document).delegate('#opendialog', 'click', function() {
        $('<div>').simpledialog2({
            mode: 'blank',
            headerText: false,
            headerButton: false,
            headerClose: false,
            blankContent :
            "<div style='padding: 5px;'>Are you sure you want to cancel this payment?</div>"+
            "<br><a href='' data-ajax='false' id='confirmCancel' style='text-decoration: none;'>"+
            "<button style='color: white; background: rgb(232,65,65);text-shadow:none; border-radius: 7px;'>Cancel Payment</button></a>" +
            "<a rel='close' href='#main' data-ajax='false' style='text-decoration: none;'><button style='text-shadow:none; border-radius: 7px;'>Back</button></a>"
        })
    })

    $( ".selector" ).loader({
        disabled: true
    });

    h=$.mobile.getScreenHeight();
    h-=60;
    $(".ui-content").css('min-height',h+'px');
    for (i = 0; i < contractsJson.length;i++) {
        contract = contractsJson[i]
        $("#contractPayTo" + contract.id).html(contract.payToName)
        if (contract.payment) {

            $("#cancel" + contract.id).attr("onclick", "setTimeout(function(){cancelPayment(" + contract.payment.id + "); $('.ui-dialog').hide()},50)")
            //settimeout hide dialog fixes weird bug with simpledialog

            $.each(payAccounts, function (i, k) {
                if (k.id == contract.payment.paymentAccountId) {
                    $("#contractFromAcc" + contract.id).html(k.name)
                }
            })
        } else {
            $("#contractFromAccLabel" + contract.id).hide()
        }
        /*
         contract object does not have payToName
         so we can just use the JSON object to put this in.
         */
    }


    $("<span>Total to Pay: </span>$<span id='total'></span> ").insertAfter($("#rentjeeves_checkoutbundle_paymenttype_amountOther"))
    $("#rentjeeves_checkoutbundle_paymenttype_total_row").hide()
    $("#rentjeeves_checkoutbundle_paymenttype_amount").onchange=updateTotal;
    $("#rentjeeves_checkoutbundle_paymenttype_amountOther").onchange=updateTotal
    $("#rentjeeves_checkoutbundle_paymenttype_type_label").html("One-Time or Recurring<sup>*</sup>");


})

function cancelPayment(i){
    $("#confirmCancel").attr("href","/checkout/cancel/"+i);
}

var gId=-1;

function setupPayForm(id) {

    gId=id;

    $("#errorMsg").html("")
    $("#errorMsg").hide()

    $(".fields-box").css('padding-bottom','10px')

    $("#backToContract").attr("href","#contract"+id)

    var today = new Date();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();


    if(mm<10) {
        mm='0'+mm
    }

    $("#rentjeeves_checkoutbundle_paymenttype_amountOther").val("0.00")

    for (i = 0; i < contractsJson.length;i++) {
        if(contractsJson[i].id==id){

            contract=contractsJson[i]

            if(isVerified!='passed' && contract.isPidVerificationSkipped==false){
                $.mobile.changePage('#notVerified')
                break;
            }else{
                $.mobile.changePage('#pay')
            }


            a=[]
            for(j=0;j<contract.groupSetting.dueDays.length;j++){
                a.push([-1,-1,contract.groupSetting.dueDays[j]])
            }

            $('#rentjeeves_checkoutbundle_paymenttype_start_date').datebox({
                mode: "calbox",
                afterToday: true,
                blackDatesRec:[a],
                defaultValue: new Date(),
                useFocus :true,
                useButton:false


            })

            $(document).on("click","#rentjeeves_checkoutbundle_paymenttype_start_date",function(){
                var viewportwidth = $(window).width();
                var datepickerwidth = $("#ui-datepicker-div").width();
                var leftpos = (viewportwidth - datepickerwidth)/2; //Standard centering method
                $("#ui-datepicker-div").css({left: leftpos,position:'absolute'});
            });

            jQuery('#rentjeeves_checkoutbundle_paymenttype_contractId').val(id);

            console.log(contract)
            paidFor=paidForArr[contract.id]
            $("#rentjeeves_checkoutbundle_paymenttype_paidFor").html("");
            $.each(paidFor,function(index,value){
                if(contract.payment) {
                    a = ""
                    if (index == contract.payment.paidFor) {
                        a = "selected"
                    }
                }
                $("#rentjeeves_checkoutbundle_paymenttype_paidFor").append("<option value='"+index+"'"+a+">"+value+"</option>")
            })

            $("#rentjeeves_checkoutbundle_paymenttype_amount").val(contract.rent)
            $('#rentjeeves_checkoutbundle_paymenttype_total').val(contract.rent)
            $("#payTo").html(contract.payToName);
            $("#contractAddress").html(contract.property.number+" "+contract.property.street+", "+contract.property.district+" "+contract.unit.name)

            $("#rentjeeves_checkoutbundle_paymenttype_dueDate").html("")
            for (i = 1; i < 32; i++) {
                a = "";
                if(contract.payment){
                    if (i == contract.payment.dueDate) {
                        a = " selected"
                    }
                }else if(i==(new Date()).getDate()){
                    a=" selected"
                }
                $("#rentjeeves_checkoutbundle_paymenttype_dueDate").append("<option value='"+i+"' " + a + ">" + i + "</option>")
            }


            if(contract.payment) {

                $("#payRentBttn").html("SAVE CHANGES")

                $("#rentjeeves_checkoutbundle_paymenttype_paymentAccount").val(contract.payment.paymentAccountId)

                if (contract.payment.status == "active") {
                    if (contract.payment.type == "recurring") {
                        $("#rentjeeves_checkoutbundle_paymenttype_type").val("recurring")
                        formType(false)
                    } else {
                        $("#rentjeeves_checkoutbundle_paymenttype_type").val("one_time")
                        formType(true);
                    }
                }

                $("#rentjeeves_checkoutbundle_paymenttype_type").selectmenu("refresh")


                $("#rentjeeves_checkoutbundle_paymenttype_startMonth").val(contract.payment.startMonth);
                $("#rentjeeves_checkoutbundle_paymenttype_startYear").val(contract.payment.startYear);
                if(contract.payment.endMonth){
                    $("#rentjeeves_checkoutbundle_paymenttype_endMonth").val(contract.payment.endMonth);
                    $("#rentjeeves_checkoutbundle_paymenttype_endYear").val(contract.payment.endYear);
                    whenCancelled(true)
                    $("#rentjeeves_checkoutbundle_paymenttype_ends_0").attr("checked",false)
                    $("#rentjeeves_checkoutbundle_paymenttype_ends_1").attr("checked",true)
                }else{
                    whenCancelled(false)
                    $("#rentjeeves_checkoutbundle_paymenttype_ends_0").attr("checked",true)
                    $("#rentjeeves_checkoutbundle_paymenttype_ends_1").attr("checked",false)
                }


                $("#rentjeeves_checkoutbundle_paymenttype_start_date").val(mm+"/"+(contract.payment.dueDate)+"/"+yyyy)


            }else{

                $("#payRentBttn").html("PAY RENT")


                $("#rentjeeves_checkoutbundle_paymenttype_start_date").val(mm+"/"+((new Date()).getDate())+"/"+yyyy)

                formType(true)
                $("#rentjeeves_checkoutbundle_paymenttype_type").val("one_time")
                $("#rentjeeves_checkoutbundle_paymenttype_startMonth").val((new Date()).getMonth()+1)
                $("#rentjeeves_checkoutbundle_paymenttype_startYear").val((new Date()).getFullYear())

                $("#rentjeeves_checkoutbundle_paymenttype_endMonth").val((new Date()).getMonth()+1)
                $("#rentjeeves_checkoutbundle_paymenttype_endYear").val((new Date()).getFullYear())

                $("#rentjeeves_checkoutbundle_paymenttype_ends_0").attr("checked",true)
                $("#rentjeeves_checkoutbundle_paymenttype_ends_1").attr("checked",false)
                whenCancelled(false)


                $("#rentjeeves_checkoutbundle_paymenttype_paymentAccount").val(payAccounts[0].id)

            }

            $("#rentjeeves_checkoutbundle_paymenttype_type").selectmenu("refresh");
            $("#rentjeeves_checkoutbundle_paymenttype_startYear").selectmenu("refresh");
            $("#rentjeeves_checkoutbundle_paymenttype_startMonth").selectmenu("refresh");
            $("#rentjeeves_checkoutbundle_paymenttype_paidFor").selectmenu("refresh")
            $("#rentjeeves_checkoutbundle_paymenttype_endMonth").selectmenu("refresh");
            $("#rentjeeves_checkoutbundle_paymenttype_endYear").selectmenu("refresh");
            $("#rentjeeves_checkoutbundle_paymenttype_dueDate").selectmenu("refresh")
            $("#rentjeeves_checkoutbundle_paymenttype_paymentAccount").selectmenu("refresh")
            $("#rentjeeves_checkoutbundle_paymenttype_ends_0").checkboxradio("refresh");
            $("#rentjeeves_checkoutbundle_paymenttype_ends_1").checkboxradio("refresh");


            $("#rentjeeves_checkoutbundle_paymenttype_paidTo").val(contract.paidTo)


            $("#revEnds").html(contract.finishAt)

            break;
        }
    }

    updateTotal()

}






function createReview(){

    updateTotal();

    for (i = 0; i < contractsJson.length;i++) {
        if (contractsJson[i].id == gId) {


            contract = contractsJson[i]

            method=""
            $.each(payAccounts,function(i,k){
                if(k.id==parseInt($("#rentjeeves_checkoutbundle_paymenttype_paymentAccount").val())){
                    method= k.type;
                }})
            var fee = 0.00;
            if ('card' == method) {
                fee = parseFloat(contract.depositAccount.feeCC) / 100 * total;
                $("#revTechFeeCont").show()
            } else if ('bank' == method) {
                fee = parseFloat(contract.depositAccount.feeACH);
                $("#revTechFeeCont").hide()
            }

            $("#revTechFee").html(accounting.formatNumber(fee,2))

        }
    }

    total=accounting.formatNumber((total+fee),2);


    $("#rentjeeves_checkoutbundle_paymenttype_paymentAccountId").val($("#rentjeeves_checkoutbundle_paymenttype_paymentAccount").val());
    $("#contract_id").val($("#rentjeeves_checkoutbundle_paymenttype_contractId").val());
    $("#revRentAmount").html(accounting.formatNumber($("#rentjeeves_checkoutbundle_paymenttype_amount").val(),2));
    $("#revOtherAmount").html(accounting.formatNumber($("#rentjeeves_checkoutbundle_paymenttype_amountOther").val(),2));
    $("#revTotalAmount").html(total);
    $("#revMethod").html($("#rentjeeves_checkoutbundle_paymenttype_paymentAccount option:selected").html());

    if($('#rentjeeves_checkoutbundle_paymenttype_type').val()=="one_time") {
        a="One time"
        $('#revEndsLabel').hide()
        $('#revEnds').hide()

        //convert to readable date
        $("#revSendOn").html(new Date($("#rentjeeves_checkoutbundle_paymenttype_start_date").val()).toDateString());

        //fix some things-- if one time, change start month/day to match startdate
        d=$("#rentjeeves_checkoutbundle_paymenttype_start_date").val().split("/")
        $("#rentjeeves_checkoutbundle_paymenttype_frequency").val("monthly")
        $("#rentjeeves_checkoutbundle_paymenttype_dueDate").val(parseInt(d[1].replace("0","")))
        $("#rentjeeves_checkoutbundle_paymenttype_startMonth").val(parseInt(d[0].replace("0","")))
        $("#rentjeeves_checkoutbundle_paymenttype_startYear").val(parseInt(d[2]));
        $("#rentjeeves_checkoutbundle_paymenttype_startYear").selectmenu("refresh");


        $("#rentjeeves_checkoutbundle_paymenttype_ends").val('cancelled');
    }else{


        if($("#rentjeeves_checkoutbundle_paymenttype_frequency").val()=="monthly"){
            $("#revSendOn").html("Day "+$("#rentjeeves_checkoutbundle_paymenttype_dueDate").val()+" of each month");
        }else{
            $("#revSendOn").html("Last day of month");
            $("#rentjeeves_checkoutbundle_paymenttype_dueDate").val(31)
        }

        d=$("#rentjeeves_checkoutbundle_paymenttype_startMonth").val()+"/"+$("#rentjeeves_checkoutbundle_paymenttype_dueDate").val()+"/"+$("#rentjeeves_checkoutbundle_paymenttype_startYear").val();
        $("#rentjeeves_checkoutbundle_paymenttype_start_date").val(d)

        if($("#rentjeeves_checkoutbundle_paymenttype_ends_0").val()!="on"){
            $('#revEnds').html(" when cancelled")
        }else{
            $('#revEnds').html($("#rentjeeves_checkoutbundle_paymenttype_endMonth").val()+"/"+$("#rentjeeves_checkoutbundle_paymenttype_endYear").val())
        }
        a="Recurring"
    }

    $("#revRecurring").html(a);
    $("#rentjeeves_checkoutbundle_paymenttype_paymentAccount").prop('disabled',true);  //w/o submission will fail

}
recurringFormIds=[
    "rentjeeves_checkoutbundle_paymenttype_frequency_row",
    "rentjeeves_checkoutbundle_paymenttype_startMonth_row",
    "rentjeeves_checkoutbundle_paymenttype_ends_row",
    "rentjeeves_checkoutbundle_paymenttype_endMonth",
    "rentjeeves_checkoutbundle_paymenttype_endYear"
]
onetimeFormIds=[
    "rentjeeves_checkoutbundle_paymenttype_start_date_row"
]

function formType(i){
    $.each(recurringFormIds,function(index,value){
        if(i) {
            $('#' + value).hide();
            $('#' + value).prop( "disabled", true );
        }else{
            $('#' + value).show();
            $('#' + value).prop( "disabled", false );
        }
    })
    $.each(onetimeFormIds,function(index,value){
        if(!i) {
            $('#' + value).hide();
            $('#' + value).prop( "disabled", true );
        }else{
            $('#' + value).show();
            $('#' + value).prop( "disabled", false );
        }
    })

    $("#rentjeeves_checkoutbundle_paymenttype_ends_0").attr("checked",true)
    $("#rentjeeves_checkoutbundle_paymenttype_ends_1").attr("checked",false)
    whenCancelled(false)

    $("#rentjeeves_checkoutbundle_paymenttype_ends_0").checkboxradio("refresh");
    $("#rentjeeves_checkoutbundle_paymenttype_ends_1").checkboxradio("refresh");
}

cancelled=[
    "rentjeeves_checkoutbundle_paymenttype_endMonth",
    "rentjeeves_checkoutbundle_paymenttype_endYear"
]
function whenCancelled(i){
    $.each(cancelled,function(index,value){
        if(i) {
            $('#' + value).selectmenu('enable');
        }else{
            $('#' + value).selectmenu('disable')
        }
    })
}

function freqHide(i){
    if(!i){//hide
        $('#rentjeeves_checkoutbundle_paymenttype_dueDate_box').hide()
    }else{
        $('#rentjeeves_checkoutbundle_paymenttype_dueDate_box').show()
    }
}


total=0;

function updateTotal(){
    a=parseFloat($("#rentjeeves_checkoutbundle_paymenttype_amount").val())
    o=parseFloat($("#rentjeeves_checkoutbundle_paymenttype_amountOther").val())
    t=0;
    if(!isNaN(a))
        t+=a;
    if(!isNaN(o))
        t+=o;
    total=a+o
    $("#total").html(accounting.formatNumber(t,2))
}


function submitForm(){

    $.ajax({
        url: 'checkout/exec',
        data: $('#rentjeeves_checkoutbundle_paymenttype').serialize(),
        type: 'post',
        async: 'true',

        success: function (result) {
            a=result
            console.log(result)
            if(result.success!=true){
                $.mobile.changePage('#pay')
                msg="";
                $.each(result,function(i,k){
                    $.each(k,function(h,j){
                        msg+=j+"<br>"
                    })
                })
                $("#errorMsg").html(msg)
                $("#errorMsg").show()
            }else{
                $("#popupBasic").popup( "open" )
                $("#errorMsg").hide()
                setTimeout(function(){
                    window.location.reload(true)
                },500)
            }
        },
        error: function (request, error) {
            $.mobile.changePage('#pay')
            msg="An error occurred. ("+error+")"
            $("#errorMsg").html(msg)
            $("#errorMsg").show()
        }
    });
}
