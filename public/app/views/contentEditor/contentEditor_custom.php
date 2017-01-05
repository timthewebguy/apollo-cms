
<fieldset class="contentEditor__group contentEditor__group--custom">
	<section class="contentEditorCustom__container">
		<div class="contentEditorCustom__title"><?php echo $index; ?></div>
		<div class="contentEditorCustom__inner">
			<?php
				$this->draw_custom_editor($value, $data->type);
			?>
		</div>
	</section>
	<?php if($data->min > 1 || ($data->max > 1 || $data->max == 'unlimited')) {include 'contentEditor_toolbar.php';} ?>
</fieldset>
