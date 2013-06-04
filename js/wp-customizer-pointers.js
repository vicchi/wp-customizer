(function($) {
	$().ready(function() {
		var WPCustomizerTour = $.extend(WPCustomizerTour, {
			buttons: function(event, t) {
				button = $('<a id="pointer-close" class="button-secondary">' + WPCustomizerTour.button1 + '</a>');
				button.bind('click.pointer', function() {
					t.element.pointer('close');
				});
				return button;
			},
			close: function() {
				$.post(ajaxurl, {
					pointer: 'wp_customizer_pointer',
					action: 'dismiss-wp-pointer'
				});
			}
		});
		
		var setup = function() {
			$(WPCustomizerTour.id).pointer(WPCustomizerTour).pointer('open');
			if (WPCustomizerTour.button2) {
				$('#pointer-close').after('<a id="pointer-primary" class="button-primary">' + WPCustomizerTour.button2 + '</a>');
				$('#pointer-primary').click(function() {
					WPCustomizerTour.function;
				});
				$('#pointer-close').click(function() {
					$.post(ajaxurl, {
						pointer: 'wp_customizer_pointer',
						action: 'dismiss-wp-pointer'
					})
				});
			}
		};
		
		if (WPCustomizerTour.position && WPCustomizerTour.position.defer_loading) {
			$(window).bind('load.wp-pointers', setup);
		}
		
		else {
			setup();
		}
	});
})(jQuery);