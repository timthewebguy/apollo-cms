function initSaveButtons() {
	var saveBtn;
	$('.groupSaveButton').addEventListener('click', function(e) {
		e.preventDefault();
		saveBtn = this;

		var formData = new FormData(),
				xhr = new XMLHttpRequest();

		this.ancestor('.groupEditor').find('.did-change').loop(function(input) {
			if(!input.hasClass('contentEditor__wysiwyg')) {
				formData.append('changeData[' + input.id + ']', input.value);
			} else {
				formData.append('changeData[' + input.id + ']', input.innerHTML);
			}
		});

		xhr.onload = function() {
			if(this.responseText == 'success') {
				saveBtn.removeClass('canSave');
				var n = saveBtn.parentElement.find('.saveNotification')[0];
				n.style.opacity = '1';
				n.style.display = 'block';
			} else {
				//alert('Something Went Wrong. Please Try Again.');
				console.log(this.responseText);
			}
		};

		xhr.open('post', '/group/save');
		xhr.send(formData);
	}, false);
}
