$(function(){

   $(".jiantou").click(function(event) {
		$(".search2").slideToggle()
	});

   $(".search2 p").each(function(index, el) {
   	    $(this).click(function(event) {
   	    	var z = $(this).text();
   	    	$(".search1 span").text(z);
   	    	$(".search2").css('display', 'none');
   	    });
   });


})