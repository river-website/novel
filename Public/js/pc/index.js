$('#load-more1').click(function(){
	var displaystr='';
	var state=$(this).attr('state')
	if (state=='false'){
		displaystr='display:none';
		$(this).attr('state','true');
		$(this).find('em').text('展开更多');
		$(this).removeClass('up');
	}else{
		$(this).attr('state','false');
		$(this).find('em').text('收起更多');
		$(this).addClass('up');
	}
	var classlist=$('.cls1 .book-list');
	for(var i=0;i<classlist.length;i++){
		var list=$(classlist[i]).find('li');
		for(var j=10;j<list.length;j++){
			$(list[j]).attr('style',displaystr);
		}
	}
});

$('#load-more2').click(function(){
	var displaystr='';
	var state=$(this).attr('state')
	if (state=='false'){
		displaystr='display:none';
		$(this).attr('state','true');
		$(this).find('em').text('展开更多');
		$(this).removeClass('up');
	}else{
		$(this).attr('state','false');
		$(this).find('em').text('收起更多');
		$(this).addClass('up');
	}
	var classlist=$('.cls2 .book-list');
	for(var i=0;i<classlist.length;i++){
		var list=$(classlist[i]).find('li');
		for(var j=10;j<list.length;j++){
			$(list[j]).attr('style',displaystr);
		}
	}
});

$('.class-list').hover(function(){
	$(this).find('.title a').attr('style','');
},function(){
	$(this).find('.title a').attr('style','display:none');
})