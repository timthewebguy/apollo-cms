function initContentEditor() {
	$('.contentEditor__incrementor').addEventListener('click', function(e) {
		e.preventDefault();
		
	});

	$('.contentEditor__mediaBrowserLaunch').addEventListener('click', function(e) {
		e.preventDefault();
		loadMediaBrowser(this.dataset.addFor);
	});
}
