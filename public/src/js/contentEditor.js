function initContentEditor() {
	$('.contentEditor__incrementor').addEventListener('click', function(e) {
		e.preventDefault();
		
	});

	$('.contentEditor__mediaBrowserLaunch').addEventListener('click', function(e) {
		e.preventDefault();
		loadMediaBrowser(this.dataset.addFor);
	});

	var glyphicon = function(name) { return '<span class="glyphicons glyphicons-' + name + '"></span>' };

	var editor = new MediumEditor('.contentEditor__wysiwyg', {
		toolbar: {
			buttons: [{ name:'bold', contentDefault: glyphicon('bold')}, 
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


	$('.contentEditorCustom__title').addEventListener('click', function() {
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
	}, false);/**/
}
