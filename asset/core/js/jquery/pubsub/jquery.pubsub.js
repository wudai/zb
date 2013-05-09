/*
 * Publish Message
 * $.publish("message", data1, data2, data3, ...);
 *
 * Subscribe Message
 * $.subscribe("message", function(data1, data2, data3, ..., message){ }, fetchCache);
 *
 * When you subscribe a message, you can also get "message" with last parameter.
 * And, you can use "fetchCache" to determine whether to get messages which are
 * fired before subscribing or not. By defaut, subscribe with fetch cached messages always.
 * 
 * You can also use linked style like below to pub/sub multiple messages.
 * $.publish().subscribe.publish()
 *
 * You can do multiple messages subscribing, if you don't care about "data" parameters.
 * $.subscribe("message1 message2", function(){ });
 *
 * 2012-11-12 wangfeng 增加unsubscribe和subscribe_once功能
 * 2012-11-13 wangfeng 重写观察者模式触发机制，不再为window绑定事件
 */

;(function($) {
	// cache objec for publish-subscribe sequence.
	var cache = {};
	
	$.publish = function() {
		var message = arguments[0], args = [], i = 1;
	
		// construct the args array, and append message as the last element.
		while( i < arguments.length ) {
			args.push(arguments[i++]);
		}
		args.push(message);

		// trigger the message event.
		if(cache[message]){
			for(var j=cache[message].length;j--;){
				cache[message][j].apply(window,args);
			}
		}
	
		return $;
	};

	$.subscribe = function() {
		var first=0;
		if(arguments[0]=='__once') first=1;
		var messages = $.trim(arguments[first]).replace(/\s+/, " "),
		arrMsg = messages.split(" "),
		callback = arguments[first+1];

		for (var i = 0; i < arrMsg.length; i++) {
			var message = arrMsg[i];
			if (!cache[message]) {
				cache[message]=[];
			}
			if(first == 1){
				cache[message].push(function(){
					var args = [], j=0;
					while (j < arguments.length) {
						args.push(arguments[j++]);
					}
					callback.apply(this,args);
					$.unsubscribe(message,arguments.callee);
				});
			}else{
				cache[message].push(callback);
			}
		}

		return $;
	};

	$.subscribe_once = function() {
		var args = ['__once'], i = 0;
		while (i < arguments.length) {
			args.push(arguments[i++]);
		}
		return $.subscribe.apply(this,args);
	};

	$.unsubscribe = function(message, callback) {
		if(cache[message]){
			if(callback){
				for(var j=cache[message].length;j--;){
					if(cache[message][j]==callback){
						cache[message].splice(j,1);
						break;
					}
				}
			}else{
				cache[message]=null;
				delete cache[messages];
			}
		}
		return $;
	};

})(jQuery);
