//缩放比例
if(/Android (\d+\.\d+)/.test(navigator.userAgent)){
    var version = parseFloat(RegExp.$1);
    if(version>2.3){
        var phoneScale = parseInt(window.screen.width)/1400;
        document.write('<meta name="viewport" content="width=1400, minimum-scale = '+ phoneScale +', maximum-scale = '+ phoneScale +', target-densitydpi=device-dpi">');
    }else{
        document.write('<meta name="viewport" content="width=1400, target-densitydpi=device-dpi">');
    }
}else{
    document.write('<meta name="viewport" content="width=1400, user-scalable=no, target-densitydpi=device-dpi">');
}