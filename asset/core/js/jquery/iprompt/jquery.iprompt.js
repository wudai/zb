;(function($) {
$.fn.iPrompt = function(settings) {
	var defaults = {
		text: null,
		css: { color: "#8c7e7e" },
		preventGray: false,
		refer: "iprompt"
	};

	// give settings to UI elements
	var opts = $.extend(defaults, settings);

	this.isEmpty = function(trim, index) {
		trim = (typeof trim == "boolean") ? trim : false;
		index = parseInt(index) || 0;

		var $target = this.filter("input[type='text'], textarea");
		if ($target.length) {
			if ($target.eq(index).hasClass("i-prompt-gray")) {
				return true;
			}
			else if (trim && $.trim($target.eq(index).val()) == "") {
				return true;
			}
			else if (!trim && $target.eq(index).val() == "") {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return null;
		}
	};

	this.preventGray = function() {
		opts.preventGray = true;
		return this.filter("input.i-prompt, textarea.i-prompt").each(function() {
			$(this).data("iprompt-opts", opts);
		}).end();
	};

	this.removeGray = function() {
		return this.filter("input.i-prompt, textarea.i-prompt").each(function() {
			removeGray(this);
		}).end();
	};

	this.addGray = function() {
		return this.filter("input.i-prompt, textarea.i-prompt").each(function() {
			addGray(this);
		}).end();
	};

	function removeGray(textbox) {
		var $textbox = $(textbox);
		if ($textbox.hasClass("i-prompt-gray")) {
			$textbox.removeClass("i-prompt-gray").val("");
			textbox.$pm.find('dfn').css('opacity',0);
		}
	}

	function addGray(textbox) {
		var $textbox = $(textbox);
		$textbox.addClass("i-prompt-gray");
		textbox.$pm.find('dfn').css('opacity',100).html( opts.text || $textbox.attr(opts.refer) || "");
	}

	this.filterEmpty = function() {
		return this.filter("input.i-prompt, textarea.i-prompt").filter(".i-prompt-gray");
	};

	this.notEmpty = function() {
		return this.filter("input.i-prompt, textarea.i-prompt").not(".i-prompt-gray");
	};

	return this.filter("input, textarea").each(function() {
		var $this = $(this);
		if (typeof $this.data("iprompt-opts") != "undefined" && $this.data('iprompt-opts')) {
			opts = $this.data("iprompt-opts");
		}
		else if (settings === false || settings === null) {
			return;
		}
		else {
		
			var pmCSS=$.extend({
				'position':'absolute',
				'width':$this.width(),
				'z-index':'1'
			},opts.css);
			// //if(!pmCSS['line-height']){
			// 	if($this.hasClass('txtL'))pmCSS['lineHeight']="36px";
			// 	if($this.hasClass('txtM'))pmCSS['lineHeight']="30px";
			// //}
			
			this.$pm=$('<div class="iprompt-pm '+$this.attr('class')+'"><dfn></dfn></div>').css(pmCSS).insertBefore($this);
			this.$pm.find('dfn').css({
				'-moz-transition':'all 0.2s ease-out 0s',
				'-webkit-transition':'all 0.2s ease-out 0s',
				'-o-transition':'all 0.2s ease-out 0s',
				'-ms-transition':'all 0.2s ease-out 0s',
				'transition':'all 0.2s ease-out 0s',
				'opacity':'100'
			})
			$this.attr('autocomplete','off')
			.val('')
			.css({
				'background':'transparent none',
				'position':($this.css('position')=='static'||!$this.css('position'))?'relative':$this.css('position'),
				'z-index':($this.css('z-index')=='auto'||!$this.css('z-index'))?'2':$this.css('z-index')
			})
			// save options to data cache
			.data("iprompt-opts", opts)
			// add css style properties
			.data("cssText", this.style.cssText)
			.addClass("i-prompt")
			.focus(function() {
				removeGray(this);
			})
			.blur(function(event) {
				var that=this;
				window.setTimeout(function() {
					var $this = $(that);

					if (opts.preventGray) {
						opts.preventGray = false;
						$this.data("iprompt-opts", opts);
						return;
					}

					if (!$.trim($this.val())) {
						addGray(that);
					}
				}, 100);
			})
			.keydown(function(event) {
				if (event.keyCode == 27) {
					$(this).blur();
				}
			})
			.closest("form")
			.submit(function(event) {
				if ($this.hasClass("i-prompt-gray")) {
					$this.removeClass("i-prompt-gray").val("");
				}
			});

			// set input element to default value
			if (!$.trim(this.value)) {
				this.value = this.defaultValue;
				addGray(this);
			}
		}
	}).end();
};
})(jQuery);
