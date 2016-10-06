
	<nav class="pageTabs">
		<?php foreach ($pages as $name => $page) : ?>
			<a href="#" id="tab-<?php echo $name ?>" class="pageTab<?php if($name == $current_page) { echo ' pageTab--active'; } ?>"><?php echo $name ?></a>
		<?php endforeach; ?>
	</nav>

	<main class="pageEditors">
		<?php foreach($pages as $name => $page) : ?>
			<section class="pageEditor<?php if($name == $current_page) { echo ' pageEditor--visible'; } ?>" id="page-<?php echo $name ?>">
				<h1 class="pageName"><?php echo $name ?></h1>
				<form action="" method="POST" role="form">
					<input type="hidden" name="page" value="<?php echo $name ?>">
					<?php 
						foreach($page as $content_name => $content_data) {
							$this->draw_content_editor($content_name, $content_data, $name);
						}  
					?>
				</form>
			</section>
		<?php endforeach; ?>
	</main>

