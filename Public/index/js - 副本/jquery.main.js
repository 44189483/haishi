$(function() {
	
	//海丝户外品牌口号2016-11-17
	$('.abuc-box li').each(function(index) {
		$(this).mouseover(function() {
			$('.abuc-box li').removeClass('on');
			$(this).addClass('on');
			$('.abuc-boxcnt').hide();
			$('.abuc-boxcnt').eq($(this).index()).show();
		})
	})
	
	$(function(){
	//海丝户外产品banner2016-11-30
		var mySwiper3 = new Swiper('.swiper-container3', {
		grabCursor: true,
		noSwiping: true,
		speed: 500,
		slidesPerView: 4,
	});
	$('.arrow-left3').on('click', function(e) {
		e.preventDefault()
		mySwiper3.swipePrev()
	});
	$('.arrow-right3').on('click', function(e) {
		e.preventDefault()
		mySwiper3.swipeNext()
	});
	
	//判斷產品個數2016-11-30
	if($('.swiper-no-swiping').length<='4'){
		$('.do-banner').remove();
	}
	
	})
	
})