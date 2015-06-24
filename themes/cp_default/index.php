<? root()->themes->get_theme_part('header') ?>
<div class="container">
	<h1>This is a cool bootstrap site.</h1>
	<div class="row">
		<div class="col-md-2 col-sm-3">
			<ul class="nav">
				<li role="presentation" class=""><a href="<?= root()->settings->get('cp_site_url') ?>/admin">System</a></li>
				<li role="presentation" class=""><a href="<?= root()->objects->get_object('CP_Page')->view_link(3) ?>">Page</a></li>
			</ul>
		</div>
		<div class="col-md-10 col-sm-9">
			<? if (root()->objects->get_object()) : ?>
				<? root()->components->object_content(); ?>
			<? endif; ?>
		</div>
	</div>
</div>
<? root()->themes->get_theme_part('footer') ?>