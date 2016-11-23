function initNavigation() {
	$('.pageTab').addEventListener('click', function(e) {
		e.preventDefault();
		var enterClass, 
				exitClass, 
				currentTab = $('.pageTab--active'),
				enterPage = $('#' + this.id.replace('tab', 'page')), 
				exitPage = $('#' + currentTab.id.replace('tab', 'page'));

		if(currentTab == this) {
			return;
		}
		if(this.isBefore(currentTab)) {
			enterClass = 'pageEditor--enterLeft';
			exitClass = 'pageEditor--exitRight';
		} else {
			enterClass = 'pageEditor--enterRight';
			exitClass = 'pageEditor--exitLeft';
		}
		enterPage.addClass(enterClass).addClass('pageEditor--visible');
		exitPage.addClass(exitClass).removeClass('pageEditor--visible');
		$('.pageTab--active').removeClass('pageTab--active');
		this.addClass('pageTab--active');

		setTimeout(function() {
			$('.pageEditor').loop(function(p) {
				p.removeClass('pageEditor--enterRight')
					.removeClass('pageEditor--enterLeft')
					.removeClass('pageEditor--exitRight')
					.removeClass('pageEditor--exitLeft');
			});

		}, 150);
	});
}
