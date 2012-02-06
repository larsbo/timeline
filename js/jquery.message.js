(function($){
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
