<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

/**
* Group Model
*/
class Group
{
	public $id;
	public $name;
	public $slug;

	function __construct($id, $name, $slug) {
		//include the database object for CRUD operations.
		require_once(APP_PATH . '/system/database.php');
		require_once(CONTROLLERS . '/ContentController.php');

		//set object variables
		$this->id = $id;
		$this->name = $name;
		$this->slug = $slug;
	}

	function Update() {
		DB::Query("UPDATE " . GROUPS_TABLE . " SET name='{$this->name}', slug='{$this->slug}' WHERE id={$this->id}");
	}

	function Delete() {
		DB::Query("DELETE FROM " . GROUPS_TABLE . " WHERE id='{$this->id}'");

		$group_content = ContentController::RetrieveContent(['group'=>$this->slug], null, true);
		if($group_content) {
			foreach($group_content as $g) {
				$g->Delete();
			}
		}
	}
}
