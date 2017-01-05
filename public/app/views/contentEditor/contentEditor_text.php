<fieldset class="contentEditor__group contentEditor__group--text">
	<input type="text" name="<?php echo $valueGUID ?>" id="<?php echo $valueGUID ?>" class="contentEditor__textInput" data-index="<?php echo $index ?>" value="<?php echo $value ?>">
	<?php if($data->min > 1 || ($data->max > 1 || $data->max == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
