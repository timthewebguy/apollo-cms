function initSaveButtons() {
	$('.groupSaveButton').addEventListener('click', function(e) {
		e.preventDefault();

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
				alert('Content Successfuly Saved.');
			} else {
				alert('Something Went Wrong. Please Try Again.');
			}
		};

		xhr.open('post', '/group/save');
		xhr.send(formData);
	}, false);
}
