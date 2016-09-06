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
  	height=screen.height;
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
$('.button.bm_ry').click(function(){
  
})