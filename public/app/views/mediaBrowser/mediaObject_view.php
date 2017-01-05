<input class="media__radio" type="radio" name="selected-media" id="media-radio-<?php echo $media->id ?>" value="<?php echo $media->path ?>">
<label class="media" id="media-<?php echo $media->id ?>" for="media-radio-<?php echo $media->id ?>">
	<div class="media__thumbnail" style="background:url(<?php echo $media->path ?>) no-repeat center/contain"></div>
	<nav class="media__toolbar">
		<button class="media__delete" data-delete-for="<?php echo $media->id ?>"><span class="glyphicons glyphicons-bin"></span></button>
	</nav>
</label>
