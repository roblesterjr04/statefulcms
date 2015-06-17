<html>
	<head>
		<?php root()->themes->cp_head() ?>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-2 col-sm-3">
					<ul class="nav">
						<li role="presentation" class=""><a href="<?= root()->settings->get('cp_site_url') ?>/admin">System</a></li>
					</ul>
				</div>
				<div class="col-md-10 col-sm-9">
					<? //if (CP_Object::get_object()) : ?>
						<? //CP_Components::object_content(); ?>
					<? //endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>