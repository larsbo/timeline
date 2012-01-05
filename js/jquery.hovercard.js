//Title: Hovercard plugin by PC 
//Documentation: http://designwithpc.com/Plugins/Hovercard
//Author: PC 
//Website: http://designwithpc.com
//Twitter: @chaudharyp

(function ($) {
	$.fn.hovercard = function (options) {
		//Set defauls for the control
		var defaults = {
			width: 400,
			cardImgSrc: "",
			detailsHTML: "",
			delay: 0,
			onHoverIn: function() { },
			onHoverOut: function() { }
		};
		//Update unset options with defaults if needed
		var options = $.extend(defaults, options);

		//Executing functionality on all selected elements
		return this.each(function(){
			var obj = $(this);

			//wrap a parent span to the selected element
			obj.wrap('<div class="event-preview" />');

			//if card image src provided then generate the image element
			var img = '';
			if (options.cardImgSrc.length > 0) {
				img = '<img class="event-img" src="' + options.cardImgSrc + '" />';
			}

			//append generated details element after the selected element
			obj.after('<div class="event-details">' + img + options.detailsHTML + '</div>');
			obj.siblings(".event-details").eq(0).css({ 
				'top': obj.css('top'), 
				'width': Math.max(options.width, obj.width())
			});

			//toggle hover card details on hover
			obj.closest(".event-preview").hover(function(){
				var $this = $(this);

				//Up the z index for the .event to overlay on .event-details
				$this.css("zIndex", "200");
				obj.css("zIndex", "100").find('.event-details').css("zIndex", "50");

				$this.find(".event-details").eq(0).stop(true, true).delay(options.delay).fadeIn();

				//Default functionality on hoverin, and also allows callback
				if (typeof options.onHoverIn == 'function') {
					//Callback function
					options.onHoverIn.call(this);
				}

			}, function(){
				//Undo the z indices 
				$this = $(this);

				$this.find(".event-details").eq(0).stop(true, true).fadeOut(300, function(){
					$this.css("zIndex", "0");
					obj.css("zIndex", "0").find('.event-details').css("zIndex", "0");

					if (typeof options.onHoverOut == 'function') {
						options.onHoverOut.call(this);
					}
				});
			});
		});
	};
})(jQuery);