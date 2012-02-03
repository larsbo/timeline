//Title: Hovercard plugin by PC 
//Documentation: http://designwithpc.com/Plugins/Hovercard
//Author: PC 
//Website: http://designwithpc.com
//Twitter: @chaudharyp

var zIndices = [];

(function ($) {
	Array.prototype.sortNum = function() {
		return this.sort( function (a,b) { return a-b; } );
	}
	Array.prototype.rmElem = function(a) {
		var index = this.indexOf(a);
		if (index >= 0) {
			this.splice(index, 1);
			return true;
		}
		else
			return false;
	}
	Array.prototype.last = function() {
		var length = this.length;
		if (length > 0)
			return this[length-1];
		else
			return 10; //start with 10, if no element is hovered yet
	}

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
			obj.wrap('<div class="event-preview"  style="zIndex: 0" />');

			//if card image src provided then generate the image element
			var img = '';
			if (options.cardImgSrc.length > 0) {
				img = '<img class="event-img" src="' + options.cardImgSrc + '" />';
			}

			//append generated details element after the selected element
			obj.after('<div class="event-details" style="zIndex: 1">' + img + options.detailsHTML + '</div>');
			obj.siblings(".event-details").eq(0).css({ 
				'top': obj.css('top'), 
				'width': Math.max(options.width, obj.width())
			});
			obj.click(function() {
				// make event sticky
				var selected = $(this);
				selected.parent().toggleClass('sticky');
			});
			obj.css("zIndex", "2");

			//toggle hover card details on hover
			obj.closest(".event-preview").hover(function(){
				var $this = $(this);
				var title = $this.find('.event');

				if (!$this.hasClass('sticky')) {
					//create new max of zIndices, so element will hover on top...
					zIndices.push(zIndices.last()+1);
					$this.css("zIndex", zIndices.last().toString());

					// if 'short modus' is active show title on hover
					if ($('#options-container').find('.selected').data('type') == 'short') {
						title.width(title.attr('data-width')).html(title.data('title'));
					}

					$this.find(".event-details").eq(0).stop(true, true).delay(options.delay).fadeIn();
				}
				else {
					//remove self from list, find max and set to max+1
					zIndices.rmElem(parseInt($this.css("zIndex")));
					zIndices.push(zIndices.last()+1);
					$this.css("zIndex", zIndices.last().toString());
				}

				//Default functionality on hoverin, and also allows callback
				if (typeof options.onHoverIn == 'function') {
					//Callback function
					options.onHoverIn.call(this);
				}

			}, function(){
				//Undo the z indices 
				$this = $(this);
				var title = $this.find('.event');

				if (!$this.hasClass('sticky')) {

					$this.find(".event-details").eq(0).stop(true, true).fadeOut(300, function(){
						// if 'short modus' is active hide title on hoverout
						if ($('#options-container').find('.selected').data('type') == 'short') {
							title.width('').html('+');
						}

						//remove self from list
						zIndices.rmElem(parseInt($this.css("zIndex")));
						$this.css("zIndex", "0");

						if (typeof options.onHoverOut == 'function') {
							options.onHoverOut.call(this);
						}
					});
				}
				else {
					if (typeof options.onHoverOut == 'function') {
						options.onHoverOut.call(this);
					}
				}
			});
		});
	};
})(jQuery);
