<?php
/**
 * Title: Intro hero
 * Slug: wove/intro-hero
 * Categories: wove
 * Description: Home-page introduction — greeting, name, and a short bio, with a portrait beside the text.
 * Keywords: hero, intro, about, portrait
 * Block Types: core/columns
 * Inserter: true
 *
 * @package Wove
 *
 * The home page is seeded with this same markup as editable block content (see
 * wove_intro_block_markup() in functions.php), so the intro lives in the page and
 * is portable. This pattern is just a convenient way to re-insert the hero.
 */

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; dynamic values escaped in the helper.
echo function_exists( 'wove_intro_block_markup' ) ? wove_intro_block_markup() : '';
