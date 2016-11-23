
<fieldset class="contentEditor__group contentEditor__group--custom">
	<section class="contentEditorCustom__container">
		<div class="contentEditorCustom__title"><?php echo $index; ?></div>
		<div class="contentEditorCustom__inner">
			<?php 
				$this->draw_custom_editor($value, $content, $index); 
			?>
		</div>
	</section>
</fieldset>


