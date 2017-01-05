
<div class="contentEditor" id="<?php echo $content->group . '_' . $content->name ?>" data-min-items="<?php echo $data->min ?>" data-max-items="<?php echo $data->max ?>" data-data-guid="<?php echo $data->guid; ?>">
	<label class="contentEditor__label" title="<?php echo $description ?>"><?php echo preg_replace('/([0-9\-(\_\_)])/', ' ', $name); ?></label>
	<?php
	if($data->min == 1 && $data->max == 1) {
		$index = 0;
		$value = $data->value;
		$valueGUID = $data->valueGUID;
		include $view;
	} else {
		for($index = 0; $index < count($data->value); $index++) {
			$value = $data->value[$index];
			$valueGUID = $data->valueGUID[$index];
			include $view;
		}
	}
	?>
	<?php if($data->max != 1) : ?>
		<button class="contentEditor__incrementor" type="button" data-addfor="<?php echo $data->guid ?>">
			<span class="glyphicons glyphicons-plus"></span>
		</button>
	<?php endif; ?>
</div>
