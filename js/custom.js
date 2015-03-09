
 	var $ = jQuery;
	var isMobile = $(window).width() < 760;
//	console.log($(window).width());
	if (!isMobile && Math.floor(Math.random()*2) > 0)
	{ 
          //check if cookie has open cookie data~
          //yes
		if ($.cookie('isEmailPop')!='yes') {
//		console.log('2');
			setTimeout(
				function(){              
				$('#myModal').modal();
//				console.log('3');
				$.cookie('isEmailPop', 'yes', { expires: 7 });
			},2000);
          
			$('#emailPopMsg').submit(function() {
				//window.open('http://feedburner.google.com/fb/a/mailverify?uri=i-health/KgZF', 'popupwindow', 'scrollbars=yes,width=550,height=520');
				$('#myModal').modal('hide');

				return true;
			}); 
		}  
	}
    
