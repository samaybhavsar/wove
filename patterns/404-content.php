<?php
/**
 * Title: 404 content
 * Slug: wove/404-content
 * Inserter: no
 *
 * @package Wove
 */
?>
<!-- wp:heading {"level":1,"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-xx-large-font-size"><?php esc_html_e( 'Not found', 'wove' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}}} -->
<p style="color:var(--wp--preset--color--muted)"><?php esc_html_e( 'The page you’re looking for doesn’t exist. Try a search, or head back home.', 'wove' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:pattern {"slug":"wove/search-form"} /-->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--30)"><a href="/"><?php esc_html_e( '← Back home', 'wove' ); ?></a></p>
<!-- /wp:paragraph -->
