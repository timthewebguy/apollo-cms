var adminPath = '/admin';

function init() {
	initNavigation();
	initContentEditor();
	initMediaBrowser();
	initSaveButtons();
	initNotifications();
}

window.addEventListener('load', init, false);
