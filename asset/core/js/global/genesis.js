$.crumb = {};
$.crumb.toString = function() {
	return $.crumb.get();
}
$.crumb.get = function() {
	return ((window.ENVOBJ || {})['crumb'] || "");
};
$.crumb.set = function(r) {
	return (window.ENVOBJ || {})['crumb'] = r;
};
$.log = function() {
	if (ENVOBJ && ENVOBJ.debug) {
		if (window.console && window.console.log) {
			window.console.log(arguments);
		}
		else if (window.opera && window.opera.postError) {
			window.opera.postError(arguments);
		}
	}
};

$.isContains = function(containerNode, node) {
	while (node) {
		if (containerNode == node) {
			return true;
		}
		try {
			node = node.parentNode;
		} catch(e) {
			return false;
		}
	}
	return false;
}

$.error = function(r) {
	if (!r) return;
	var $loginurl = $('#login_url').length ? $('#login_url').val() : null;
	if (typeof r == 'string') {
		var code = parseInt(r);
		if (code == 2) location.href = $loginurl || r.substr(2) || $.domains.www;
		else return {
			code: parseInt(r),
			msg: r.substr(2)
		};
	} else {
		if (r.code == 2) {
			$.confirm('<div class="block_simple_alert clearfix">'+r.msg+'</div>',{
				title:'出错啦！',
				onAccept:function(){
					var url = $.domains.www;
					if (r.data && r.data.tourl) url = r.data.tourl;
					location.href = $loginurl || url;
				}
			});
		}
		else return r;
	}

	function gotoLogin(loginurl){
		//TODO: 弹框选择 登陆、回首页
	}
}
$.httpData=$.httpData||function( xhr, type, s ) {
		var ct = xhr.getResponseHeader("content-type") || "",
				xml = type === "xml" || !type && ct.indexOf("xml") >= 0,
				data = xml ? xhr.responseXML : xhr.responseText;

		if ( xml && data.documentElement.nodeName === "parsererror" ) {
				jQuery.error( "parsererror" );
		}

		// Allow a pre-filtering function to sanitize the response
		// s is checked to keep backwards compatibility
		if ( s && s.dataFilter ) {
				data = s.dataFilter( data, type );
		}

		// The filter can actually parse the response
		if ( typeof data === "string" ) {
				// Get the JavaScript object, if JSON is used.
				if ( type === "json" || !type && ct.indexOf("json") >= 0 ) {
						data = jQuery.parseJSON( data );

				// If the type is "script", eval it in global context
				} else if ( type === "script" || !type && ct.indexOf("javascript") >= 0 ) {
						jQuery.globalEval( data );
				}
		}

		return data;
}
//badie
ENVOBJ.badie = $.browser.msie && (parseInt($.browser.version) <= 8);
function getViewport(){
	var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;
	return {width:x,height:y}
}
/*
$(function() {
	if (window.systemNotice) {
		$.notice(window.systemNotice.message, window.systemNotice.type);
	}
	

});
*/
//ajax default setting
$.ajaxSetup({
	type: "post",
	error: function() {
		//$.alert("查询或保存数据时发生错误, 请重试");
	},
	timeout: 15000
});
//bi setting
(function() {
	var _ajax = jQuery.ajax;
	function parseURL(options) {
		var url = options.url;
		var data = options.data || {};
		var _protocal = location.protocal;
		var _host = location.host;
		if (url.indexOf(location.protocol + '//') !== 0) url = location.protocol + '//' + location.host + url;
		var loc = {
			'href': url
		};
		var parts = url.replace('//', '/').split('/');
		loc.protocol = parts[0];
		loc.host = parts[1];
		parts[1] = parts[1].split(':');
		loc.hostname = parts[1][0];
		loc.port = parts[1].length > 1 ? parts[1][1] : '';
		parts.splice(0, 2);
		loc.pathname = '/' + parts.join('/');
		loc.pathname = loc.pathname.split('#');
		loc.hash = loc.pathname.length > 1 ? '#' + loc.pathname[1] : '';
		loc.pathname = loc.pathname[0];
		loc.pathname = loc.pathname.split('?');
		loc.search = loc.pathname.length > 1 ? '?' + loc.pathname[1] : '';
		if (typeof data == 'string') {
			loc.search += '&' + data;
		}
		else {
			for (var i in data) {
				loc.search += '&' + i + '=' + escape(data[i]);
			}
		}
		loc.search = loc.search.replace(/&password=[^&]+/gi, '');
		loc.pathname = loc.pathname[0];
		return loc;
	}
	$.extend({
		sendTobi: function(options) {
			if(options.data && (options.data.nolog || options.url.indexOf('nolog=1') > 0)) return ;
			var _uid = ((window.ENVOBJ || {}).curUser || {}).id || 0;
			var base = 'http://120.132.134.44/t_ajax.gif';
			var parts = parseURL(options);
			//console.dir(parts);
			var data = [];
			data.push(base);
			data.push('m=' + Math.random());
			data.push('u=' + _uid);
			data.push('k=' + $.cookie('c.bi_c'));
			data.push('s=' + $.cookie('c.bi_s'));
			data.push('f=2');
			data.push('t=0');
			data.push('rd=' + parts.host);
			data.push('ru=' + parts.pathname);
			data.push('rp=' + parts.search.substr(1));

			var img = new Image();
			img.src = data.join('?');
		},
		logAct: function(options) {
			if(options.data && (options.data.nolog || options.url.indexOf('nolog=1') > 0)) return ;
			var _uid = ((window.ENVOBJ || {}).curUser || {}).id || 0;
			var base = 'http://127.0.0.1/t_act.gif';
			var parts = parseURL(options);
			//console.dir(parts);
			var data = [];
			data.push(base);
			data.push('m=' + Math.random());
			data.push('u=' + _uid);
			data.push('atype=' +( options.atype||''));
			data.push('sid=' +( options.sid||''));
			data.push('k=' + $.cookie('c.bi_c'));
			data.push('s=' + $.cookie('c.bi_s'));
			data.push('f=2');
			data.push('t=0');
			data.push('rd=' + parts.host);
			data.push('ru=' + parts.pathname);
			data.push('rp=' + parts.search.substr(1));

			var img = new Image();
			img.src = data.join('?');
		}
	});
	jQuery.ajax = function(options) {
		var _success = options.success || function(r) {};
		options.data = options.data || {};

		switch (typeof options.data) {
		case 'string':
			if (options.data.length > 0) options.data += '&crumb=' + $.crumb.get();
			break;
		case 'object':
			options.data['crumb'] = $.crumb.get();
			break;
		case 'array':
			options.data.push('crumb=' + $.crumb.get());
			break;
		}
		options.success = function(r) {
			var r = $.error(r);
			if (r) {
				_success(r);
				if (r && r.crumb) {
					$.crumb.set(r.crumb);
				}
				if (r && r.data && r.data.got){
					$.publish('got',r.data.got);
				}
			}
		}
		var _xhr = _ajax;
		if (options.dataType != "script" && options.dataType != 'jsonp' && options.url.indexOf('callback=') == - 1) {
			var rhost = (/^(\w+:)?\/\/([^\/?#]+)/.exec(options.url));
			if (rhost) {
				rhost = rhost[0];
				var shost = location.protocol + '//' + location.host;
				if (rhost !== shost && $.swfajax) _xhr = $.swfajax;
			}
		}
		var _result = _xhr(options);
		//$.sendTobi(options);
		return _result;
	}
})();

