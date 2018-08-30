$(function() {
   var a = $(".lunbo img").height();
   $("#lunbobox").css('height', a);
})

var t;
var index = 0;
/////自动播放
t = setInterval(play, 3000)

function play() {
    //if (index > 5) {
        //index = 1
    //}
    // console.log(index)
    $("#lunbobox ul li").eq(index).css({
        "background": "#0080ed"
    }).siblings().css({
        "background": "#fff"
    })

    $(".lunbo a ").eq(index).fadeIn(1000).siblings().fadeOut(1000);
	index++;
};

