<?php
/**
 * Plugin Name: Block reference
 * Description: Presents a list of blocks and their class names, attributes etc.
 * Author: Carolina Nymark
 * Version: 1.0.0
 * Text Domain: block-reference
 * Requires at least: 5.4
 * Tested up to: 5.4
 * Requires PHP: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Get an array of block.
 *
 * @since 1.0.0
 * @return array
 */
function block_reference_block_list() {
	return [ 'archives', 'audio', 'block', 'button', 'buttons', 'calendar', 'categories', 'freeform', 'code', 'column',
	'columns', 'cover', 'file', 'gallery', 'group', 'heading', 'html', 'image', 'latest-comments', 'latest-posts', 'legacy-widget',
	'list', 'media-text', 'more', 'navigation-link', 'navigation', 'nextpage', 'paragraph', 'post-author', 'post-comments-count',
	'post-comments-form', 'post-comments', 'post-content', 'post-date', 'post-excerpt', 'post-featured-image', 'post-tags',
	'post-title', 'preformatted', 'pullquote', 'query-loop', 'query-pagination', 'query', 'quote', 'rss', 'search', 'separator',
	'shortcode', 'site-title', 'social-link', 'social-links', 'spacer', 'table', 'tag-cloud', 'verse', 'video', 'widget-area' ];
}

/**
 * Print the form for sorting the blocks.
 *
 * @since 1.0.0
 */
function block_reference_block_form() {
	echo '<form action="' , esc_url( get_page_link() ) , '" method="post">';
	echo '<label>' , esc_html__( 'Select a block to view:', 'block-reference' );
	echo '<br><select name="block">';
	echo '<option value="*" selected>' , esc_html__( 'All', 'block-reference' ) , '</option>';

	// Get the array of blocks.
	$blocklist = block_reference_block_list();
	foreach ( $blocklist as $block_name ) {
		if ( 'freeform' === $block_name ) {
			$block_name = 'classic';
		}
		echo '<option value="' , esc_attr( $block_name ) , '">' , esc_html( ucfirst( $block_name ) ) , '</option>';
	}
	echo '</select></label><br>';
	echo '<input class="button" type="submit" value="' , esc_attr__( 'Continue', 'block-reference' ) , '" />';
	echo '</form>';
}

/**
 * Get the description from index.js.
 *
 * @param var $block block name.
 * @since 1.0.0
 */
function block_reference_block_description( $block ) {
	$block_source = plugin_dir_path( __FILE__ ) . 'src/' . $block . '/index.js';

	if ( file_exists( $block_source ) ) {
		$block_description = file_get_contents( $block_source );

		if ( strpos( $block_description, 'description:' ) ) {
			$block_description    = explode( 'description:', $block_description );
			$block_description    = explode( '),', $block_description[1] );
			$block_description[0] = str_replace( '__(', '', $block_description[0] );
			$block_description[0] = str_replace( "'", '', $block_description[0] );
			return '<p>' . $block_description[0] . '</p>';
		}
	}
}


/**
 * Get the transform from transform.js.
 *
 * @param var $block block name.
 * @since 1.0.0
 */
function block_reference_block_transforms( $block ) {
	$block_source = plugin_dir_path( __FILE__ ) . 'src/' . $block . '/transforms.js';

	$from = array();
	$to   = array();

	if ( 'legacy-widget' !== $block && file_exists( $block_source ) ) {
		$block_transforms = file( $block_source );

		foreach ( $block_transforms as $line_num => $line ) {
			/* Locate blocks that the block can transform to */
			if ( strpos( $line, 'to:' ) ) {
				$transform_to_line = $line_num;
			}

			if ( strpos( $line, "createBlock( '" ) && strpos( $line, 'return createBlock' ) == false ) {
				if ( isset( $transform_to_line ) && $transform_to_line < $line_num ) {
					$block_line = $line_num;
					$to_block   = $line;

					$to_block = explode( ',', $to_block );

					$to_block = str_replace( "createBlock( 'core/", '', $to_block[0] );
					$to_block = str_replace( "'", '', $to_block );
				}
			}

			/* Locate blocks that the block can transform from */
			if ( strpos( $line, 'blocks: [' ) ) {
				$from_block = $line;
				$from_block = explode( "[ 'core/", $from_block );
				$from_block = explode( "' ],", $from_block[1] );
				$from_block = $from_block[0];
			}

			if ( isset( $transform_to_line ) && isset( $block_line ) ) {
				$to[] = $to_block;
			} elseif ( isset( $from_block ) ) {
				$from[] = $from_block;
			}
		}

		if ( ! empty( $to ) || ! empty( $from ) ) {
			echo '<h3>' , esc_html__( 'Transforms', 'block-reference' ) , '</h3>';
			echo '<p><i>' , esc_html__( 'Optional.', 'block-reference' ) , '</i> ' ,
			esc_html__( 'Type: Array.', 'block-reference' ) , '</p>';
			echo '<p>' . esc_html__( 'Transforms provide rules for what a block can be transformed from and what it can be transformed to.', 'block-reference' ) , '</p>';
			echo '<p> <a href="https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#transforms-optional">' ,
				esc_html__( 'Documentation', 'block-reference' ) , '</a></p><p>';
		}

		if ( ! empty( $to ) ) {
			$to = array_unique( $to );
			echo '<p>' , esc_html__( 'Transforms from: ', 'block-reference' );
			foreach ( $to as $to ) {
				echo $to , ', ';
			}
			echo '</p>';
		}

		if ( ! empty( $from ) ) {
			$from = array_unique( $from );
			echo '<p>' , esc_html__( 'Transforms to: ', 'block-reference' );
			foreach ( $from as $from ) {
				echo $from , ', ';
			}
			echo '</p>';
		}
	}
}

/**
 * Get block grammar / markup.
 *
 * @param var $block block name.
 * @since 1.0.0
 */
function block_reference_block_grammar( $block ) {
	$block_source = plugin_dir_path( __FILE__ ) . 'src/core__' . $block . '.html';
	if ( 'legacy-widget' !== $block && file_exists( $block_source ) ) {
		$block_grammar = file_get_contents( $block_source );

		if ( strpos( $block_grammar, 'class="' ) ) {
			$block_class = explode( 'class="', $block_grammar );
			$block_class = explode( '"', $block_class[1] );
			// if there is a space, we have two classes.
			if ( strpos( $block_class[0], ' ' ) ) {
				$block_class = explode( ' ', $block_class[0] );
			}

			echo '<p><b>' , esc_html__( 'CSS Class:', 'block-reference' ) , '</b> ' ,
			'<code>' , esc_html( $block_class[0] ) , '</code></p>';
		}

		echo '<h3>' , esc_html__( 'Block Grammar & Markup', 'block-reference' ), '</h3>';
		echo '<p><textarea>' . $block_grammar . '</textarea><p>';
	}
}

/**
 * Present the blocks.
 *
 * @since 1.0.0
 */
function block_reference() {

	block_reference_block_form();

	if ( isset( $_POST['block'] ) ) {
		$block = sanitize_text_field( wp_unslash( $_POST['block'] ) );
	} else {
		$block = '*';
	}

	$block_source = plugin_dir_path( __FILE__ ) . 'src';

	// Check if the file exists.
	if ( file_exists( $block_source . '/' . $block . '/block.json' ) || $block === '*' ) {
		foreach ( glob( $block_source . '/' . $block . '/block.json' ) as $file ) {
			$block_info = json_decode( file_get_contents( $file ), true );

			/* Block name */
			$block_name = str_replace( 'core/', '', $block_info['name'] );

			echo '<h2>' , esc_html( ucfirst( $block_name ) ) , '</h2>';

			if ( 'block' === $block_name ) {
				echo '<p><b>', esc_html__( 'Reusable blocks', 'block-reference' ) ,'</b></p>';
			} elseif ( 'freeform' == $block_name ) {
				echo '<p><b>', esc_html__( 'The Classic block', 'block-reference' ) ,'</b></p>';
			}

			/* Block description */
			echo block_reference_block_description( $block_name );

			/* Category */
			if ( isset( $block_info['category'] ) ) {
				echo '<p><b>' , esc_html__( 'Category:', 'block-reference' ) , '</b> ' ,
				esc_html( ucfirst( $block_info['category'] ) ) , '.</p>';
			}

			/* Source */
			echo '<p><b>' , esc_html__( 'Source:', 'block-reference' ) , '</b> ' ,
			'<a href="https://github.com/WordPress/gutenberg/tree/master/packages/block-library/src/' , $block_name , '">',
			esc_html__( 'View source on Github', 'blocklist' ) , '</a></p>';

			block_reference_block_grammar( $block_name );

			/* Parent */
			if ( isset( $block_info['parent'] ) ) {
				echo '<h3>' , esc_html__( 'Parent', 'block-reference' ) , '</h3>';
				echo '<p><i>' , esc_html__( 'Optional.', 'block-reference' ) , '</i> ' ,
				esc_html__( 'Type: Array.', 'block-reference' ) , '</p>';
				echo '<p>' , esc_html__( 'Blocks are able to be inserted into blocks that use InnerBlocks as nested content. Setting parent lets a block require that it is only available when nested within the specified blocks.', 'block-reference' );

				echo '<p> <a href="https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#parent-optional">' ,
				esc_html__( 'Documentation', 'block-reference' ) , '</a></p><p>';

				foreach ( $block_info['parent'] as $parent => $value ) {
					echo '<code>' , $value , '</code>';
				}
				echo '</p>';
			}

			/* Attributes */
			if ( isset( $block_info['attributes'] ) ) {
				echo '<h3>' , esc_html__( 'Attributes', 'block-reference' ) , '</h3>' ,
				'<p><i>' , esc_html__( 'Optional.', 'block-reference' ) , '</i> ' ,
				esc_html__( 'Type: Object.', 'block-reference' ) , '</p>' ,
				'<p>' , esc_html__( 'Attributes provide the structured data needs of a block. They can exist in different forms when they are serialized, but they are declared together under a common interface.', 'block-reference' ) , '</p>' ,
				'<p><a href="https://github.com/WordPress/gutenberg/blob/master/docs/designers-developers/developers/block-api/block-attributes.md">' ,
				esc_html__( 'Documentation', 'block-reference' ) ,'</a></p>';

				$block_attributes = $block_info['attributes'];
				echo '<ul>';
				foreach ( $block_attributes as $block_attribute_name => $block_attribute_value ) {
					echo '<li><b>', ucfirst( $block_attribute_name ) , ':</b>';
					if ( is_array( $block_attribute_value ) ) {
						echo '<ul>';
						foreach ( $block_attribute_value as $inner_block_attribute_name => $inner_block_attribute_value ) {
							echo '<li>&emsp;<b>', ucfirst( $inner_block_attribute_name ) , ':</b> ';
							if ( is_array( $inner_block_attribute_value ) ) {
								foreach ( $inner_block_attribute_value as $name => $value ) {
									if ( ! is_array( $value ) ) {
										if ( ! is_numeric( $name ) ) {
											echo $name , ': ';
										}
										echo $value , ', ';
									}
									// Nested queries, see table block for example.
									if ( is_array( $value ) ) {
										echo '<br><ul><li><b>' , $name , ': </b><ul>';
										foreach ( $value as $name => $value ) {
											if ( $value == null ) {
												$value = '[]';
											}
											if ( is_array( $value ) ) {
												echo '<li><b>', $name, ': </b><ul>';
												foreach ( $value as $name => $value ) {
													echo '<li><b>', $name , ': </b><ul>';
													if ( is_array( $value ) ) {
														foreach ( $value as $name => $value ) {
															echo '<li><b>', $name, ': </b>', $value , '</li>';
														}
													}
													echo '</ul>';
												} // End for each.
												echo '</ul>';
											} else {
												echo '<li><b>', $name, ': </b>', $value , '.</li>';
											}
										} // End for each.
										echo '</ul></li></ul>';
									}
								} // End for each.
							} else {
								if ( $inner_block_attribute_value === true ) {
									esc_html_e( 'True.', 'block-reference' );
								} elseif ( $inner_block_attribute_value === false ) {
									esc_html_e( 'False.', 'block-reference' );
								} else {
									echo ucfirst( $inner_block_attribute_value ), '.<br>';
								}
							}
							echo '</li>';
						} // End for each.
						echo '</ul>';
					}
					echo '</li>';
				} // End for each.
				echo '</ul>';
			}

			/* Supports */
			if ( isset( $block_info['supports'] ) ) {
				echo '<h3>' , esc_html__( 'Supports', 'block-reference' ) , '</h3>';
				echo '<p><i>' , esc_html__( 'Optional.', 'block-reference' ) , '</i> ' ,
				esc_html__( 'Type: Object.', 'block-reference' ) , '</p>';
				echo '<p>' , esc_html__( 'Optional block extended support features.', 'block-reference' ) , '</p>';
				echo '<p><a href="https://github.com/WordPress/gutenberg/blob/master/docs/designers-developers/developers/block-api/block-attributes.md">' ,
				esc_html__( 'Documentation', 'block-reference' ) , '</a></p>';

				$block_supports = $block_info['supports'];

				echo '<ul>';
				foreach ( $block_supports as $block_support_name => $block_support_value ) {
					echo '<li><b>', ucfirst( $block_support_name ) , ':</b> ';
					if ( $block_support_value === true || $block_support_value === 1 ) {
						esc_html_e( 'True.', 'block-reference' );
						echo '<br>';
					} elseif ( $block_support_value === false ) {
						esc_html_e( 'False.', 'block-reference' );
						echo '<br>';
					} elseif ( is_array( $block_support_value ) ) {
						foreach ( $block_support_value as $inner_block_support_name => $inner_block_support_value ) {
							if ( is_array( $inner_block_support_value ) ) {
								echo ucfirst( $inner_block_support_name ) , ': ';
								foreach ( $inner_block_support_value as $name => $value ) {
									echo $name , ': ';
									if ( $value === true || $value === 1 ) {
										esc_html_e( 'True.', 'block-reference' );
									} elseif ( $value === false ) {
										esc_html_e( 'False.', 'block-reference' );
									}
								}
							} else {
								if ( $inner_block_support_name !== 0 && $inner_block_support_name !== 1 ) {
									echo $inner_block_support_name , ': ';
								}
								if ( $inner_block_support_value === true || $inner_block_support_value === 1 ) {
									esc_html_e( 'True. ', 'block-reference' );
								} elseif ( $inner_block_support_value === false ) {
									esc_html_e( 'False. ', 'block-reference' );
								} else {
									echo $inner_block_support_value , ', ';
								}
							}
						} // End for each.
						echo '<br>';
					} else {
						echo ucfirst( $block_support_value ), '.<br>';
					}
					echo '</li>';
				} // End for each.
				echo '</ul>';

				block_reference_block_transforms( $block_name );

				echo '<hr class="wp-block-separator is-style-wide" style="background:#000;" />';
			}
		}
	} else {
		// Rich image?
		esc_html_e( 'There is no information to present at this time. This block has no block.json file.', 'block-reference' );
	}
}
