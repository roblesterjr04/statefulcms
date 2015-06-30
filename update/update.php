<?
	
//if (!defined(CP_WORKING_DIR)) exit('Direct access not allowed');

class CP_Update {
	
	public $version;
	
	public function __construct() {
		$this->version = root()->settings->get('running_sha');
	}
	
	public function has_update() {
		
		require_once(__DIR__ . '/client/GitHubClient.php');
	
		$owner = 'roblesterjr04';
		$repo = 'statefulcms';
		
		$client = new GitHubClient();
		$client->setPage();
		$client->setPageSize(1);
		$commits = $client->repos->commits->listCommitsOnRepository($owner, $repo);
		
		foreach($commits as $commit)
		{
			$running_sha = $this->version;
			$current_sha = $commit->getSha();
			
			return $running_sha == $current_sha ? false : $current_sha;
		}
		
	}
	
	private function delTree($dir) { 
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) { 
			(is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file"); 
		} 
		return rmdir($dir); 
	}
	
	public function update_core($update) {
		$package = file_get_contents('https://github.com/roblesterjr04/statefulcms/archive/master.zip');
		file_put_contents(__DIR__ . '/update_package.zip', $package);
		
		mkdir(__DIR__ . '/statefulcms-master');
		
		$zip = zip_open(__DIR__ . '/update_package.zip');
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				$entry_name = zip_entry_name($zip_entry);
				$directory = substr($entry_name, strlen($entry_name) - 1, 1) == '/';
				$fp = fopen(__DIR__ . '/'.$entry_name, "w");
				if (zip_entry_open($zip, $zip_entry, "r")) {
					if ($directory) {
						mkdir(__DIR__ . '/'.$entry_name);
					} else {
						$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
						fwrite($fp,"$buf");
						zip_entry_close($zip_entry);
						fclose($fp);
					}
				}
			}
			zip_close($zip);
		}
		
		rename(__DIR__ . '/statefulcms-master/core', __DIR__ . '/../core');
		
		$this->delTree(__DIR__ . '/statefulcms-master');
		unlink(__DIR__ . '/update_package.zip');
		
		root()->settings->set('running_sha', $update);
		$this->version = $update;
		return true;
	}

}

class Update_Control extends CP_Object {
	
	public function __construct() {
		parent::__construct('Update_Control');
	}
	
	public function title() {
		return 'Updates';
	}
	
	public function update_button_click($sender) {
		$updating = root()->update->update_core($this->state->update_version);
		$this->controls->update_label->val('Done.');
	}
	
	public function admin() {
		$update = root()->update->has_update();
		
		if ($update) {
			$this->state->update_version = $update;
			$button = new CP_Button('update_button', 'Update Now', ['class'=>'btn btn-success'], $this);
			$label = new CP_Label('update_label', '', [], $this);
			?>
				<p>There is an update available</p>
				<? $button->display() ?>
				<? $label->display() ?>
			<?
		} else {
			?>
				<p>You are using the latest version.</p>
			<?
		}
	}
	
}