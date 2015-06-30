<?
	
require_once '../cp-config.php';
require_once '../core/init.php';
	
require_once(__DIR__ . '/client/GitHubClient.php');

$owner = 'roblesterjr04';
$repo = 'statefulcms';

$client = new GitHubClient();
$client->setPage();
$client->setPageSize(1);
$commits = $client->repos->commits->listCommitsOnRepository($owner, $repo);

//echo "Count: " . count($commits) . "\n";
foreach($commits as $commit)
{
	/* @var $commit GitHubCommit */
	//echo get_class($commit) . " - Sha: " . $commit->getSha() . "\n";
	
	$running_sha = root()->settings->get('running_sha');
	$current_sha = $commit->getSha();
	
	echo $running_sha == $current_sha ? 'Up to date.' : 'Needs update';
    
}

/*$commits = $client->getNextPage();

echo "Count: " . count($commits) . "\n";
foreach($commits as $commit)
{
    echo get_class($commit) . " - Sha: " . $commit->getSha() . "\n";
}*/