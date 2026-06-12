<?php
/**
 * Title: Intro hero
 * Slug: wove/intro-hero
 * Categories: wove
 * Description: Home-page introduction — greeting, name, and a short bio, with a portrait beside the text. Content comes from Appearance → Wove.
 * Keywords: hero, intro, about, portrait
 * Inserter: false
 *
 * @package Wove
 *
 * Content is editable from the backend (Appearance → Wove): the greeting, bio
 * and photo come from the `wove_intro` option; the name is the Site Title. This
 * pattern is wired into front-page.html, so the hero always renders even when the
 * Home page itself is empty.
 */

$wove_intro        = function_exists( 'wove_get_intro' ) ? wove_get_intro() : array( 'greeting' => '', 'bio' => '', 'photo' => 0 );
$wove_greeting     = $wove_intro['greeting'];
$wove_bio          = $wove_intro['bio'];
$wove_photo_id     = (int) $wove_intro['photo'];
$wove_portrait_alt = get_bloginfo( 'name' );
if ( '' === trim( (string) $wove_portrait_alt ) ) {
	$wove_portrait_alt = _x( 'Portrait', 'Portrait alt text', 'wove' );
}
?>
<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center">
	<!-- wp:column {"verticalAlignment":"center","width":"64%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:64%">
		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"fontSize":"large","style":{"color":{"text":"var:preset|color|muted"},"typography":{"fontStyle":"italic","fontWeight":"400"}}} -->
			<p class="has-large-font-size" style="color:var(--wp--preset--color--muted);font-style:italic;font-weight:400"><?php echo esc_html( $wove_greeting ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:site-title {"level":1,"isLink":false,"fontSize":"display","className":"wove-hero-name"} /-->

			<!-- wp:paragraph {"fontSize":"medium","style":{"color":{"text":"var:preset|color|muted"}}} -->
			<p class="has-medium-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $wove_bio ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"verticalAlignment":"center","width":"36%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:36%">
		<!-- wp:image {"className":"hero-portrait"} -->
		<figure class="wp-block-image hero-portrait"><?php
		$wove_photo_html = $wove_photo_id
			? wp_get_attachment_image( $wove_photo_id, 'medium', false, array( 'alt' => esc_attr( $wove_portrait_alt ) ) )
			: '';
		if ( $wove_photo_html ) {
			echo $wove_photo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() is safe.
		} else {
			// No photo chosen (or the attachment was removed): show the placeholder silhouette.
			printf(
				'<img src="%s" alt="%s"/>',
				esc_url( get_template_directory_uri() . '/assets/images/portrait.svg' ),
				esc_attr( $wove_portrait_alt )
			);
		}
		?></figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
