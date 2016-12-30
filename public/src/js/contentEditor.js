function initContentEditor() {

	var addTarget, removeTarget;

	function addContent(addFor, guid) {

		var formData = new FormData(),
			xhr = new XMLHttpRequest();

		formData.append('data', addFor);
		formData.append('guid', guid);

		xhr.onload = function() {
			addTarget.insertAdjacentHTML('beforebegin', this.responseText);
			contentEditorEvents();
		};

		xhr.open('post', '/content/add_content');
		xhr.send(formData);
	}

	function removeContent(removeFor, removeIndex) {
		var formData = new FormData(),
			xhr = new XMLHttpRequest();

		formData.append('target', removeFor);
		formData.append('index', removeIndex);

		xhr.onload = function() {
			if(this.responseText == 'success') {
				removeTarget.parentElement.removeChild(removeTarget);
			} else {
				//alert("Something went wrong in deleting the content. Please try again.");
				console.log(this.responseText);
			}
		};

		xhr.open('post', '/content/remove_content');
		xhr.send(formData);
	}

	var glyphicon = function(name) { return '<span class="glyphicons glyphicons-' + name + '"></span>' };

	var editor = new MediumEditor('.contentEditor__wysiwyg', {
		toolbar: {
			buttons: [{ name:'bold', contentDefault:glyphicon('bold')},
								{ name:'italic', contentDefault:glyphicon('italic')},
								{ name:'anchor', contentDefault:glyphicon('link')},
								{ name:'justifyLeft', contentDefault:glyphicon('align-left')},
								{ name:'justifyCenter', contentDefault:glyphicon('align-center')},
								{ name:'justifyRight', contentDefault:glyphicon('align-right')},
								{ name:'orderedlist', contentDefault:glyphicon('list-numbered')},
								{ name:'unorderedlist', contentDefault:glyphicon('list')},
								'h1', 'h2', 'h3'],
			buttonLabels:'fontawesome',
			static:true,
			updateOnEmptySelection:true
		},
		placeholder:false
	});

	function customTypeOpenEvent() {
		var p = this.parentElement;
		var i = this.nextElement();

		p.style.height = (i.getBoundingClientRect().height + this.offsetHeight) + 'px';

		if(p.hasClass('open')) {
			p.removeClass('open');
			setTimeout(function() { p.style.height = '2.5em' ;}, 1);
		} else {
			p.addClass('open');
			setTimeout(function() { p.style.height = 'auto'; }, 300);
		}
	}

	function mediaBrowserButtonEvent(e) {
		e.preventDefault();
		loadMediaBrowser(this.dataset.addFor);
	}

	function addContentButtonEvent(e) {
		e.preventDefault();
		addTarget = this;
		addContent(this.dataset.addfor, this.parentElement.dataset.guid);
	}

	function contentRemoveButtonEvent(e) {
		e.preventDefault();
		removeTarget = this.parentElement.parentElement;
		removeContent(this.dataset.removeFor, this.dataset.removeIndex);
	}

	function contentEditorEvents() {

		$('.contentEditor__incrementor').removeEventListener('click', addContentButtonEvent, false);
		$('.contentEditorCustom__title').removeEventListener('click', customTypeOpenEvent, false);
		$('.contentEditor__mediaBrowserLaunch').removeEventListener('click', mediaBrowserButtonEvent, false);
		$('.contentEditor__toolbarBtn--remove').removeEventListener('click', contentRemoveButtonEvent, false);

		$('.contentEditorCustom__title').addEventListener('click', customTypeOpenEvent, false);
		$('.contentEditor__mediaBrowserLaunch').addEventListener('click', mediaBrowserButtonEvent, false);
		$('.contentEditor__incrementor').addEventListener('click', addContentButtonEvent, false);
		$('.contentEditor__toolbarBtn--remove').addEventListener('click', contentRemoveButtonEvent, false);

		$('.contentEditor').loop(function(ce) {
			if(ce.find('> .contentEditor__group').length >= ce.dataset.maxItems) {
				ce.find('> .contentEditor__incrementor').loop(function(i) {
					i.addClass('disabled');
				});
			}
		});
	}

	contentEditorEvents();


}
