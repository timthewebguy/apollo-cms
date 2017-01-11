<fieldset class="contentEditor__group contentEditor__group--image">
	<div class="contentEditor__imageInput">
		<input type="text" name="<?php echo $valueGUID ?>" id="<?php echo $valueGUID ?>" class="contentEditor__textInput contentEditor__textInput--image" data-index="<?php echo $index ?>" value="<?php echo $value ?>">
		<button class="contentEditor__mediaBrowserLaunch" data-add-for="<?php echo $valueGUID ?>">Browse</button>
	</div>
	<?php if($data->min > 1 || ($data->max > 1 || $data->max == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
