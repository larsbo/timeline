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

	$.fn.hovercard = function(){
		//Set defauls for the control
		var event = $(this);
		var options = {
			width: 400,
			detailsHTML: $('#event-' + event.data('event')).html(),
			delay: 0,
			onHoverIn: function() { },
			onHoverOut: function() { }
		};

		shortModus = function(){
			return $('#options-container').find('.selected').data('type') == 'short';
		},

		// align event details container
		event.next().css({ 
			'top': event.css('top'), 
			'min-width': Math.max(options.width, event.width()),
			'left': event.css('left')
		});


		var isDragging = false;
		event.mousedown(function() {
			$(window).mousemove(function() {
				isDragging = true;
				$(window).unbind("mousemove");
			});
		})
		event.mouseup(function() {
			var wasDragging = isDragging;
			isDragging = false;
			$(window).unbind("mousemove");
			if (!wasDragging) {
				// make event sticky
				var eventContainer =  $(this).parent();
				eventContainer.toggleClass('sticky');
	
				// move event back to original position
				if (!eventContainer.hasClass('sticky')) {
					eventContainer.draggable('disable');
					eventContainer.animate({
						top: eventContainer.data('origtop'),
						left: eventContainer.data('origleft'),
						duration: 'slow'
					});
				} else {
					eventContainer.draggable('enable');
				}
			}
		});

		// show event details on hover
		event.parent().hover(function(){
			var $this = $(this);

			if ($this.find('.event-details').html() != '') {
				// show details only when not empty
	
				if (!$this.hasClass('sticky')) {
					//create new max of zIndices, so element will hover on top...
					zIndices.push(zIndices.last()+1);
					$this.css("zIndex", zIndices.last().toString());
	
					if (shortModus()) {
						// show title on hover
						event
							.width(event.attr('data-width'))
							.html(event.data('title') + '<span class="pin"></span>');
					}
					event.next().stop(true, true).delay(options.delay).fadeIn();
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
			}
		},
		// hode event details on hover out
		function(){
			$this = $(this);

			if (!$this.hasClass('sticky')) {
				event.next().stop(true, true).fadeOut(300, function(){

					if (shortModus()) {
						event.width('').html('+');
					}

					//remove self from list
					zIndices.rmElem(parseInt($this.css("zIndex")));
					$this.css("zIndex", "1");

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
	};
})(jQuery);
