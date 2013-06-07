// from http://stackoverflow.com/questions/16105801/handling-objects-with-twitter-bootstrap-typeahead/16159942
var typeahead = control.typeahead({ /* ... */ }).data('typeahead');

// manually override select and render
//  (change attr('data-value' ...) to data('value' ...))
//  otherwise both functions are exact copies
typeahead.select = function() {
	var val = this.$menu.find('.active').data('value')
	this.$element.val(this.updater(val)).change()
	return this.hide()
};
typeahead.render = function(items) {
	var that = this

	items = $(items).map(function (i, item) {
		i = $(that.options.item).data('value', item)
		i.find('a').html(that.highlighter(item))
		return i[0]
	});

	items.first().addClass('active')
	this.$menu.html(items)
	return this
};
