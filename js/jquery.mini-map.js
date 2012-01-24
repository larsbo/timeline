/* 
jQuery Mini-Map plugin copyright Sam Croft <samcroft@gmail.com>
extended for timeline use copyright Lars Borchert <borchert.lars@gmail.com>
Licensed like jQuery - http://docs.jquery.com/License
*/
(function($){
	$.fn.minimap = function(timeline, factor){
		if (!factor) {
			factor = 8;
		}
		var el = this;
		var years = el.find('.date');
		var events = el.find('.event');
		var clicked = false;
		var miniMap = $('#mini-map');
		var miniMapCurrentView = $('#current-view');

		var height = Math.round(el.height()/factor);
		var width = Math.round(el.width()/factor);
		miniMap.height(height + 10);
		miniMap.width(width);

		miniMapCurrentView.height(height + 12);
		miniMapCurrentView.width(Math.round($(window).width()/factor));

		// show every 5th year
		years.each(function(i,t){
			if (i % 5 == 0) {
				var year = $(this);
				var yearCoords = year.offset();

				var mapIcon = $('<div>' + year.text() + '</div>');
				mapIcon
				.css({
					'width': 18, 
					'left': Math.round(yearCoords.left/factor)
				})
				.addClass(t.tagName.toLowerCase())
				.appendTo(miniMap);
			}
		});

		// show events
		events.each(function(i,t){
			var event = $(this);
			var eventCoords = event.offset();

			var mapIcon = $('<div>');
			mapIcon
			.css({
				'height': Math.round(event.height()/factor), 
				'width': Math.round(event.width()/factor), 
				'left': Math.round(eventCoords.left/factor),
				'top': Math.round(eventCoords.top/factor) + 3
			})
			.addClass(t.tagName.toLowerCase())
			.appendTo(miniMap);
		});

		miniMapCurrentView.mousedown(function(){
			clicked = true;
		});

		miniMap.mouseup(function(e){
			clicked = false;

			var view = $('#current-view');
			var mousePosition = e.pageX;
			var offset =  $(this).offset();
			var viewCenter = Math.round(view.width()/2);
			var newPosition = Math.round((mousePosition - viewCenter - offset.left) * -8);

			view.css('cursor', 'pointer');
			timeline.scrollTo(newPosition, 0, 200);
		});

		miniMapCurrentView.mousemove(function(e){
			if (clicked) {
				var view = $(this);
				var mousePosition = e.pageX;
				var offset = view.parent().offset();
				var viewCenter = Math.round(view.width()/2);
				var newPosition = mousePosition - viewCenter - offset.left;

				view.css({'cursor': 'ew-resize', 'left': newPosition});
			}
		});
	};


	/* messages */
	$.fn.message = function(){
		var myMessages = ['info','warning','error','success'];
		var immediateMessage = $('.immediate');
		var page = $('#page');

		function hideAllMessages(){
			var messagesHeights = new Array(); // this array will store height for each
			for (i=0; i<myMessages.length; i++) {
				messagesHeights[i] = $('.' + myMessages[i]).outerHeight(); // fill array
				$('.' + myMessages[i]).css('top', -messagesHeights[i]); //move element outside viewport
			}
		}

		function showImmediateMessage(){
			if (immediateMessage.length){
				immediateMessage.animate({top:0}, 500);
				page.animate({
					top: immediateMessage.outerHeight(),
					height: page.outerHeight() - immediateMessage.outerHeight()
				}, 500);
			}
		}

		function showMessage(){
			for (i=0; i<myMessages.length; i++) {
				$('.'+ myMessages[i] +'-trigger').click(function(){
					hideAllMessages();
					$('.' + myMessages[i]).animate({top:"0"}, 500);
				});
			}
		}

		$('.msg').click(function(){
			$(this).animate({top: -$(this).outerHeight()}, 500);
			page.animate({
				top: 0,
				height: page.outerHeight() + immediateMessage.outerHeight()
			}, 500);
		});

		hideAllMessages();
		showImmediateMessage();
	};
})(jQuery);

