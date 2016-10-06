<fieldset class="contentEditor__group contentEditor__group--wysiwyg">
	<label class="contentEditor__label">
		<?php echo preg_replace('/-/', ' ', $name); ?>
	</label>
	<div class="contentEditor__wysiwygInput">
		<input type="hidden" name="<?php echo $name ?>" id="<?php echo $name ?>" class="contentEditor__textInput contentEditor__textInput--wysiwyg">
		<div class="contentEditor__wysiwygPreview" id="<?php echo $name ?>-preview"></div>
		<button class="contentEditor__wysiwygEditorLaunch" data-edit-for="<?php echo $name ?>">Edit</button>
	</div>

</fieldset>
