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
	
Event = {
	hoverin: function(event) {
		//highlight my clone and disable all other events
		$('.event').not(event).stop().animate({'opacity': '0.2'}, 'slow');
		var clone = $('#clone-' + event.data('event'));
		if(clone)
			clone.find('.clone').stop().animate({'opacity': '1'}, 'slow');
	},
	hoverout: function(event) {
		//restore event visibility states...
		$('.event').not(event).stop().animate({'opacity': '1'}, 'slow');
		var clone = $('#clone-' + event.data('event'));
		if(clone)
			clone.find('.clone').stop().animate({'opacity': '0.2'}, 'slow');
	},
	makeClone: function(eventContainer) {
		var event = eventContainer.find('.event');
		var id = 'clone-' + event.attr('data-event');
		if($('#'+id).length == 0) {
			var clone = eventContainer.clone().css({
				'opacity': 1,
				'z-Index': 0,
				'bottom': ''
			});
			clone.find('.event').addClass('clone');
			clone.find('.event-details, .pin').remove(); // remove unneeded elements
			clone.attr('id', id);
			event.parent().after(clone);	// insert clone after original event
		}
	}
};

	hoverInFunction = function($this, event){
		if ($this.find('.event-details').text().length) {	// check for content

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
				
				var eventDetails = event.next();
				eventDetails.stop(true, true).fadeIn();

				xE = eventDetails.offset().top + eventDetails.outerHeight();
				xB = $(window).height() - $('#options').outerHeight();
				if (xE >= xB) {
					//event is too big, we need to shift it up a bit ...
					eventDetails.css('bottom', 0).css('position', 'relative');
					eventDetails.parent().css('bottom', 0).css('top', '');
				}
			}
			else {
				//remove self from list, find max and set to max+1
				zIndices.rmElem(parseInt($this.css("zIndex")));
				zIndices.push(zIndices.last()+1);
				$this.css("zIndex", zIndices.last().toString());
			}
		
			Event.hoverin($this.find('.event'));
		}
	};
	hoverOutFunction = function($this, event){
		if (!$this.hasClass('sticky')) {
			var eventDetails = event.next();
			eventDetails.css('bottom', '').css('position', '');
			eventDetails.parent().css('bottom', '');
	//		if (eventDetails.parent().data('top'))
	//			eventDetails.parent().css('top', eventDetails.parent().data('top'));

			event.next().stop(true, true).fadeOut(300, function(){

				if (shortModus()) {
					event.width('').html('+');
				}

				//remove self from list
				zIndices.rmElem(parseInt($this.css("zIndex")));
				$this.css("zIndex", "1");

				Event.hoverout($this.find('.event'));
			});
		}
		else {
			Event.hoverout($this.find('.event'));
		}
	};

	$.fn.hovercard = function(){
		//Set defauls for the control
		var event = $(this);
		var options = {
			detailsHTML: $('#event-' + event.data('event')).html(),
		};

		shortModus = function(){
			return $('#options-container').find('.selected').data('type') == 'short';
		},

		// align event details container
		event.next().css({ 
			'top': event.css('top'), 
			'left': event.css('left'),
			'padding-top': event.height() + 12
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
					var cl = $('#clone-' + eventContainer.find('.event').attr('data-event'));
					eventContainer.animate({
						top: cl.css('top'),
						left: cl.css('left'),
						duration: 'slow'
					}, function(){
						var id = 'clone-' + eventContainer.find('.event').attr('data-event');
						$('#'+id).remove();
					});
				} else {
					eventContainer.draggable('enable');
					Event.makeClone(eventContainer);
				}
			}
		});

		// show event details on hover
		event.parent().on('mouseenter mouseleave', function(e){
			if (e.type == 'mouseenter')
				hoverInFunction($(this), event);
			else
				hoverOutFunction($(this), event);
		});
	};
})(jQuery);
