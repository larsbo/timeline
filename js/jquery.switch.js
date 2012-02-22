/************************************************ 
*  jQuery iphoneSwitch plugin                   *
*                                               *
*  Author: Daniel LaBare                        *
*  Date:   2/4/2008                             *
************************************************/
jQuery.fn.Switch = function(start_state, switched_on_callback, switched_off_callback) {
	var state = start_state == 'on' ? start_state : 'off';

	// define default settings
	var settings = {
		switch_on: 'images/switch_container_on.png',
		switch_off: 'images/switch_container_off.png',
	};

	// create the switch
	return this.each(function() {
		var elem = $(this);
		var image;

		// make the switch image based on starting state
		image = $('<img class="switch" style="background-position:'+(state == 'on' ? 0 : -53)+'px" src="'+(state == 'on' ? settings.switch_on : settings.switch_off)+'" /></div>');
		elem.html(image);

		// click handling
		elem.click(function() {
			if(state == 'on') {
				elem.find('.switch').animate({backgroundPosition: -53}, "slow", function() {
					$(this).attr('src', settings.switch_off);
					switched_off_callback();
				});
				state = 'off';
			} else {
				elem.find('.switch').animate({backgroundPosition: 0}, "slow", function() {
					switched_on_callback();
				});
				elem.find('.switch').attr('src', settings.switch_on);
				state = 'on';
			}
		});
	});
};
