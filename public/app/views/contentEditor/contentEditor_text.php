<fieldset class="contentEditor__group contentEditor__group--text">
	<input type="text" name="<?php echo $content->page . '__' . $content->name . '__' . $index ?>" id="<?php echo $content->page . '__' . $content->name . '__' . $index ?>" class="contentEditor__textInput" value="<?php echo $value ?>">
	<?php if($content->minValues > 1 || ($content->maxValues > 1 || $content->maxValues == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
