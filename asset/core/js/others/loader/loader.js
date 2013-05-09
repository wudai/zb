/*
	loader.js
*/

;
(function() {
	
	var nocache = (new Date()).valueOf();
	function praseUrl(url){
		return (url.indexOf('http')==0?url:(ENVOBJ.domains.asset+url+'?v='+nocache));
	}
	function isFunction(obj) {
		return Object.prototype.toString.call(obj) === "[object Function]";
	}

	function isArray(obj) {
		return Object.prototype.toString.call(obj) === "[object Array]";
	}

	function isString(obj) {
		return typeof obj == "string" || Object.prototype.toString.call(obj) === "[object String]";
	}
	var LOADJS = window.LOADJS = function(urls, callback, charset) {
		// type checking functions

		// callback for js queue loading
		var i = 0;
		function success() {
			if (isFunction(callback)) {
				callback();
			}
		}

		// inner function for loading a single js file
		function loadjs(url, callback, charset) {
			if (isString(callback)) {
				charset = callback;
			}
			var head = document.getElementsByTagName("head")[0];
			var script = document.createElement("script");
			if (charset) {
				script.charset = charset;
			}
			script.defer=true;
			script.async=true;
			script.src = praseUrl(url);
			if(url=='')return success();	
			// Handle Script loading
			var done = false;
			// Attach handlers for all browsers
			script.onload = script.onreadystatechange = function(){
				if ( !done && (!this.readyState ||
						this.readyState == "loaded" || this.readyState == "complete") ) {
					done = true;
					if (isFunction(callback)) {
						callback();
					}
					// Handle memory leak in IE
					script.onload = script.onreadystatechange = null;
					head.removeChild( script );
				}
			};
			head.appendChild(script);
			if (isArray(urls) && i < urls.length) {
				loadjs(urls[i++], success, charset);
			}
		};

		// use different way to load js or js array
		if (isString(urls)) {
			loadjs(urls, callback, charset);
		}
		else if (isArray(urls)) {
			loadjs(urls[i++], success, charset);
		}
	}
	var LOADCSS = window.LOADCSS = function(urls) {
	
		// callback for js queue loading
		var i = 0;
		function success() {
			if (i < urls.length) {
				loadcss(urls[i++]);
			}
		}
		function loadcss(url){
			if(url.length<2)return;
			var css = document.createElement("link");
			css.setAttribute("rel", "stylesheet");
			css.setAttribute("type", "text/css");
			css.setAttribute("href", praseUrl(url));
			document.getElementsByTagName("head")[0].appendChild(css);
			success();
		}
		
		// use different way to load js or js array
		if (isString(urls)) {
			loadcss(urls);
		}
		else if (isArray(urls)) {
			urls.push('');
			loadcss(urls[i++]);
		}
	};

})();
