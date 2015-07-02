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
	
	public function check_for_update() {
		
		$git = GIT_BRANCH;
		
		$data = file_get_contents("https://raw.githubusercontent.com/roblesterjr04/statefulcms/$git/update/version.txt");
		
		$data_lines = explode("\n", $data);
		$line = explode(":", $data_lines[0]);
		$version = trim($line[1]);
		
		return $version == $this->version ? false : $version;
		
	}
	
	private function delTree($dir) { 
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) { 
			(is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file"); 
		} 
		return rmdir($dir); 
	}
	
	private function replaceTree($dir, $new) {
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) { 
			(is_dir("$dir/$file")) ? $this->replaceTree("$dir/$file") : rename("$dir/$file", "$new/$file"); 
		} 
	}
	
	public function update_core($update) {
		$git = GIT_BRANCH;
		$package = file_get_contents("https://github.com/roblesterjr04/statefulcms/archive/$git.zip");
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
		
		$data = file_get_contents(__DIR__ . '/statefulcms-master/update/version.txt');
		
		$data_lines = explode("\n", $data);
		
		foreach ($data_lines as $line) {
			$line_parts = explode(':', $line);
			if (isset($line_parts[0]) && isset($line_parts[1])) {
				if ($line_parts[0] == 'Replace') {
					$value = trim($line_parts[1]);
					if (is_dir(__DIR__ . '/statefulcms-master/' . $value)) {
						$this->replaceTree(__DIR__ . '/statefulcms-master/' . $value, __DIR__ . '/../' . $value);
					} else {
						rename(__DIR__ . '/statefulcms-master/' . $value, __DIR__ . '/../' . $value);
					}
				}
				if ($line_parts[0] == 'Setting') {
					$value = trim($line_parts[1]);
					$setting = explode('=', $value);
					root()->settings->set($setting[0], $setting[1]);
				}
				if ($line_parts[0] == 'Remove') {
					$value = trim($line_parts[1]);
					if (is_dir(__DIR__ . '/../' . $value)) {
						$this->delTree(__DIR__ . '/../' . $value);
					} else {
						unlink(__DIR__ . '/../' . $value);
					}
				}
			}
		}
		
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
		$count = 0;
		if (root()->update->check_for_update()) $count++;
		return 'Updates <span class="badge">'.($count ?: '').'</span>';
	}
	
	public function update_button_click($sender) {
		$updating = root()->update->update_core($this->state->update_version);
		$this->controls->update_label->val('Done.');
		root()->iface->refresh();
	}
	
	public function admin() {
		$update = root()->update->check_for_update();
		
		if ($update) {
			$this->state->update_version = $update;
			$button = new CP_Button('update_button', 'Update Now', ['class'=>'btn btn-success'], $this);
			$label = new CP_Label('update_label', '', [], $this);
			?>
				<p>There is an update available: <?= $update ?></p>
				<? $button->display() ?>
				<? $label->display() ?>
			<?
		} else {
			?>
				<p>This is the latest version.</p>
			<?
		}
		?>
			<p>You are running version: <?= root()->settings->get('running_sha') ?></p>
		<?
	}
	
}