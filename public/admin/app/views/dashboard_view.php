
	<nav class="groupTabs">
		<?php foreach ($groups as $group) : ?>
			<a href="#" id="tab-<?php echo $group->name ?>" class="groupTab<?php if($group->name == $current_group) { echo ' groupTab--active'; } ?>"><?php echo $group->name ?></a>
		<?php endforeach; ?>
		<a href="#" id="tab-settings" class="groupTab groupTab--right<?php if($current_group == 'settings') { echo ' groupTab--active'; } ?>"><span class="glyphicons glyphicons-cogwheel"></span></a>
	</nav>

	<main class="groupEditors">
		<?php foreach($groups as $group) : ?>
			<section class="groupEditor<?php if($group->name == $current_group) { echo ' groupEditor--visible'; } ?>" id="group-<?php echo $group->name ?>">
				<header class="groupHeader">
					<div class="notificationContainer">
						<p class="notification saveNotification" style="opacity:0;display:none;">Saved!</p>
					</div>
					<h1 class="groupName"><?php echo $group->name ?></h1>
					<a href="#" class="groupSaveButton">Save</a>
				</header>
				<form action="" method="POST" role="form">
					<input type="hidden" name="group" value="<?php echo $group->name ?>">
					<?php
						$this->draw_editors($group);
					?>
				</form>
			</section>
		<?php endforeach; ?>
		<section class="groupEditor<?php if($current_group == 'settings') { echo ' groupEditor--visible'; } ?>" id="group-settings">
			<h1>Settings</h1>
			<h3>YAML Operations</h3>
			<?php if($message == 'loadedTypes') { ?>
				<p class="notification"><strong>Loaded Types</strong></p>
			<?php } ?>
			<?php if($message == 'loadedGroups') { ?>
				<p class="notification"><strong>Loaded Groups</strong></p>
			<?php } ?>
			<a href="<?php echo ROOT_PATH ?>/type/load" class="settingsBtn">Reload Types</a>
			<a href="<?php echo ROOT_PATH ?>/group/load" class="settingsBtn">Reload Groups</a>
			<h3>Media Operations</h3>
			<a href="<?php echo ROOT_PATH ?>/media/reload" class="settingsBtn">Reload Media</a>
		</section>
	</main>
