
<div class="contentEditor" id="<?php echo 'content__' . $content->page . '__' . $content->name ?>" data-min-items="<?php echo $content->minValues ?>" data-max-items="<?php echo $content->maxValues ?>" data-guid="<?php echo $content->guid; ?>">
	<label class="contentEditor__label" title="<?php echo $content->description ?>"><?php echo preg_replace('/([0-9\-(\_\_)])/', ' ', $content->name); ?></label>
	<?php
	foreach($content->values as $index => $value) {
		include $view;
	}
	?>
	<?php if($content->maxValues != 1) : ?>
		<button class="contentEditor__incrementor" type="button" data-addfor="<?php echo $content->page . '__' . $content->name ?>">
			<span class="glyphicons glyphicons-plus"></span>
		</button>
	<?php endif; ?>
</div>
