<fieldset class="contentEditor__group contentEditor__group--text">
	<label for="<?php echo $name ?>" class="contentEditor__label">
		<?php echo preg_replace('/-/', ' ', $name); ?>
	</label>
	<input type="text" name="<?php echo $name ?>" id="<?php echo $name ?>" class="contentEditor__textInput">
</fieldset>
