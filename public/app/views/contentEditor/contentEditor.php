
<div class="contentEditor" id="content-<?php echo $content->name ?>">
	<label class="contentEditor__label" title="<?php echo $content->description ?>"><?php echo preg_replace('/([0-9\-])/', ' ', $content->name); ?></label>
	<?php 
	foreach($content->values as $index => $value) {
		include $view;
	}
	?>
	<?php if($content->maxValues != 1) : ?>
		<button class="contentEditor__incrementor" type="button" data-addfor="<?php echo $name . '-group' ?>">
			<span class="glyphicons glyphicons-plus"></span>
		</button>
	<?php endif; ?>
</div>
