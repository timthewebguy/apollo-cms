function initContentEditor() {

	var addTarget, removeTarget;

	function addContent(addFor) {

		var formData = new FormData(),
			xhr = new XMLHttpRequest();

		formData.append('data-guid', addFor);

		xhr.onload = function() {
			if(this.responseText == 'failure') {
				alert('You already have the maximum number of values for this data.');
			} else {
				addTarget.insertAdjacentHTML('beforebegin', this.responseText);
				contentEditorEvents();
			}
		};

		xhr.open('post', adminPath + '/content/add_content');
		xhr.send(formData);
	}

	function removeContent(removeFor, removeIndex) {
		var formData = new FormData(),
			xhr = new XMLHttpRequest();

		formData.append('data-guid', removeFor);
		formData.append('index', removeIndex);

		xhr.onload = function() {
			if(this.responseText == 'success') {
				removeTarget.style.opacity = "0";
				setTimeout(function() {
					removeTarget.parentElement.removeChild(removeTarget);
					contentEditorEvents();
				}, 150);
			} else {
				alert("You already have the minimum number of values for this data.");
				//console.log(this.responseText);
			}
		};

		xhr.open('post', adminPath + '/content/remove_content');
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
		addContent(this.dataset.addfor);
	}

	function contentRemoveButtonEvent(e) {
		e.preventDefault();
		removeTarget = this.parentElement.parentElement;
		removeContent(this.dataset.removeFor, this.dataset.removeIndex);
	}

	function contentInputChangeEvent() {
		this.addClass('did-change');
		this.ancestor('.groupEditor').find('.groupSaveButton')[0].addClass('canSave');
	}

	function contentEditorEvents() {

		$('.contentEditor__incrementor').removeEventListener('click', addContentButtonEvent, false);
		$('.contentEditorCustom__title').removeEventListener('click', customTypeOpenEvent, false);
		$('.contentEditor__mediaBrowserLaunch').removeEventListener('click', mediaBrowserButtonEvent, false);
		$('.contentEditor__toolbarBtn--remove').removeEventListener('click', contentRemoveButtonEvent, false);
		$('.contentEditor>fieldset>input').removeEventListener('input', contentInputChangeEvent, false);
		$('.contentEditor>fieldset>div>input').removeEventListener('input', contentInputChangeEvent, false);
		$('.contentEditor__wysiwyg').removeEventListener('input', contentInputChangeEvent, false);

		$('.contentEditor__incrementor').addEventListener('click', addContentButtonEvent, false);
		$('.contentEditorCustom__title').addEventListener('click', customTypeOpenEvent, false);
		$('.contentEditor__mediaBrowserLaunch').addEventListener('click', mediaBrowserButtonEvent, false);
		$('.contentEditor__toolbarBtn--remove').addEventListener('click', contentRemoveButtonEvent, false);
		$('.contentEditor>fieldset>input').addEventListener('input', contentInputChangeEvent, false);
		$('.contentEditor>fieldset>div>input').addEventListener('input', contentInputChangeEvent, false);
		$('.contentEditor__wysiwyg').addEventListener('input', contentInputChangeEvent, false);

		$('.contentEditor').loop(function(ce) {
			if(ce.find('> .contentEditor__group').length >= ce.dataset.maxItems) {
				ce.find('> .contentEditor__incrementor').loop(function(i) {
					i.addClass('disabled');
				});
			} else {
				ce.find('> .contentEditor__incrementor').loop(function(i) {
					i.removeClass('disabled');
				});
			}
		});
	}

	contentEditorEvents();


}
