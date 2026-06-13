<?php
/**
 * Wove theme functions.
 *
 * A block theme — almost all configuration lives in theme.json. This file
 * only handles theme support flags, the front-end stylesheet, and the pattern
 * category. Fonts are declared in theme.json (fontFace) and need no PHP.
 *
 * @package Wove
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

if ( ! function_exists( 'wove_setup' ) ) {
	/**
	 * Theme setup.
	 */
	function wove_setup() {
		load_theme_textdomain( 'wove', get_template_directory() . '/languages' );

		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

		// Load style.css inside the editor canvas too, so the small extra rules match.
		add_editor_style( 'style.css' );

		/*
		 * Starter content: on a brand-new site, WordPress offers to create these pages,
		 * set the static front page + blog page, and wire the nav. (The "Set up" admin
		 * notice below is the reliable fallback for sites that already have content.)
		 */
		add_theme_support(
			'starter-content',
			array(
				'posts'   => array(
					'home'    => array(
						'post_type'    => 'page',
						'post_title'   => _x( 'Home', 'Starter page title', 'wove' ),
						'post_content' => wove_intro_block_markup(),
					),
					'blog'    => array(
						'post_type'  => 'page',
						'post_title' => _x( 'Blog', 'Starter page title', 'wove' ),
					),
					'about'   => array(
						'post_type'    => 'page',
						'post_title'   => _x( 'About', 'Starter page title', 'wove' ),
						'post_content' => '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you — who you are and what you write about. Edit this on the About page.', 'wove' ) . '</p><!-- /wp:paragraph -->',
					),
					'contact' => array(
						'post_type'    => 'page',
						'post_title'   => _x( 'Contact', 'Starter page title', 'wove' ),
						'post_content' => '<!-- wp:wove/social-links /-->',
					),
				),
				'options' => array(
					'show_on_front'  => 'page',
					'page_on_front'  => '{{home}}',
					'page_for_posts' => '{{blog}}',
				),
			)
		);
	}
	add_action( 'after_setup_theme', 'wove_setup' );
}

if ( ! function_exists( 'wove_enqueue_styles' ) ) {
	/**
	 * Enqueue the front-end stylesheet (style.css holds the few rules theme.json can't express).
	 */
	function wove_enqueue_styles() {
		// Version by file modification time so any CSS edit busts the browser cache.
		$style_path = get_stylesheet_directory() . '/style.css';
		$version    = file_exists( $style_path ) ? (string) filemtime( $style_path ) : wp_get_theme()->get( 'Version' );

		wp_enqueue_style(
			'wove-style',
			get_stylesheet_uri(),
			array(),
			$version
		);
	}
	add_action( 'wp_enqueue_scripts', 'wove_enqueue_styles' );
}

if ( ! function_exists( 'wove_pattern_category' ) ) {
	/**
	 * Register the theme's block pattern category.
	 */
	function wove_pattern_category() {
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				'wove',
				array( 'label' => __( 'Wove', 'wove' ) )
			);
		}
	}
	add_action( 'init', 'wove_pattern_category' );
}

if ( ! function_exists( 'wove_register_reading_time' ) ) {
	/**
	 * Block-bindings source that outputs an estimated reading time ("· N min read")
	 * from the current post's word count. Bound to a paragraph in single.html's meta row.
	 */
	function wove_register_reading_time() {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}
		register_block_bindings_source(
			'wove/reading-time',
			array(
				'label'              => __( 'Reading time', 'wove' ),
				'get_value_callback' => function () {
					$post = get_post();
					if ( ! $post ) {
						return '';
					}
					$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
					$minutes = max( 1, (int) round( $words / 200 ) );
					/* translators: %d: estimated reading time in minutes. */
					return sprintf( __( '· %d min read', 'wove' ), $minutes );
				},
			)
		);
	}
	add_action( 'init', 'wove_register_reading_time' );
}

if ( ! function_exists( 'wove_preload_primary_font' ) ) {
	/**
	 * Preload the primary (upright) Newsreader woff2 for a faster first paint.
	 * The italic face stays on-demand.
	 */
	function wove_preload_primary_font() {
		$href = get_stylesheet_directory_uri() . '/assets/fonts/newsreader-latin-standard-normal.woff2';
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $href )
		);
	}
	add_action( 'wp_head', 'wove_preload_primary_font', 1 );
}

if ( ! function_exists( 'wove_mark_current_nav_link' ) ) {
	/**
	 * The navigation uses portable custom links, which WordPress does not auto-mark
	 * as the current page. Add aria-current="page" to the link whose path matches the
	 * current request (treating the blog index, single posts and post archives as the
	 * "Writing" section) so it can be styled active. No install-specific IDs.
	 */
	function wove_mark_current_nav_link( $block_content, $block ) {
		if ( is_admin() || empty( $block['attrs']['url'] ) ) {
			return $block_content;
		}

		$link_path = wp_parse_url( $block['attrs']['url'], PHP_URL_PATH );
		$link_path = trailingslashit( $link_path ? $link_path : '/' );

		$curr_path = '/';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$path      = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
			$curr_path = trailingslashit( $path ? $path : '/' );
		}

		$is_writing = ( is_home() || is_singular( 'post' ) || is_post_type_archive( 'post' ) || is_category() || is_tag() || is_date() );
		$is_current = ( $link_path === $curr_path ) || ( '/blog/' === $link_path && $is_writing );

		if ( $is_current && false === strpos( $block_content, 'aria-current' ) ) {
			$block_content = preg_replace( '/<a\b/', '<a aria-current="page"', $block_content, 1 );
		}

		return $block_content;
	}
	add_filter( 'render_block_core/navigation-link', 'wove_mark_current_nav_link', 10, 2 );
}

if ( ! function_exists( 'wove_seo_meta' ) ) {
	/**
	 * Lightweight SEO / social meta: meta description, Open Graph, Twitter cards, and
	 * JSON-LD (Person on the front page, BlogPosting on single posts). Self-contained —
	 * no plugin. If you later install Yoast/Rank Math, remove this to avoid duplicate tags.
	 */
	function wove_seo_meta() {
		$site_name   = get_bloginfo( 'name' );
		// Default share image: the site icon if one is set (no personal image is shipped).
		$default_img = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( 512 ) : '';

		if ( is_front_page() ) {
			// The static front page is also is_singular(), so handle it first.
			$title = $site_name;
			$desc  = get_bloginfo( 'description' );
			$url   = home_url( '/' );
			$type  = 'website';
			$img   = $default_img;
		} elseif ( is_singular() ) {
			$post  = get_queried_object();
			$title = get_the_title( $post );
			$desc  = has_excerpt( $post )
				? get_the_excerpt( $post )
				: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 40, '…' );
			$url   = get_permalink( $post );
			$type  = is_singular( 'post' ) ? 'article' : 'website';
			$img   = has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'large' ) : $default_img;
		} else {
			$title = wp_get_document_title();
			$desc  = get_bloginfo( 'description' );
			$url   = home_url( add_query_arg( array() ) );
			$type  = 'website';
			$img   = $default_img;
		}

		$desc = trim( wp_strip_all_tags( (string) $desc ) );

		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		echo '<meta name="twitter:card" content="' . ( $img ? 'summary_large_image' : 'summary' ) . '">' . "\n";
		printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
		if ( $img ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $img ) );
			printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $img ) );
		}

		$schema = null;
		if ( is_front_page() ) {
			$schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Person',
				'name'     => $site_name,
				'url'      => home_url( '/' ),
			);
			if ( $default_img ) {
				$schema['image'] = $default_img;
			}
			$same_as = wove_get_social_urls();
			if ( $same_as ) {
				$schema['sameAs'] = array_values( $same_as );
			}
		} elseif ( is_singular( 'post' ) ) {
			$schema = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'BlogPosting',
				'headline'         => $title,
				'datePublished'    => get_the_date( 'c' ),
				'dateModified'     => get_the_modified_date( 'c' ),
				'author'           => array( '@type' => 'Person', 'name' => $site_name ),
				'mainEntityOfPage' => $url,
			);
			if ( $img ) {
				$schema['image'] = $img;
			}
		}
		if ( $schema ) {
			echo '<script type="application/ld+json">'
				. wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
				. '</script>' . "\n";
		}

		// Favicon fallback when no Site Icon is set in the editor.
		if ( ! has_site_icon() ) {
			printf(
				'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
				esc_url( get_stylesheet_directory_uri() . '/assets/icon.svg' )
			);
		}
	}
	add_action( 'wp_head', 'wove_seo_meta', 5 );
}

if ( ! function_exists( 'wove_color_scheme_boot' ) ) {
	/**
	 * Dark mode: set documentElement[data-theme] from a saved choice before paint
	 * (no flash of the wrong theme). With no saved choice, CSS follows the OS via
	 * prefers-color-scheme.
	 */
	function wove_color_scheme_boot() {
		echo "<script>(function(){try{var t=localStorage.getItem('wove-theme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();</script>\n";
	}
	add_action( 'wp_head', 'wove_color_scheme_boot', 0 );
}

if ( ! function_exists( 'wove_enqueue_theme_toggle' ) ) {
	/**
	 * Enqueue the dark-mode toggle script (mtime-versioned, deferred, in footer).
	 */
	function wove_enqueue_theme_toggle() {
		$path = get_stylesheet_directory() . '/assets/js/theme-toggle.js';
		$ver  = file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0';
		wp_enqueue_script(
			'wove-theme-toggle',
			get_stylesheet_directory_uri() . '/assets/js/theme-toggle.js',
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
	add_action( 'wp_enqueue_scripts', 'wove_enqueue_theme_toggle' );
}

/* -------------------------------------------------------------------------
 * Identity & social links — editable from the backend (Appearance → Wove).
 * A single option (`wove_social`) is the source of truth for the footer, the
 * Contact block, and the SEO JSON-LD. No links live in template files.
 * ---------------------------------------------------------------------- */

if ( ! function_exists( 'wove_social_fields' ) ) {
	/**
	 * The editable identity/social fields, in display order.
	 *
	 * @return array<string,array{label:string,type:string}>
	 */
	function wove_social_fields() {
		return array(
			'email'     => array( 'label' => __( 'Email', 'wove' ), 'type' => 'email' ),
			'github'    => array( 'label' => __( 'GitHub', 'wove' ), 'type' => 'url' ),
			'linkedin'  => array( 'label' => __( 'LinkedIn', 'wove' ), 'type' => 'url' ),
			'x'         => array( 'label' => __( 'X', 'wove' ), 'type' => 'url' ),
			'mastodon'  => array( 'label' => __( 'Mastodon', 'wove' ), 'type' => 'url' ),
			'instagram' => array( 'label' => __( 'Instagram', 'wove' ), 'type' => 'url' ),
		);
	}
}

if ( ! function_exists( 'wove_get_social_links' ) ) {
	/**
	 * Display list of filled-in links (label + href), with the site feed appended.
	 *
	 * @return array<int,array{label:string,url:string,external:bool}>
	 */
	function wove_get_social_links() {
		$values = (array) get_option( 'wove_social', array() );
		$links  = array();

		foreach ( wove_social_fields() as $key => $field ) {
			$value = isset( $values[ $key ] ) ? trim( (string) $values[ $key ] ) : '';
			if ( '' === $value ) {
				continue;
			}
			if ( 'email' === $field['type'] ) {
				$links[] = array(
					'label'    => $field['label'],
					'url'      => 'mailto:' . $value,
					'external' => false,
				);
			} else {
				$links[] = array(
					'label'    => $field['label'],
					'url'      => $value,
					'external' => true,
				);
			}
		}

		$links[] = array(
			'label'    => __( 'RSS', 'wove' ),
			'url'      => get_feed_link(),
			'external' => false,
		);

		return $links;
	}
}

if ( ! function_exists( 'wove_get_social_urls' ) ) {
	/**
	 * Just the profile URLs (no email/feed) — used for JSON-LD `sameAs`.
	 *
	 * @return array<int,string>
	 */
	function wove_get_social_urls() {
		$values = (array) get_option( 'wove_social', array() );
		$urls   = array();
		foreach ( wove_social_fields() as $key => $field ) {
			if ( 'url' === $field['type'] && ! empty( $values[ $key ] ) ) {
				$urls[] = $values[ $key ];
			}
		}
		return $urls;
	}
}

if ( ! function_exists( 'wove_register_settings' ) ) {
	/**
	 * Register the `wove_social` option with sanitisation.
	 */
	function wove_register_settings() {
		register_setting(
			'wove_options',
			'wove_social',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'wove_sanitize_social',
				'default'           => array(),
			)
		);
	}
	add_action( 'admin_init', 'wove_register_settings' );
}

if ( ! function_exists( 'wove_sanitize_social' ) ) {
	/**
	 * Sanitise the social option (emails + URLs).
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	function wove_sanitize_social( $input ) {
		$clean = array();
		$input = is_array( $input ) ? $input : array();
		foreach ( wove_social_fields() as $key => $field ) {
			$raw = isset( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';
			if ( '' === $raw ) {
				continue;
			}
			$clean[ $key ] = ( 'email' === $field['type'] ) ? sanitize_email( $raw ) : esc_url_raw( $raw );
		}
		return $clean;
	}
}

if ( ! function_exists( 'wove_intro_block_markup' ) ) {
	/**
	 * The home-page intro as portable block markup — greeting, name, bio, and a
	 * portrait. Seeded into the Home page so it lives in post content: editable in
	 * the block editor, and carried along on export or a theme switch (rather than
	 * stored in a theme option). The name is the Site Title block (Settings →
	 * General), which also feeds the SEO data.
	 *
	 * @return string
	 */
	function wove_intro_block_markup() {
		$greeting = esc_html_x( 'Hi, I’m', 'Greeting above the name', 'wove' );
		$bio      = esc_html__( 'A short introduction goes here — a sentence or two about who you are and what you write about. Edit it right here on the Home page.', 'wove' );
		$portrait = esc_url( get_template_directory_uri() . '/assets/images/portrait.svg' );
		$alt      = esc_attr_x( 'Portrait', 'Portrait alt text', 'wove' );

		return '<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"64%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:64%"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"textColor":"muted","fontSize":"large","style":{"typography":{"fontStyle":"italic","fontWeight":"400"}}} -->
<p class="has-muted-color has-text-color has-large-font-size" style="font-style:italic;font-weight:400">' . $greeting . '</p>
<!-- /wp:paragraph -->

<!-- wp:site-title {"level":1,"isLink":false,"fontSize":"display","className":"wove-hero-name"} /-->

<!-- wp:paragraph {"textColor":"muted","fontSize":"medium"} -->
<p class="has-muted-color has-text-color has-medium-font-size">' . $bio . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"36%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:36%"><!-- wp:image {"className":"hero-portrait"} -->
<figure class="wp-block-image hero-portrait"><img src="' . $portrait . '" alt="' . $alt . '"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->';
	}
}

if ( ! function_exists( 'wove_settings_page' ) ) {
	/**
	 * Add the Appearance → Wove settings page.
	 */
	function wove_settings_page() {
		add_theme_page(
			__( 'Wove Settings', 'wove' ),
			__( 'Wove', 'wove' ),
			'edit_theme_options',
			'wove-settings',
			'wove_render_settings_page'
		);
	}
	add_action( 'admin_menu', 'wove_settings_page' );
}

if ( ! function_exists( 'wove_render_settings_page' ) ) {
	/**
	 * Render the settings form.
	 */
	function wove_render_settings_page() {
		$values = (array) get_option( 'wove_social', array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Wove', 'wove' ); ?></h1>
			<?php
			if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_settings_error( 'wove_messages', 'wove_saved', __( 'Settings saved.', 'wove' ), 'updated' );
			}
			settings_errors( 'wove_messages' );
			?>
			<form method="post" action="options.php">
				<?php settings_fields( 'wove_options' ); ?>

				<h2><?php esc_html_e( 'Email & social links', 'wove' ); ?></h2>
				<p><?php esc_html_e( 'These appear in the footer and on the Contact page, and feed the site’s structured data. Leave a field blank to hide it.', 'wove' ); ?></p>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( wove_social_fields() as $key => $field ) : ?>
						<tr>
							<th scope="row">
								<label for="wove_social_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							</th>
							<td>
								<input
									type="<?php echo esc_attr( 'email' === $field['type'] ? 'email' : 'url' ); ?>"
									id="wove_social_<?php echo esc_attr( $key ); ?>"
									name="wove_social[<?php echo esc_attr( $key ); ?>]"
									value="<?php echo esc_attr( isset( $values[ $key ] ) ? $values[ $key ] : '' ); ?>"
									class="regular-text"
									placeholder="<?php echo esc_attr( 'email' === $field['type'] ? 'you@example.com' : 'https://…' ); ?>"
								/>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wove_render_social_links' ) ) {
	/**
	 * Server-rendered `wove/social-links` block: a single line of links built
	 * live from the settings option (so the footer, Contact page and SEO all stay
	 * in sync). Outputs nothing if no links are set.
	 *
	 * @return string
	 */
	function wove_render_social_links() {
		$links = wove_get_social_links();
		// Only the auto feed link present and nothing configured? Still show the feed.
		$parts = array();
		foreach ( $links as $item ) {
			$parts[] = sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( $item['url'] ),
				$item['external'] ? ' rel="me noopener" target="_blank"' : '',
				esc_html( $item['label'] )
			);
		}
		if ( empty( $parts ) ) {
			return '';
		}
		return '<p class="wove-social has-small-font-size">' . implode( ' · ', $parts ) . '</p>';
	}
}

if ( ! function_exists( 'wove_register_blocks_and_bindings' ) ) {
	/**
	 * Register the dynamic social-links block and the copyright binding.
	 */
	function wove_register_blocks_and_bindings() {
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'wove/social-links',
				array(
					'api_version'     => 3,
					'render_callback' => 'wove_render_social_links',
				)
			);
		}

		if ( function_exists( 'register_block_bindings_source' ) ) {
			register_block_bindings_source(
				'wove/copyright',
				array(
					'label'              => __( 'Copyright', 'wove' ),
					'get_value_callback' => function () {
						return sprintf(
							/* translators: 1: current year, 2: site name. */
							__( '© %1$s %2$s', 'wove' ),
							gmdate( 'Y' ),
							get_bloginfo( 'name' )
						);
					},
				)
			);
		}
	}
	add_action( 'init', 'wove_register_blocks_and_bindings' );
}

/* -------------------------------------------------------------------------
 * First-run setup: a one-click button that creates the pages, sets the static
 * front page + blog page, and (if needed) enables pretty permalinks. Reliable
 * on any install — complements WordPress's fresh-install starter content.
 * ---------------------------------------------------------------------- */

if ( ! function_exists( 'wove_needs_setup' ) ) {
	/**
	 * True when the site isn't yet using a static front page (so nav/blog routing
	 * won't work as the theme intends).
	 *
	 * @return bool
	 */
	function wove_needs_setup() {
		return ! ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) );
	}
}

if ( ! function_exists( 'wove_setup_notice' ) ) {
	/**
	 * Offer a one-click setup after activation.
	 */
	function wove_setup_notice() {
		if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'wove_setup_dismissed' ) || ! wove_needs_setup() ) {
			return;
		}
		$run     = wp_nonce_url( admin_url( 'admin-post.php?action=wove_setup' ), 'wove_setup' );
		$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=wove_setup_dismiss' ), 'wove_setup_dismiss' );
		?>
		<div class="notice notice-info">
			<p><strong><?php esc_html_e( 'Welcome to Wove!', 'wove' ); ?></strong>
			<?php esc_html_e( 'Finish setup in one click — it creates your Home, Blog, About and Contact pages and sets the front page.', 'wove' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $run ); ?>" class="button button-primary"><?php esc_html_e( 'Set up Wove', 'wove' ); ?></a>
				<a href="<?php echo esc_url( $dismiss ); ?>" class="button-link"><?php esc_html_e( 'Dismiss', 'wove' ); ?></a>
			</p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'wove_setup_notice' );
}

if ( ! function_exists( 'wove_run_setup' ) ) {
	/**
	 * Idempotently create the pages, assign front/blog pages, ensure pretty
	 * permalinks, then return to the dashboard.
	 */
	function wove_run_setup() {
		if ( ! current_user_can( 'edit_theme_options' ) || ! check_admin_referer( 'wove_setup' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wove' ) );
		}

		$pages = array(
			'home'    => _x( 'Home', 'Starter page title', 'wove' ),
			'blog'    => _x( 'Blog', 'Starter page title', 'wove' ),
			'about'   => _x( 'About', 'Starter page title', 'wove' ),
			'contact' => _x( 'Contact', 'Starter page title', 'wove' ),
		);
		$content = array(
			'home'    => wove_intro_block_markup(),
			'about'   => '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you — who you are and what you write about.', 'wove' ) . '</p><!-- /wp:paragraph -->',
			'contact' => '<!-- wp:wove/social-links /-->',
		);
		$ids = array();
		foreach ( $pages as $slug => $title ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$ids[ $slug ] = (int) $existing->ID;
				continue;
			}
			$ids[ $slug ] = (int) wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => isset( $content[ $slug ] ) ? $content[ $slug ] : '',
				)
			);
		}

		if ( ! empty( $ids['home'] ) && ! empty( $ids['blog'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $ids['home'] );
			update_option( 'page_for_posts', $ids['blog'] );
		}

		// Pretty permalinks so /blog/, /about/ resolve (only if still on the plain default).
		if ( '' === get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}
		flush_rewrite_rules();

		update_option( 'wove_setup_dismissed', 1 );
		wp_safe_redirect( admin_url( 'index.php?wove_setup=done' ) );
		exit;
	}
	add_action( 'admin_post_wove_setup', 'wove_run_setup' );
}

if ( ! function_exists( 'wove_setup_done_notice' ) ) {
	/**
	 * One-time success confirmation after the setup runs.
	 */
	function wove_setup_done_notice() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		// Display-only flag (no state change), so no nonce is required.
		if ( ! isset( $_GET['wove_setup'] ) || 'done' !== $_GET['wove_setup'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		printf(
			'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
			esc_html__( 'Wove is set up.', 'wove' ),
			esc_html__( 'Your Home, Blog, About and Contact pages are ready.', 'wove' ),
			esc_url( home_url( '/' ) ),
			esc_html__( 'View your site →', 'wove' )
		);
	}
	add_action( 'admin_notices', 'wove_setup_done_notice' );
}

if ( ! function_exists( 'wove_dismiss_setup' ) ) {
	/**
	 * Dismiss the setup notice without running it.
	 */
	function wove_dismiss_setup() {
		if ( ! current_user_can( 'edit_theme_options' ) || ! check_admin_referer( 'wove_setup_dismiss' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wove' ) );
		}
		update_option( 'wove_setup_dismissed', 1 );
		wp_safe_redirect( admin_url() );
		exit;
	}
	add_action( 'admin_post_wove_setup_dismiss', 'wove_dismiss_setup' );
}
