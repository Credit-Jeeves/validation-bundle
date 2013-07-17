$(document).ready(function(){
		
	function doCheckStart() {
		$("input:radio,input:checkbox").each(
			function(){
				doCheck(this);
  			}
  		);
	}
	doCheckStart();
	
	function doCheck(el) {
		if($(el).is(":checked")) {
			$(el).next().removeClass("uncheck");
			$(el).next().addClass("check");
		}
		else {
			$(el).next().removeClass("check");
			$(el).next().addClass("uncheck");
		}				
	}
	
	$("input:radio,input:checkbox").change(
  		function(){  						
  			if($(this).attr("type")=="radio") {
  				var inputName=$(this).attr("name");
  				$("[name='"+inputName+"']").each(
					function(){
						doCheck(this);
		  			}
		  		);
  			}
  			else {
  				doCheck(this);
  			}					
  		}
  	);
  	
})