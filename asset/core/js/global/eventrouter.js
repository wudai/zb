;
(function($) {
	var __acPatten=/ac_[A-z]+/g;
	$(document).click(function(e) {
		$.publish('click_body',document.body);
		var data_click=$(e.target).closest('[data-click]');
		if(data_click.size()){
			e.target=data_click;
			$.publish('click_'+data_click.data('click'),e)
			return;
		}
	}).mouseover(function(ev){
		var role=$(ev.target).closest('[data-role]');
		if(role.size() && !role.data('rollin')){
			ev.target=role[0]
			role.data('rollin',1)
			$.publish('enter_'+role.data('role'),ev)
		}
		return false;
	}).mouseout(function(ev){
		var role=$(ev.target).closest('[data-role]');
		if(role.size()){
			if(!$(ev.relatedTarget).closest('[data-role]').is(role)){
				ev.target=role[0]
				role.removeData('rollin')
				$.publish('leave_'+role.data('role'),ev)
			}
		}
		return false;
	})
	function __getActions(actions){
		var tmp=actions.match(__acPatten);
		if(tmp&&tmp.length)
			return tmp.join(',').replace('ac_','').split(',');
		else
			return [];
	}

	$.subscribe('track' , __tracking, false);
	
	var _serverPath = 'http://120.132.134.44/hotmap.gif';
	var bi_img = new Image();
	
	function __tracking(target,actions,e){
		var actions=actions.split(','),key=0,className='';
		while (actions[key]){
			className='.ac_'+actions[key++];
			var _bi_url = _serverPath 
					+'?doms='+className 
					+':eq('+$(className).index(target)+')'
					+'&m='+Math.random()
					+'&hotmap='+ENVOBJ.hotmap
					+'&uid='+ENVOBJ.curUser.id;
			bi_img.src = _bi_url;
		}
	}
})(jQuery);
