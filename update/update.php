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
	
	public function update_core() {
		$package = file_get_contents('https://github.com/roblesterjr04/statefulcms/archive/master.zip');
		
	}

}

class Update_Control extends CP_Object {
	
	public function __construct() {
		parent::__construct('Update_Control');
	}
	
	public function title() {
		return 'Updates';
	}
	
	public function admin() {
		$update = root()->update->has_update();
		if ($update) {
			?>
				<p>There is an update available</p>
			<?
		} else {
			?>
				<p>You are using the latest version.</p>
			<?
		}
	}
	
}