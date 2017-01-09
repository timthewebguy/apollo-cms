function initNotifications() {
	$('.notification').addEventListener('click', function() {
		var n = this;
		n.style.opacity = "0";
		setTimeout(function() {
			n.style.display = "none";
		}, 150);
	}, false);
}
