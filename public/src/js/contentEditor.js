function initContentEditor() {
	$('.contentEditor__incrementor').addEventListener('click', function(e) {
		e.preventDefault();
		
	});

	$('.contentEditor__mediaBrowserLaunch').addEventListener('click', function(e) {
		e.preventDefault();
		loadMediaBrowser(this.dataset.addFor);
	});

	var editor = new MediumEditor('.contentEditor__wysiwyg', {
		toolbar: {
			buttons: [{ name:'bold', contentDefault: '<span class="glyphicons glyphicons-bold"></span>'}, 
								{ name:'italic', contentDefault:'<span class="glyphicons glyphicons-italic"></span>'},
								{ name:'anchor', contentDefault:'<span class="glyphicons glyphicons-link"></span>'},
								{ name:'justifyLeft', contentDefault:'<span class="glyphicons glyphicons-align-left"></span>'},
								{ name:'justifyCenter', contentDefault:'<span class="glyphicons glyphicons-align-center"></span>'},
								{ name:'justifyRight', contentDefault:'<span class="glyphicons glyphicons-align-right"></span>'},
								{ name:'justifyFull', contentDefault:'<span class="glyphicons glyphicons-justify"></span>'},
								{ name:'orderedlist', contentDefault:'<span class="glyphicons glyphicons-list"></span>'},
								{ name:'unorderedlist', contentDefault:'<span class="glyphicons glyphicons-list-numbered"></span>'},
								'h1', 'h2', 'h3'],
			buttonLabels:'fontawesome',
			static:true,
			updateOnEmptySelection:true
		},
		placeholder:false
	});
}
