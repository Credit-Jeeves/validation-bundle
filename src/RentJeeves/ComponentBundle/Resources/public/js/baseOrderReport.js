$(function() {
    var currentTime = new Date()
    var minDate = new Date(currentTime.getYear(), currentTime.getMonth()-24); //previous
    var maxDate =  new Date(currentTime.getFullYear(),currentTime.getMonth()); // this month
    $('.calendar').datepicker({
        showOn: "both",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        format:'m/d/Y',
        starts: 1,
        minDate: minDate,
        maxDate: maxDate,
        position: 'r'
    });

    $('.typeReport').change(function(){
       $('.helpContainer>a').hide();
       $('.'+$(this).val()).show();
    });
});