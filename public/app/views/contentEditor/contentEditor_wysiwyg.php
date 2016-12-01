<fieldset class="contentEditor__group contentEditor__group--wysiwyg">
	<div class="contentEditor__wysiwyg" data-name="<?php echo $content->page . '__' . $content->name . '__' . $index ?>">
		<?php echo $value; ?>
	</div>
	<?php if($content->minValues > 1 || ($content->maxValues > 1 || $content->maxValues == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
