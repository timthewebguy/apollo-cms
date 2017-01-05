
	<nav class="pageTabs">
		<?php foreach ($groups as $group) : ?>
			<a href="#" id="tab-<?php echo $group->name ?>" class="pageTab<?php if($group->name == $current_page) { echo ' pageTab--active'; } ?>"><?php echo $group->name ?></a>
		<?php endforeach; ?>
	</nav>

	<main class="pageEditors">
		<?php foreach($groups as $group) : ?>
			<section class="pageEditor<?php if($group->name == $current_page) { echo ' pageEditor--visible'; } ?>" id="page-<?php echo $group->name ?>">
				<h1 class="pageName"><?php echo $group->name ?></h1>
				<form action="" method="POST" role="form">
					<input type="hidden" name="page" value="<?php echo $group->name ?>">
					<?php
						$this->draw_editors($group);
					?>
				</form>
			</section>
		<?php endforeach; ?>
	</main>
