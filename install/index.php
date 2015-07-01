<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="../admin/bootstrap/css/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="../admin/bootstrap/css/bootstrap-theme.min.css" type="text/css" />
		<!--<link rel="stylesheet" href="themes/holo/css/styles.css" type="text/css" />-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="../admin/bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="//cdn.ckeditor.com/4.4.7/standard/ckeditor.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<? if (empty($_POST)) : ?>
						<h1>Install</h1>
						<div class="panel panel-default">
							<div class="panel-body">
								<form method="POST">
									<div class="form-group">
										<label for="site_url">Site URL</label>
										<input type="text" class="form-control" name="site_url" placeholder="...">
									</div>
									<div class="form-group">
										<label for="admin_email">Admin Email</label>
										<input type="email" class="form-control" name="admin_email" placeholder="admin@site.com">
									</div>
									<div class="form-group">
										<label for="admin_password">Admin Password</label>
										<input type="password" class="form-control" name="admin_password" placeholder="admin">
									</div>
									<hr />
									<div class="form-group">
										<label for="db_host">Database Host</label>
										<input type="text" class="form-control" name="db_host" placeholder="localhost">
									</div>
									<div class="form-group">
										<label for="db_name">Database Name</label>
										<input type="text" class="form-control" name="db_name" placeholder="control_panel">
									</div>
									<div class="form-group">
										<label for="db_user">Database User</label>
										<input type="text" class="form-control" name="db_user" placeholder="root">
									</div>
									<div class="form-group">
										<label for="db_pass">Database Password</label>
										<input type="password" class="form-control" name="db_pass" placeholder="root">
									</div>
									<div class="form-group">
										<label for="db_prefix">Database Prefix</label>
										<input type="text" class="form-control" name="db_prefix" placeholder="cp_">
									</div>
									<div class="form-group">
										<label for="db_port">Database Port</label>
										<input type="text" class="form-control" name="db_port" placeholder="3306">
									</div>
									<button type="submit" class="btn btn-default">Save</button>
								</form>
							</div>
						</div>
					<? else : ?>
						<h1>Done!</h1>
						<?
							$db = file_get_contents(__DIR__ . '/database.sql');
							$db = str_replace('cp_', $_POST['db_prefix'] ?: 'cp_', $db);
							$config = file_get_contents(__DIR__ . '/cp_config.php');
							$config = str_replace('%%db_server%%', $_POST['db_host'] ?: 'localhost', $config);
							$config = str_replace('%%db_name%%', $_POST['db_name'] ?: 'control_panel', $config);
							$config = str_replace('%%db_user%%', $_POST['db_user'] ?: 'root', $config);
							$config = str_replace('%%db_pass%%', $_POST['db_pass'] ?: 'root', $config);
							$config = str_replace('%%db_prefix%%', $_POST['db_prefix'] ?: 'cp_', $config);
							$config = str_replace('%%db_port%%', $_POST['db_port'] ?: '3306', $config);
							
							file_put_contents(__DIR__ . '/../cp-config.php', $config);
																										
							// Execute DB Script
							
							require_once __DIR__ . '/../cp-config.php';
							require_once 'install_init.php';
							
							root()->db->mySql->multi_query($db);
							
							while (root()->db->mySql->next_result()) {
								if (!root()->db->mySql->more_results()) break;
							}
							
							root()->settings->set('cp_site_url', $_POST['site_url'] ?: 'www.site.com');
							root()->settings->set('cp_current_theme', 'cp_default');
							
							$data = [
								'id' => 0,
								'first_name' => 'Admin',
								'last_name' => '',
								'email' => $_POST['admin_email'] ?: 'admin@site.com',
								'user_name' => 'admin',
								'pass_word' => md5($_POST['admin_password'] ?: 'admin'),
								'meta' => [
									'date_modified' => date('n/j/Y'),
								]
							];
							$user = new CP_Users();
							$user->save($data);
						?>
					<? endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>