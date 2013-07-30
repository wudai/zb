$(function() {
	// 跳转按钮
	$.subscribe("click_goto", function(e){
		window.location.href=$(e.target).attr('srv');
	});
	$.subscribe("click_goblank", function(e){
		window.open($(e.target).attr('srv'));
	});
	$.subscribe("click_goback", function(e){
		window.history.back();
	});
	if($('.ac_goback').length&&history.length<2){
		$('.ac_goback').hide();
	}
});
