<fieldset class="contentEditor__group contentEditor__group--image">
	<div class="contentEditor__imageInput">
		<input type="text" name="<?php echo $content->page . '__' . $content->name . '__' . $index ?>" id="<?php echo $content->page . '__' . $content->name . '__' . $index ?>" class="contentEditor__textInput contentEditor__textInput--image" value="<?php echo $value ?>">
		<button class="contentEditor__mediaBrowserLaunch" data-add-for="<?php echo $content->page . '__' . $content->name . '__' . $index ?>">Browse</button>
	</div>
	<?php if($content->minValues > 1 || ($content->maxValues > 1 || $content->maxValues == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
