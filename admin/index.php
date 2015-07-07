<?php
	
require_once '../cp-config.php';
require_once '../core/init.php';
	
root()->authentication->secure(true);

?>
<html>
	<head>
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css" />
		<!--<link rel="stylesheet" href="themes/holo/css/styles.css" type="text/css" />-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="//cdn.ckeditor.com/4.4.7/standard/ckeditor.js"></script>
		<script type="text/javascript" src="<?= root()->settings->get('cp_site_url') ?>/js/state.js"></script>
		<script type="text/javascript">var state_host = "<?= root()->settings->get('cp_site_url') ?>/admin/state.php";</script>
		<script type="text/javascript">var ajax_host = "<?= root()->settings->get('cp_site_url') ?>/admin/ajax.php";</script>
	</head>
	<body style="padding-top: 70px;">
		<!-- Top Nav Toolbar -->
		<div class="container-fluid">
			<nav class="navbar navbar-inverse navbar-fixed-top">
				<div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				  <span class="sr-only">Toggle navigation</span>
				  <span class="icon-bar"></span>
				  <span class="icon-bar"></span>
				  <span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?= root()->settings->get('cp_site_url') ?>/admin/">StatefulCMS</a>
				</div>
				
				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
				  <li class="dropdown">
				    <a href="<?= root()->settings->get('cp_site_url') ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?= root()->settings->get('cp_site_name') ?: 'Menu' ?> <span class="caret"></span></a>
				    <? root()->components->admin_menu('site', 'dropdown-menu'); ?>
				  </li>
				  <? root()->hooks->action->perform('toolbar_menu_item') ?>
				</ul>
				<? $right_menu = new CP_Menu('navbar_right', ['class'=>'nav navbar-nav navbar-right'], root()->objects->get_object()); $right_menu->display(); ?>
				</div><!-- /.navbar-collapse -->
				</div><!-- /.container-fluid -->
			</nav>
			<div class="row">
				<div class="col-md-2 col-sm-3">
					<? root()->components->admin_menu() ?>
				</div>
				<div class="col-md-10 col-sm-9">
					<? if (root()->objects->get_object()) : ?>
						<? root()->components->admin_content() ?>
					<? else : ?>
						<p>nothing here yet.</p>
					<? endif; ?>
				</div>
			</div>
		</div>
		<form id="index_form">
			<? unset($_GET['sort']); foreach($_GET as $k=>$g) : ?>
			<input type="hidden" name="<?= $k ?>" value="<?= $g ?>" />
			<? endforeach; ?>
		</form>
	</body>
</html>