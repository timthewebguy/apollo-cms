
<div class="contentEditor" id="content-<?php echo $name ?>">
	<?php include $view; ?>
	<?php if($data['max-items'] != 1) : ?>
		<button class="contentEditor__incrementor" type="button" data-addfor="<?php echo $name . '-group' ?>">
			<span class="glyphicons glyphicons-plus"></span>
		</button>
	<?php endif; ?>
</div>
