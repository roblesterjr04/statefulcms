<?
	
//if (!defined(CP_WORKING_DIR)) exit('Direct access not allowed');

class CP_Update {
	
	public $version;
	
	public function __construct() {
		$this->version = root()->settings->get('running_sha');
	}
	
	public function check_for_update() {
		
		$git = GIT_BRANCH ?: 'master';
		$v = time();
		
		$data = file_get_contents("https://raw.githubusercontent.com/roblesterjr04/statefulcms/$git/update/version.txt?v=$v");
		
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
	
	private function unzip_package($package) {
		$zip = zip_open($package);
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				$entry_name = zip_entry_name($zip_entry);
				$directory = substr($entry_name, strlen($entry_name) - 1, 1) == '/';
				if (zip_entry_open($zip, $zip_entry, "r")) {
					if ($directory) {
						mkdir(__DIR__ . '/'.$entry_name);
					} else {
						$fp = fopen(__DIR__ . '/'.$entry_name, "w");
						$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
						fwrite($fp,"$buf");
						zip_entry_close($zip_entry);
						fclose($fp);
					}
				}
			}
			zip_close($zip);
		}
	}
	
	private function parse_version_file($file) {
		$git = GIT_BRANCH ?: 'master';
		
		$data = file_get_contents($file);
		
		$data_lines = explode("\n", $data);
		
		foreach ($data_lines as $line) {
			$line_parts = explode(':', $line);
			if (isset($line_parts[0]) && isset($line_parts[1])) {
				if ($line_parts[0] == 'Replace') {
					$value = trim($line_parts[1]);
					if (is_dir(__DIR__ . "/statefulcms-$git/" . $value)) {
						$this->replaceTree(__DIR__ . "/statefulcms-$git/" . $value, __DIR__ . '/../' . $value);
					} else {
						rename(__DIR__ . "/statefulcms-$git/" . $value, __DIR__ . '/../' . $value);
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
				if ($line_parts[0] == 'Config') {
					$value = trim($line_parts[1]);
					$config = file(__DIR__ . '/../cp-config.php');
					if (!in_array($value, $config)) $config[] = $value;
					file_put_contents(__DIR__ . '/../cp-config.php', $config);
				}
			}
		}
	}
	
	public function update_core($update) {
		$git = GIT_BRANCH ?: 'master';
		$package = file_get_contents("https://github.com/roblesterjr04/statefulcms/archive/$git.zip");
		file_put_contents(__DIR__ . '/update_package.zip', $package);
		
		mkdir(__DIR__ . '/statefulcms-'.$git);
		
		$this->unzip_package(__DIR__ . '/update_package.zip');
		
		$this->parse_version_file(__DIR__ . "/statefulcms-$git/update/version.txt");
		
		$this->delTree(__DIR__ . '/statefulcms-'.$git);
		unlink(__DIR__ . '/update_package.zip');
		
		root()->settings->set('running_sha', $update);
		$this->version = $update;
		return true;
	}

}

class Update_Control extends CP_Object {
	
	public $menus = ['top','side'];
	
	public function __construct() {
		parent::__construct('Update_Control');
	}
	
	public function title() {
		$count = 0;
		if (root()->update->check_for_update()) $count++;
		return 'Updates <span class="badge">'.($count ?: '').'</span>';
	}
	
	public function update_button_click($sender) {
		$this->controls->ajax_update->update('ajax_update_core');
	}
	
	public function ajax_update_core() {
		root()->update->update_core($this->state->update_version);
		echo "<p>Update Complete.</p>";
		$this->ajax_update_check();
	}
	
	public function ajax_update_check() {
		$update = root()->update->check_for_update();
		
		if ($update) {
			$this->state->update_version = $update;
			$button = new CP_Button('update_button', 'Update Now', ['class'=>'btn btn-success'], $this);
			?>
				<p>There is an update available: <?= $update ?></p>
				<? $button->display() ?>
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
	
	public function check_again_click($sender) {
		$this->controls->ajax_update->update();
	}
	
	public function admin() {
		$button = new CP_Button('check_again', 'Check Again...', ['class'=>'btn btn-default'], $this);
		$button->display();
		echo "<br/><br/>";
		$display = new CP_Ajax('ajax_update', 'ajax_update_check', [], $this);
		$display->display()->update();
	}
	
}