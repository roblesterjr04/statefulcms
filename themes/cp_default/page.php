<? root()->themes->get_theme_part('header') ?>
<div class="container">
	<? if (root()->objects->get_object()) : ?>
		<? root()->components->object_content(); ?>
	<? endif; ?>		
</div>
<? root()->themes->get_theme_part('footer') ?>