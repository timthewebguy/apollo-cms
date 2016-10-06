function initContentEditor() {
	$('.contentEditor__incrementor').addEventListener('click', function(e) {
		e.preventDefault();
		
	});

	$('.contentEditor__mediaBrowserLaunch').addEventListener('click', function(e) {
		e.preventDefault();
		loadMediaBrowser(this.dataset.addFor);
	});
}



function init() {
	initNavigation();
	initContentEditor();
	initMediaBrowser();
}

window.addEventListener('load', init, false);



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
		$(loadTarget).value = $('.media__radio:checked').value;
		$(loadTarget).dataset.valueName = $('.media__radio:checked').id;
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
	var exts = new Array('jpg', 'png', 'gif', 'pdf', 'psd');
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

	xhr.open('post', '/media/upload');
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

		xhr.open('post', '/media/delete');
		xhr.send(formData);

		$('#media-' + this.dataset.deleteFor).addClass('delete');
	}
}

function loadMediaBrowser(target) {
	loadTarget = '#' + target;
	if($(loadTarget).value != '') {
		$('#' + $(loadTarget).dataset.valueName).checked = true;
	}
	$('.mediaBrowser').addClass('visible');
}



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
			$('.pageEditor').forEach(function(p) {
				p.removeClass('pageEditor--enterRight')
					.removeClass('pageEditor--enterLeft')
					.removeClass('pageEditor--exitRight')
					.removeClass('pageEditor--exitLeft');
			});

		}, 150);
	});
}


function $(sel) {
	var query = document.querySelectorAll(sel);
	if(query.length == 1) {
		return query.item(0);
	} else {
		return query;
	}
}

Element.prototype.nodeNumber = function() {
	var el = this, node=0;
	while( (el = el.previousElementSibling) != null) {
		node++;
	}
	return node;
};

Element.prototype.isBefore = function(el) {
	if(this.parentNode != el.parentNode) {
		console.log('not the same parent');
		return false;
	}
	if(this.nodeNumber() > el.nodeNumber()) {
		return false;
	}
	return true;
};

Element.prototype.addClass = function(_class) {
	if(this.classList) {
		this.classList.add(_class);
		return this;
	} else {
		var classes = this.className.split(' ');
		if(classes.indexOf(classToAdd) === -1) {
			this.className = this.className + (classes.length > 0 ? ' ' : '') + classToAdd;
		}
		return this;
	}
};

Element.prototype.removeClass = function(_class) {
	if(this.classList) {
		this.classList.remove(_class);
		return this;
	} else {
		var finalClassName = '';
		this.className.split(' ').forEach(function(cl) {
			if(cl != _class) { finalClassName += cl + ' ' }
		});
		this.className = finalClassName.replace(/[ /t]+$/, '');
		return this;	
	}
};

Element.prototype.hasClass = function(_class) {
	if(this.classList) {
		return this.classList.contains(_class);
	} else {
		return this.className.split(' ').indexOf(_class) != -1;
	}
};

NodeList.prototype.addEventListener = function(event, callback, capture) {
	this.forEach(function (n) {
		n.addEventListener(event, callback, capture || false);
	});
};
NodeList.prototype.removeEventListener = function(event, callback, capture) {
	this.forEach(function (n) {
		n.removeEventListener(event, callback, capture || false);
	});
};

