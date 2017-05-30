(function(jQuery){

	jQuery.extend({
		url: (function(){
			var data = {};
			var $meta = $('meta[name=data-url]');
			if ($meta.length > 0) {
				var content = $meta.attr('content').split(',');
				for (var i=0; i < content.length; i++) {
					var variable = content[i].trim().split('=');
					data[variable[0]] = variable[1];
				}
			}
			return data;
		})()
	});

})(jQuery);
