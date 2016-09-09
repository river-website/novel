function getScrollTop() { 
  var scrollPos; 
  if (window.pageYOffset) {
    scrollPos = window.pageYOffset; 
  } 
  else if(document.compatMode && document.compatMode !='BackCompat') {
    scrollPos = document.documentElement.scrollTop; 
  }
  else if (document.body) {
    scrollPos = document.body.scrollTop; 
  } 
  return scrollPos; 
}
function setScrollTop(height){ 
  if (window.pageYOffset) {
    window.pageYOffset = height; 
  } 
  if(document.compatMode && document.compatMode !='BackCompat') {
    document.documentElement.scrollTop = height; 
  }
  if (document.body) {
    document.body.scrollTop = height; 
  }  
}

$('#webhtml').click(function(event) {
  	y=event.clientY;
  	height=window.screen.height;
    if (y > 1024)
      y = y / window.devicePixelRatio;
    if (height > 1024)
      height = height /window.devicePixelRatio
  	if ($(".topMenu").attr("style")=="display:none"){
  		height3 = height /3;
  		state = parseInt(y / height3) +1;
  		if (state == 2){
   		$(".topMenu").attr("style","");
  		$(".bmMenu").attr("style",""); 			
  		}
      else if(state==1){
        setScrollTop(getScrollTop()-height + 10);
      }
      else if(state==3){
        setScrollTop(getScrollTop()+height - 10);
      }
  	}
  	else{
  		$(".topMenu").attr("style","display:none");
  		$(".bmMenu").attr("style","display:none");
      $(".fontset").attr("style","display:none");
      
  	}
})

$('.bm_bt.bm_size').click(function(){
  if($('.fontset').attr("style")=="display:none"){
    $('.fontset').attr("style","");
  }
  else{
    $('.fontset').attr("style","display:none");
  }
})

$('#font_s').click(function(){
  content =$('#content');
  sizestr=content.css('font-size');
  size = parseInt(sizestr) -1;
  newsizestr=size + 'px';
  content.css('font-size',newsizestr);
})
$('#font_l').click(function(){
  content =$('#content');
  sizestr=content.css('font-size');
  size = parseInt(sizestr) +1;
  newsizestr=size + 'px';
  content.css('font-size',newsizestr);
})
$('.bm_ry').click(function(){
  str = $(".bm_ry").text();
  if(str=='夜间'){
    $('#zhangjieming').css('background','#000');
    $('#zhangjieming').attr('class','');
    $('#zhangjieming>h1').css('color','RGB(110,110,110)');
    $('#content').css('color','RGB(110,110,110)');
    $(".bm_ry").text('白天');
  }
  else if(str=='白天'){
    $('#zhangjieming').css('background','');
    $('#zhangjieming').attr('class','zhangjieming');
    $('#zhangjieming>h1').css('color','');
    $('#content').css('color','');
    $(".bm_ry").text('夜间');
  }
})