if ( typeof(jQuery) != 'undefined' ) {
    jQuery(document).ready(function($){

        var css_class = '';

        if ( 'undefined' != typeof iworks_position ) {
            if ( 'gradient' == iworks_position.style) {
                css_class = 'multiple';
            } else if ( 'transparent' == iworks_position.style) {
                css_class = 'single';
            }
            if ( '' != css_class ) {
                css_class += ' ';
            }
            css_class += iworks_position.style;
            css_class += ' ';
            css_class += ' position-' + iworks_position.position;
        }
        $('body').append('<progress value="0" id="reading-position-indicator" class="'+css_class+'"><div class="progress-container"><span class="progress-bar"></span></div></progress>');

        var getMax = function(){
            var end = $('.reading-position-indicator-end');
            if ( end.length ) {
                return parseInt( end.offset().top )- $(window).height() * 3 / 4;
            }
            return $(document).height() - $(window).height();
        }
        /**
         * getValue function to check window scroll
         */
        var getValue = function(){
            return $(window).scrollTop();
        }
            $(window).on( 'load', function(){
                progressBar.attr({ value: getValue() });
            }).on ( 'resize', function() {
                // On resize, both Max/Value attr needs to be calculated
                progressBar.attr({ max: getMax(), value: getValue() });
            });
        if ('max' in document.createElement('progress')) {
            var progressBar = $('#reading-position-indicator');
            progressBar.attr({ max: getMax() });
            $(document).on('scroll', function(){
                progressBar.attr({ value: getValue() });
            })
        } else {
            var progressBar = $('.progress-bar');
            var max = getMax();
            var value;
            var width;
            var getWidth = function() {
                value = getValue();
                width = (value/max) * 100;
                width = width + '%';
                return width;
            }
            var setWidth = function(){
                progressBar.attr({ value: getValue() })
            }
            $(document).on('scroll', setWidth);
            $(window).on('resize', function(){
                max = getMax();
                setWidth;
            })
            .on("load", setWidth);
        }
    });
}
