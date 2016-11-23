<fieldset class="contentEditor__group contentEditor__group--image">
	<div class="contentEditor__imageInput">
		<input type="text" name="<?php echo $content->page . '-' . $content->name . '-' . $index ?>" id="<?php echo $content->page . '-' . $content->name . '-' . $index ?>" class="contentEditor__textInput contentEditor__textInput--image" value="<?php echo $value ?>">
		<button class="contentEditor__mediaBrowserLaunch" data-add-for="<?php echo $content->page . '-' . $content->name . '-' . $index ?>">Browse</button>
	</div>

</fieldset>
