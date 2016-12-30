<div class="mediaBrowser">
	<div class="mediaBrowser__inner">
		<header class="mediaBrowser__header">
			<h1>Media</h1>
			<button class="mediaBrowser__close"><span class="glyphicons glyphicons-remove"></span></button>
		</header>
		<section class="mediaBrowser__body">
			<?php 
			foreach ($all_media as $media) {
				include 'mediaObject_view.php';
			}
			?>
		</section>
		<footer class="mediaBrowser__footer">
			<input type="file" id="mediaBrowser__uploadInput" name="mediaBrowser__uploadInput" class="mediaBrowser__uploadInput">
			<label for="mediaBrowser__uploadInput" class="mediaBrowser__upload">Upload</label>
			<progress value="0" max="100" class="mediaBrowser__uploadProgress"></progress>
			<button class="mediaBrowser__select">Select</button>
			<button class="mediaBrowser__cancel">Cancel</button>
		</footer>
	</div>
</div>
