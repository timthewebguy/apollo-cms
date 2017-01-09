<fieldset class="contentEditor__group contentEditor__group--wysiwyg">
	<div class="contentEditor__wysiwyg" data-name="<?php echo $valueGUID ?>" data-index="<?php echo $index ?>" id="<?php echo $valueGUID ?>">
		<?php echo $value; ?>
	</div>
	<?php if($data->min > 1 || ($data->max > 1 || $data->max == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
