
var loadTarget;

function initMediaBrowser() {
	var mediaBrowser = $('.mediaBrowser');
	//close events
	$('.mediaBrowser__close').addEventListener('click', closeMediaBrowser);
	$('.mediaBrowser__cancel').addEventListener('click', closeMediaBrowser);

	//upload events
	mediaBrowser.addEventListener('dragover', function(e) {
		if(mediaBrowser.hasClass('visible')) {
			mediaBrowser.addClass('dragHover');
		}
		e.preventDefault();
	});

	mediaBrowser.addEventListener('dragleave', function(e) {
		if(mediaBrowser.hasClass('visible')) {
			mediaBrowser.removeClass('dragHover');
		}
		e.preventDefault();
	});

	mediaBrowser.addEventListener("drop", function(e) {
		e.stopPropagation();
		e.preventDefault();
		mediaBrowser.removeClass('dragHover');
		validateFileUpload(e.dataTransfer.files);
	});

	$('.mediaBrowser__uploadInput').addEventListener('change', function() {
		validateFileUpload(this.files);
	});

	$('.mediaBrowser__select').addEventListener('click', function() {
		console.log($(loadTarget));
		$(loadTarget).value = $('.media__radio:checked').value;
		$(loadTarget).dataset.valueName = $('.media__radio:checked').id;
		$(loadTarget).fire('input');
		$('.media__radio:checked').checked = false;
		$('.mediaBrowser').removeClass('visible');
	});

	deleteButtonEvent();

}

function closeMediaBrowser() {
	$('.mediaBrowser').removeClass('visible');
	$('.media__radio:checked').checked = false;
}

function validateFileUpload(files) {
	var exts = new Array('jpg', 'png', 'gif', 'pdf');
	var filesToUpload = new Array();

	for(var i = 0; i < files.length; i++) {
		var fileNameArray = files[i].name.split('.');
		var ext = fileNameArray.pop();
		var name = fileNameArray.join('');
		if(exts.indexOf(ext.toLowerCase()) !== -1 ) {
			filesToUpload.push(files[i]);
		} else {
			window.alert("Your File "+name+" is of type "+ext+" which is not allowed. Only jpg, png, gif, or pdf. It will not be uploaded.");
		}
	}
	if(filesToUpload.length > 0) {
		uploadFiles(filesToUpload);
	}
}

function uploadFiles (files) {
	$('.mediaBrowser__uploadProgress').value = 0;
	var formData = new FormData(),
			xhr = new XMLHttpRequest();

	for(var i = 0; i < files.length; i++) {
		formData.append('uploaded['+i+']', files[i]);
	}

	xhr.onload = function() {
		updateFileList(this.responseText);
	};

	xhr.addEventListener('progress', function(e) {
		if (e.lengthComputable) {
    	$('.mediaBrowser__uploadProgress').value = e.loaded / e.total * 100;
    }
	});

	xhr.open('post', adminPath + '/media/upload');
	xhr.send(formData);
}

function updateFileList(data) {
	$('.mediaBrowser__body').innerHTML += data;
	deleteButtonEvent();
}

function deleteButtonEvent() {
	$('.media__delete').removeEventListener('click', deleteMedia, false);
	$('.media__delete').addEventListener('click', deleteMedia, false);
}

function deleteMedia() {

	if(window.confirm("Are you sure you want to delete this media?")) {
		var formData = new FormData(),
				xhr = new XMLHttpRequest();

		formData.append('delete_id', this.dataset.deleteFor);

		xhr.open('post', adminPath + '/media/delete');
		xhr.send(formData);

		$('#media-' + this.dataset.deleteFor).addClass('delete');
	}
}

function loadMediaBrowser(target) {
	loadTarget = '#' + target;
	if($(loadTarget).value != '' && $(loadTarget).dataset.valueName != '') {
		$('#' + $(loadTarget).dataset.valueName).checked = true;
	}
	$('.mediaBrowser').addClass('visible');
}
