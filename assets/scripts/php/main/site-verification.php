<?php

// ----------------------------------- Подтверждение прав на сайт (мета-теги в head)
function moveat_site_verification_meta() {
	// Impact — партнёрская программа iHerb.
	echo "<meta name='impact-site-verification' value='5b709111-5d92-4076-a6ce-30cac9d02e56'>\n";
}
add_action( 'wp_head', 'moveat_site_verification_meta', 1 );
