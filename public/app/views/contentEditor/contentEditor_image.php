<fieldset class="contentEditor__group contentEditor__group--image">
	<label for="<?php echo $name ?>" class="contentEditor__label">
		<?php echo preg_replace('/-/', ' ', $name); ?>
	</label>
	<div class="contentEditor__imageInput">
		<input type="text" name="<?php echo $name ?>" id="<?php echo $name ?>" class="contentEditor__textInput contentEditor__textInput--image">
		<button class="contentEditor__mediaBrowserLaunch" data-add-for="<?php echo $name ?>">Browse</button>
	</div>

</fieldset>
