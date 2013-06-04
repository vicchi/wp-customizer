(function($) {
	$().ready(function() {
		$('#wp-customizer-functions-enabled').click(function() {
			$('#wp-customizer-functions-wrap').toggle(this.checked);
		});
		$('#wp-customizer-admin-functions-enabled').click(function() {
			$('#wp-customizer-admin-functions-wrap').toggle(this.checked);
		});
		$('#wp-customizer-common-functions-enabled').click(function() {
			$('#wp-customizer-common-functions-wrap').toggle(this.checked);
		});

		$('#wp-customizer-scripts-enabled').click(function() {
			$('#wp-customizer-scripts-wrap').toggle(this.checked);
		});
		$('#wp-customizer-admin-scripts-enabled').click(function() {
			$('#wp-customizer-admin-scripts-wrap').toggle(this.checked);
		});
		$('#wp-customizer-common-scripts-enabled').click(function() {
			$('#wp-customizer-common-scripts-wrap').toggle(this.checked);
		});

		$('#wp-customizer-css-enabled').click(function() {
			$('#wp-customizer-css-wrap').toggle(this.checked);
		});
		$('#wp-customizer-admin-css-enabled').click(function() {
			$('#wp-customizer-admin-css-wrap').toggle(this.checked);
		});
		$('#wp-customizer-common-css-enabled').click(function() {
			$('#wp-customizer-common-css-wrap').toggle(this.checked);
		});
	});
})(jQuery);