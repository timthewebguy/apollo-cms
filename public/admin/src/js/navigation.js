function initNavigation() {
	$('.groupTab').addEventListener('click', function(e) {
		e.preventDefault();
		var enterClass,
				exitClass,
				currentTab = $('.groupTab--active'),
				enterPage = $('#' + this.id.replace('tab', 'group')),
				exitPage = $('#' + currentTab.id.replace('tab', 'group'));

		if(currentTab == this) {
			return;
		}
		if(this.isBefore(currentTab)) {
			enterClass = 'groupEditor--enterLeft';
			exitClass = 'groupEditor--exitRight';
		} else {
			enterClass = 'groupEditor--enterRight';
			exitClass = 'groupEditor--exitLeft';
		}
		enterPage.addClass(enterClass).addClass('groupEditor--visible');
		exitPage.addClass(exitClass).removeClass('groupEditor--visible');
		$('.groupTab--active').removeClass('groupTab--active');
		this.addClass('groupTab--active');
		window.history.pushState(null, null, "/dashboard/group/" + this.id.replace('tab-', ''));

		setTimeout(function() {
			$('.groupEditor').loop(function(p) {
				p.removeClass('groupEditor--enterRight')
					.removeClass('groupEditor--enterLeft')
					.removeClass('groupEditor--exitRight')
					.removeClass('groupEditor--exitLeft');
			});

		}, 150);
	});
}
