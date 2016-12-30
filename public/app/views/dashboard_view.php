
	<nav class="pageTabs">
		<?php foreach ($pages as $page) : ?>
			<a href="#" id="tab-<?php echo $page->name ?>" class="pageTab<?php if($page->name == $current_page) { echo ' pageTab--active'; } ?>"><?php echo $page->name ?></a>
		<?php endforeach; ?>
	</nav>

	<main class="pageEditors">
		<?php foreach($pages as $page) : ?>
			<section class="pageEditor<?php if($page->name == $current_page) { echo ' pageEditor--visible'; } ?>" id="page-<?php echo $page->name ?>">
				<h1 class="pageName"><?php echo $page->name ?></h1>
				<form action="" method="POST" role="form">
					<input type="hidden" name="page" value="<?php echo $page->name ?>">
					<?php 
						$this->draw_editors($page); 
					?>
				</form>
			</section>
		<?php endforeach; ?>
	</main>

