(function( $ ){
	
	if (window.PIE) {
        $('input,select,textarea,.button span,.pie-el').each(function() {
            PIE.attach(this);
        });
    }
    
    function doCheckRadioStart() {
		$(".select-box input:radio:checked").each(
			function(){
				$(this).parent("label").addClass("active");
  			}
  		);
	}
	doCheckRadioStart();
    
    $("input:radio").change(
  		function(){
  			var inputRadioName=$(this).attr("name");
			$("[name='"+inputRadioName+"']").each(
				function(){
					$(this).parent("label").removeClass("active");
	  			}
	  		);
	  		$(this).parent("label").addClass("active");
  		}
  	);
  	
  	$.fn.doSubMenu = function() {
  	
		this.hover(
			function () {
				$(this).addClass("active");
				clearTimeout($.data(this,'timer'));
				$('ul',this).stop(true,true).slideDown(200);
			},
			function () {
				$(this).removeClass("active");
				$.data(this,'timer', setTimeout($.proxy(function() {
					$('ul',this).stop(true,true).slideUp(200);
				},
				this), 100));
			});

	};
	
	$("#delete").click(function() {
        $("#searsh-field").val("");
    });
  
})( jQuery );

$(document).ready(function(){
	$("#menu li.submenu").doSubMenu();
});