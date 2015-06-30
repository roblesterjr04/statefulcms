<? root()->themes->get_theme_part('header') ?>
<div class="container">
	<h1>The index theme page.</h1>
	<div class="row">
		<div class="col-md-2 col-sm-3">
			<ul class="nav">
				<li role="presentation" class=""><a href="<?= root()->settings->get('cp_site_url') ?>/admin">System</a></li>
				<li role="presentation" class=""><a href="<?= root()->objects->get_object('CP_Page')->view_link(11) ?>">Page</a></li>
			</ul>
		</div>
		<div class="col-md-10 col-sm-9">
			<? 
				$button = new CP_Button('index_button', 'Don&apos;t click me.', ['class'=>'btn btn-danger'], root()->objects->get_object('default_theme')); 
				if (root()->authentication->required()) $button->display(); 
			?>
		</div>
	</div>
</div>
<? root()->themes->get_theme_part('footer') ?>