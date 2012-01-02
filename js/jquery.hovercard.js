//Title: Hovercard plugin by PC 
//Documentation: http://designwithpc.com/Plugins/Hovercard
//Author: PC 
//Website: http://designwithpc.com
//Twitter: @chaudharyp

(function ($) {
    $.fn.hovercard = function (options) {

        //Set defauls for the control
        var defaults = {
            width: 300,
            openOnLeft: false,
            openOnTop: false,
            cardImgSrc: "",
            detailsHTML: "",
            twitterScreenName: '',
            showTwitterCard: false,
            facebookUserName: '',
            showFacebookCard: false,
            showCustomCard: false,
            customCardJSON: {},
            customDataUrl: '',
            background: "#ffffff",
            delay: 0,
            onHoverIn: function () { },
            onHoverOut: function () { }
        };
        //Update unset options with defaults if needed
        var options = $.extend(defaults, options);

        //Executing functionality on all selected elements
        return this.each(function () {
            var obj = $(this);

            //wrap a parent span to the selected element
            obj.wrap('<div class="hc-preview" />');

            //add a relatively positioned class to the selected element
            obj.addClass("hc-name");

            //if card image src provided then generate the image element
            var hcImg = '';
            if (options.cardImgSrc.length > 0) {
                hcImg = '<img class="hc-pic" src="' + options.cardImgSrc + '" />';
            }

            //generate details span with html provided by the user
            var hcDetails = '<div class="hc-details">' + hcImg + options.detailsHTML + '</div>';

            //append this detail after the selected element
            obj.after(hcDetails);
            obj.siblings(".hc-details").eq(0).css({ 'width': options.width, 'background': options.background });

            //toggle hover card details on hover
            obj.closest(".hc-preview").hover(function () {
							console.log('klick!');

                var $this = $(this);
                adjustToViewPort($this);

                //Up the z index for the .hc-name to overlay on .hc-details
                $this.css("zIndex", "200");
                obj.css("zIndex", "100").find('.hc-details').css("zIndex", "50");

                var curHCDetails = $this.find(".hc-details").eq(0);
                curHCDetails.stop(true, true).delay(options.delay).fadeIn();

                //Default functionality on hoverin, and also allows callback
                if (typeof options.onHoverIn == 'function') {
                    //Callback function
                    options.onHoverIn.call(this);
                }

            }, function () {
                //Undo the z indices 
                $this = $(this);

                $this.find(".hc-details").eq(0).stop(true, true).fadeOut(300, function () {
                    $this.css("zIndex", "0");
                    obj.css("zIndex", "0").find('.hc-details').css("zIndex", "0");

                    if (typeof options.onHoverOut == 'function') {
                        options.onHoverOut.call(this);
                    }
                });
            });

            //Add parameter to force open on left
            function adjustToViewPort(hcPreview) {

                var hcDetails = hcPreview.find('.hc-details').eq(0);
                var hcPreviewRect = hcPreview[0].getBoundingClientRect();

                var hcdTop = hcPreviewRect.top - 20; //Subtracting 35px of padding;
                var hcdRight = hcPreviewRect.left + 35 + hcDetails.width(); //Adding 35px of padding;
                var hcdBottom = hcPreviewRect.top + 35 + hcDetails.height(); //Adding 35px of padding;
                var hcdLeft = hcPreviewRect.top - 10; //Subtracting 35px of padding;

                if (options.openOnLeft || hcdRight > window.innerWidth) {
                    hcDetails.addClass("hc-details-open-left");
                } else {
                    hcDetails.removeClass("hc-details-open-left");
                }
                if (options.openOnTop || hcdBottom > window.innerHeight) {
                    hcDetails.addClass("hc-details-open-top");
                } else {
                    hcDetails.removeClass("hc-details-open-top");
                }
            }
        });
    };
})(jQuery);