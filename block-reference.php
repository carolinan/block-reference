<?php
/**
 * Plugin Name: Block reference
 * Description: Presents a list of blocks and their class names, attributes etc.
 * Author: Carolina Nymark
 * Version: 1.0.1
 * Text Domain: block-reference
 * Requires at least: 5.4
 * Tested up to: 5.6
 * Requires PHP: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package block reference
 */

/**
 * How to update
 * Place these block files inside the src folder:
 * Index.js
 * transform.js
 * block.json
 */

define( 'BLOCK_REFERENCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( ! class_exists( 'Block_Reference' ) ) :

	/**
	 * Create a class to fetch and present the block information.
	 */
	class Block_Reference {

		/**
		 * List of block names
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private static $blocklist = [
			'archives', 'audio', 'block', 'button', 'buttons', 'calendar', 'categories', 'freeform', 'code', 'column',
			'columns', 'cover', 'file', 'gallery', 'group', 'heading', 'html', 'image', 'latest-comments', 'latest-posts', 'legacy-widget',
			'list', 'media-text', 'more', 'navigation-link', 'navigation', 'nextpage', 'paragraph', 'post-author',
			'post-comment', 'post-comment-author', 'post-comment-content', 'post-comment-date', 'post-comments',
			'post-comments-count', 'post-comments-form', 'post-content', 'post-date', 'post-excerpt',
			'post-featured-image', 'post-hierarchical-terms', 'post-tags', 'post-title', 'preformatted', 'pullquote',
			'query', 'query-loop', 'query-pagination', 'quote', 'rss', 'search', 'separator', 'shortcode', 'site-title',
			'social-link', 'social-links', 'spacer', 'table', 'tag-cloud', 'template-part','verse', 'video',
			'widget-area',
		];

		/**
		 * List of attributes
		 *
		 * @since 1.0.1
		 * @var array
		 */
		private static $attributelist = [
			'displayAsDropdown', 'showPostCounts', 'src', 'caption', 'id', 'autoplay', 'loop', 'preload', 'ref',
			'url', 'title', 'text', 'linkTarget', 'rel', 'placeholder', 'borderRadius', 'style', 'backgroundColor',
			'textColor', 'gradient', 'month', 'year', 'showHierarchy', 'content', 'verticalAlignment', 'width',
			'templateLock', 'hasParallax', 'isRepeated', 'dimRatio', 'overlayColor', 'customOverlayColor',
			'backgroundType', 'focalPoint', 'minHeight', 'minHeightUnit', 'customGradient', 'contentPosition',
			'providerNameSlug', 'allowResponsive', 'responsive', 'previewable', 'href', 'fileName', 'textLinkHref',
			'textLinkTarget', 'showDownloadButton', 'downloadButtonText', 'images', 'ids', 'columns', 'imageCrop',
			'linkTo', 'sizeSlug', 'tagName', 'align', 'level', 'alt', 'linkClass', 'height', 'linkDestination',
			'commentsToShow', 'displayAvatar', 'displayDate', 'displayExcerpt', 'categories', 'selectedAuthor',
			'postsToShow', 'displayPostContent', 'displayPostContentRadio', 'excerptLength', 'displayAuthor',
			'displayPostDate', 'postLayout', 'order', 'orderBy', 'displayFeaturedImage', 'featuredImageAlign',
			'featuredImageSizeSlug', 'featuredImageSizeWidth', 'featuredImageSizeHeight', 'addLinkToFeaturedImage',
			'ordered', 'values', 'type', 'start', 'reversed', 'mediaAlt', 'mediaPosition', 'mediaId', 'mediaUrl',
			'mediaLink', 'mediaType', 'mediaWidth', 'mediaSizeSlug', 'isStackedOnMobile', 'imageFill', 'customText',
			'noTeaser', 'orientation', 'customTextColor', 'rgbTextColor', 'customBackgroundColor', 'rgbBackgroundColor',
			'itemsJustification', 'showSubmenuIcon', 'label', 'opensInNewTab', 'description', 'direction', 'dropCap',
			'textAlign', 'avatarSize', 'showAvatar', 'showBio', 'byline', 'commentId', 'format', 'wordCount', 'moreText',
			'showMoreOnNewLine', 'isLink', 'term', 'citation', 'mainColor', 'customMainColor', 'queryId', 'query',
			'blockLayout', 'feedURL', 'itemsToShow', 'showLabel', 'widthUnit', 'buttonText', 'buttonPosition',
			'buttonUseIcon', 'color', 'customColor', 'service', 'openInNewTab', 'taxonomy', 'showTagCounts', 'postId',
			'slug', 'theme', 'controls', 'muted', 'poster', 'playsInline', 'tracks', 'contentJustification',
		];

		/**
		 * List of block supports
		 *
		 * @since 1.0.1
		 * @var array
		 */
		private static $supportlist = [
			'align', 'anchor', 'color', 'customClassName', 'fontSize', 'html', 'inserter', 'lineHeight',
			'multiple', 'reusable', 'spacing', '__experimentalFontAppearance', '__experimentalTextTransform',
			'__experimentalFontFamily', '__experimentalTextDecoration',
			'__experimentalSelector', '__unstablePasteTextInline',
		];

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 1.0.1
		 */
		public function __construct() {
			register_activation_hook( __FILE__, [ $this, 'create_block_reference_page' ] );
			add_filter( 'the_content', [ $this, 'filter_output' ], 10, 2 );
		}

		/**
		 * Present the content on the selected page.
		 *
		 * @access public
		 * @since 1.0.1
		 * @param var $content post content.
		 */
		public function filter_output( $content ) {
			if ( get_the_title() === 'Block Reference' ) {
				ob_start();
				self::output();
				return ob_get_clean();
			} else {
				return $content;
			}
		}

		/**
		 * Create a page for displaying the content.
		 *
		 * @access private
		 * @since 1.0.1
		 */
		public function create_block_reference_page() {

			// Avoid creating duplicate pages if a page is already published.
			if ( post_exists( 'Block Reference', '', '', 'page' ) ) {
				$post_id = post_exists( 'Block Reference', '', '', 'page' );
				if ( get_post_status( $post_id ) === 'publish' ) {
					return;
				}
			}

			$reference_page = array(
				'post_title'  => wp_strip_all_tags( 'Block Reference' ),
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type'   => 'page',
			);

			// Insert the page into the database.
			wp_insert_post( $reference_page );
		}

		/**
		 * The form for sorting the blocks.
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private function block_form() {
			echo '<form action="' , esc_url( get_page_link() ) , '" method="post">';
			echo '<label>' , esc_html__( 'Select a block to view:', 'block-reference' );
			echo '<br><select name="block">';
			echo '<option value="*" selected>' , esc_html__( 'All', 'block-reference' ) , '</option>';

			// Get the array of blocks.
			$blocklist = self::$blocklist;
			foreach ( $blocklist as $block_name ) {
				if ( 'freeform' === $block_name ) {
					$block_name = 'classic';
				}
				$display_name = str_replace( '-', ' ', $block_name );
				$display_name = ucfirst( $display_name );
				echo '<option value="' , esc_attr( $block_name ) , '">' , esc_html( $display_name ) , '</option>';
			}
			echo '</select></label><br>';

			echo '<br><label>' , esc_html__( 'View blocks with the following attribute:', 'block-reference' );
			echo '<br><select name="attribute">';
			echo '<option value="*" selected>' , esc_html__( 'Select attribute', 'block-reference' ) , '</option>';
			// Get the array of attributes.
			$attributelist = self::$attributelist;
			foreach ( $attributelist as $attribute_name ) {
				echo '<option value="' , esc_attr( $attribute_name ) , '">' , esc_html( $attribute_name ) , '</option>';
			}
			echo '</select>';
			echo '</label><br>';

			echo '<br><label>' , esc_html__( 'View block support', 'block-reference' );
			echo '<br><select name="support">';
			echo '<option value="*" selected>' , esc_html__( 'Select support', 'block-reference' ) , '</option>';
			// Get the array of supports.
			$supportlist = self::$supportlist;
			foreach ( $supportlist as $support_name ) {
				echo '<option value="' , esc_attr( $support_name ) , '">' , esc_html( $support_name ) , '</option>';
			}
			echo '</select>';
			echo '</label><br>';

			wp_nonce_field( 'block_form_action', 'block_form_nonce_field' );
			echo '<br><input class="button" type="submit" value="' , esc_attr__( 'Continue', 'block-reference' ) , '" />';
			echo '</form><br><hr class="wp-block-separator is-style-wide" style="background:#000;" />';
		}

		/**
		 * Get the description from index.js.
		 *
		 * @access private
		 * @param var $block block name.
		 * @since 1.0.0
		 */
		private function block_description( $block ) {
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
		 * Get block variations from variation.js
		 *
		 * @param var $block block name.
		 * @since 1.0.1
		 */
		private function block_variations( $block ) {
			$block_source = plugin_dir_path( __FILE__ ) . 'src/' . $block . '/variations.js';
			if ( 'legacy-widget' !== $block && file_exists( $block_source ) ) {
				$block_variations = file_get_contents( $block_source );

				if ( strpos( $block_variations, 'description' ) ) {
					echo '<h3>' , esc_html__( 'Block variation', 'block-reference' ), '</h3>';
					// Remove everything before the first description.
					$block_variations = strstr( $block_variations, 'description:' );
					$block_variations = explode( 'description:', $block_variations );

					foreach ( $block_variations as $block_variation ) {
						$block_variation = strstr( $block_variation, '\' ),', true );
						$block_variation = str_replace( "__( '", '', $block_variation );
						echo '<p>' , esc_html( $block_variation ) , '</p>';
					}
				}
			}
		}

		/**
		 * Get block grammar / markup.
		 *
		 * @param var $block block name.
		 * @since 1.0.0
		 */
		private function block_grammar( $block ) {
			$block_source = plugin_dir_path( __FILE__ ) . 'html/core__' . $block . '.html';
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
		 * Get the transform from transform.js.
		 *
		 * @param var $block block name.
		 * @since 1.0.0
		 */
		private function block_transforms( $block ) {
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
					if ( strpos( $line, 'blocks: [' ) && strpos( $line, '*' ) == false ) {

						$from_block = $line;
						$from_block = explode( "[ 'core/", $from_block );
						$from_block = explode( "' ],", $from_block[1] );
						$from_block = $from_block[0];
						$from_block = str_replace( 'core/', '', $from_block );
						$from_block = str_replace( "'", '', $from_block );
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
						echo esc_html( $to ) , ', ';
					}
					echo '</p>';
				}

				if ( ! empty( $from ) ) {
					$from = array_unique( $from );
					echo '<p>' , esc_html__( 'Transforms to: ', 'block-reference' );
					foreach ( $from as $from ) {
						echo esc_html( $from ), ', ';
					}
					echo '</p>';
				}
			}
		}

		/**
		 *  Print the information about the Blocks
		 *
		 * @access public
		 * @since 1.0.0
		 *
		 * @param var $block block identifier.
		 */
		public function block_result( $block ) {
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
					} elseif ( 'freeform' === $block_name ) {
						echo '<p><b>', esc_html__( 'The Classic block', 'block-reference' ) ,'</b></p>';
					}

					/* Block description */
					echo wp_kses_post( $this->block_description( $block_name ) );

					/* Category */
					if ( isset( $block_info['category'] ) ) {
						echo '<p><b>' , esc_html__( 'Category:', 'block-reference' ) , '</b> ' ,
						esc_html( ucfirst( $block_info['category'] ) ) , '.</p>';
					}

					/* Source */
					echo '<p><b>' , esc_html__( 'Source:', 'block-reference' ) , '</b> ' ,
					'<a href="https://github.com/WordPress/gutenberg/tree/master/packages/block-library/src/' , $block_name , '">',
					esc_html__( 'View source on Github', 'blocklist' ) , '</a></p>';

					self::block_variations( $block_name );

					self::block_grammar( $block_name );

					/* Parent */
					if ( isset( $block_info['parent'] ) ) {
						echo '<h3>' , esc_html__( 'Parent', 'block-reference' ) , '</h3>';
						echo '<p><i>' , esc_html__( 'Optional.', 'block-reference' ) , '</i> ' ,
						esc_html__( 'Type: Array.', 'block-reference' ) , '</p>';
						echo '<p>' , esc_html__( 'Blocks are able to be inserted into blocks that use InnerBlocks as nested content. Setting parent lets a block require that it is only available when nested within the specified blocks.', 'block-reference' );

						echo '<p> <a href="https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#parent-optional">' ,
						esc_html__( 'Documentation', 'block-reference' ) , '</a></p><p>';

						foreach ( $block_info['parent'] as $parent => $value ) {
							echo esc_html__( 'Parent:', 'block-reference' ), ' <code>' , esc_html( $value ) , '</code>';
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
							echo '<li><b>', esc_html( ucfirst( $block_attribute_name ) ) , ':</b>';
							if ( is_array( $block_attribute_value ) ) {
								echo '<ul>';
								foreach ( $block_attribute_value as $inner_block_attribute_name => $inner_block_attribute_value ) {
									echo '<li>&emsp;<b>', esc_html( ucfirst( $inner_block_attribute_name ) ) , ':</b> ';
									if ( is_array( $inner_block_attribute_value ) ) {
										foreach ( $inner_block_attribute_value as $name => $value ) {
											if ( ! is_array( $value ) ) {
												if ( ! is_numeric( $name ) ) {
													echo esc_html( $name ) , ': ';
												}
												echo esc_html( $value ) , ', ';
											}
											// Nested queries, see table block for example.
											if ( is_array( $value ) ) {
												echo '<br><ul><li><b>' , esc_html( $name ) , ': </b><ul>';
												foreach ( $value as $name => $value ) {
													if ( null === $value ) {
														$value = '[]';
													}
													if ( is_array( $value ) ) {
														echo '<li><b>', esc_html( $name ), ': </b><ul>';
														foreach ( $value as $name => $value ) {
															echo '<li><b>', esc_html( $name ) , ': </b><ul>';
															if ( is_array( $value ) ) {
																foreach ( $value as $name => $value ) {
																	echo '<li><b>', esc_html( $name ), ': </b>', esc_html( $value ) , '</li>';
																}
															}
															echo '</ul>';
														} // End for each.
														echo '</ul>';
													} else {
														echo '<li><b>', esc_html( $name ) , ': </b>', esc_html( $value ), '.</li>';
													}
												} // End for each.
												echo '</ul></li></ul>';
											}
										} // End for each.
									} else {
										if ( true === $inner_block_attribute_value ) {
											esc_html_e( 'True.', 'block-reference' );
										} elseif ( false === $inner_block_attribute_value ) {
											esc_html_e( 'False.', 'block-reference' );
										} else {
											echo esc_html( ucfirst( $inner_block_attribute_value ) ), '.<br>';
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
							echo '<li><b>', esc_html( ucfirst( $block_support_name ) ) , ':</b> ';
							if ( true === $block_support_value || 1 === $block_support_value ) {
								esc_html_e( 'True.', 'block-reference' );
								echo '<br>';
							} elseif ( false === $block_support_value ) {
								esc_html_e( 'False.', 'block-reference' );
								echo '<br>';
							} elseif ( is_array( $block_support_value ) ) {
								foreach ( $block_support_value as $inner_block_support_name => $inner_block_support_value ) {
									if ( is_array( $inner_block_support_value ) ) {
										echo esc_html( ucfirst( $inner_block_support_name ) ), ': ';
										foreach ( $inner_block_support_value as $name => $value ) {
											echo esc_html( $name ), ': ';
											if ( true === $value || 1 === $value ) {
												esc_html_e( 'True.', 'block-reference' );
											} elseif ( false === $value ) {
												esc_html_e( 'False.', 'block-reference' );
											}
										}
									} else {
										if ( 0 !== $inner_block_support_name && 1 !== $inner_block_support_name ) {
											echo esc_html( $inner_block_support_name ) , ': ';
										}
										if ( true === $inner_block_support_value || 1 === $inner_block_support_value ) {
											esc_html_e( 'True. ', 'block-reference' );
										} elseif ( false === $inner_block_support_value ) {
											esc_html_e( 'False. ', 'block-reference' );
										} else {
											echo esc_html( $inner_block_support_value ), ', ';
										}
									}
								} // End for each.
								echo '<br>';
							} else {
								echo esc_html( ucfirst( $block_support_value ) ), '.<br>';
							}
							echo '</li>';
						} // End for each.
						echo '</ul>';

						self::block_transforms( $block_name );

						echo '<hr class="wp-block-separator is-style-wide" style="background:#000;" />';
					}
				}
			}
		}

		/**
		 * Attribute results
		 *
		 * @access public
		 * @since 1.0.1
		 * @param var $attribute block attribute identifier.
		 */
		public function attribute_result( $attribute ) {
			$block_source = plugin_dir_path( __FILE__ ) . 'src';
			$files        = glob( $block_source . '/*/block.json', GLOB_BRACE );

			echo '<h3>' , esc_html__( 'The attribute ', 'block-reference' ) , '"' , $attribute , '" ' ,
			esc_html__( 'is used by the following blocks: ', 'block-reference' ) , '</h3>';
			echo '<p>';
			foreach ( $files as $file ) {
				$block_info = json_decode( file_get_contents( $file ), true );
				/* Attributes */
				if ( isset( $block_info['attributes'] ) ) {
					$block_attributes = $block_info['attributes'];
					foreach ( $block_attributes as $block_attribute_name => $block_attribute_value ) {
						if ( $block_attribute_name === $attribute ) {
							$block = str_replace( 'core/', '', $block_info['name'] );
							echo $block , ', ';
						}
					} // End for each.
				}
			}
			echo '</p><hr class="wp-block-separator is-style-wide" style="background:#000;" />';
			foreach ( $files as $file ) {
				$block_info = json_decode( file_get_contents( $file ), true );
				/* Attributes */
				if ( isset( $block_info['attributes'] ) ) {
					$block_attributes = $block_info['attributes'];
					foreach ( $block_attributes as $block_attribute_name => $block_attribute_value ) {
						if ( $block_attribute_name === $attribute ) {
							$block = str_replace( 'core/', '', $block_info['name'] );
							self::block_result( $block );
						}
					} // End for each.
				}
			}
		}

		/**
		 * Block support results
		 *
		 * @access public
		 * @since 1.0.1
		 * @param var $support block support identifier.
		 */
		public function support_result( $support ) {
			$block_source = plugin_dir_path( __FILE__ ) . 'src';
			$files        = glob( $block_source . '/*/block.json', GLOB_BRACE );

			printf(
				/* translators: %s: search term. */
				'<h3>' . esc_html__( 'The following blocks have registered support for "%s":', 'block-reference' ) . '</h3>',
				$support
			);
			echo '<p>';
			foreach ( $files as $file ) {
				$block_info = json_decode( file_get_contents( $file ), true );
				/* Supports */
				if ( isset( $block_info['supports'] ) ) {
					$block_supports = $block_info['supports'];
					foreach ( $block_supports as $block_support_name => $block_support_value ) {
						if ( $block_support_name === $support && true == $block_support_value ) {
							$block = str_replace( 'core/', '', $block_info['name'] );
							$block = str_replace( '-', ' ', $block );
							echo $block , ', ';
							$has_support = true;
						}
					}
				}
			}
			if ( ! isset( $has_support ) ) {
				echo '-';
			}

			printf(
				/* translators: %s: search term. */
				'<h3>' . __( 'The following blocks have registered that they do <b>not</b> have support for "%s":', 'block-reference' ) . '</h3>',
				$support
			);
			echo '<p>';
			foreach ( $files as $file ) {
				$block_info = json_decode( file_get_contents( $file ), true );
				/* Supports */
				if ( isset( $block_info['supports'] ) ) {
					$block_supports = $block_info['supports'];
					foreach ( $block_supports as $block_support_name => $block_support_value ) {
						if ( $block_support_name === $support && false == $block_support_value ) {
							$block = str_replace( 'core/', '', $block_info['name'] );
							$block = str_replace( '-', ' ', $block );
							echo $block , ', ';
							$has_no_support = true;
						}
					}
				}
			}
			if ( ! isset( $has_no_support ) ) {
				echo '-';
			}
			echo '</p><hr class="wp-block-separator is-style-wide" style="background:#000;" />';

			echo '<h3>' . esc_html__( 'Block information', 'block-reference' ) . '<h3>';
			foreach ( $files as $file ) {
				$block_info = json_decode( file_get_contents( $file ), true );
				/* Supports */
				if ( isset( $block_info['supports'] ) ) {
					$block_supports = $block_info['supports'];
					foreach ( $block_supports as $block_support_name => $block_support_value ) {
						if ( $block_support_name === $support ) {
							$block = str_replace( 'core/', '', $block_info['name'] );
							self::block_result( $block );
						}
					} // End for each.
				}
			}
		}

		/**
		 * Output the form and the block information.
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function output() {
			self::block_form();

			if ( isset( $_POST['block_form_nonce_field'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['block_form_nonce_field'] ) ), 'block_form_action' ) ) {

				if ( isset( $_POST['block'] ) && '*' !== $_POST['block'] ) {
					$block = sanitize_text_field( wp_unslash( $_POST['block'] ) );
					self::block_result( $block );
				} elseif ( isset( $_POST['attribute'] ) && '*' !== $_POST['attribute'] ) {
					$attribute = sanitize_text_field( wp_unslash( $_POST['attribute'] ) );
					self::attribute_result( $attribute );
				} elseif ( isset( $_POST['support'] ) && '*' !== $_POST['support'] ) {
					$support = sanitize_text_field( wp_unslash( $_POST['support'] ) );
					self::support_result( $support );
				} else {
					$block = '*';
					self::block_result( $block );
				}
			} else {
				$block = '*';
				self::block_result( $block );
			}
		}
	}

endif;

new Block_Reference();
