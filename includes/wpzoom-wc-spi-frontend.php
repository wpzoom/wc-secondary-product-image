<?php

//Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) 
	exit;

/**
 * WPZOOM_WC_Secondary_Image_Frontend Class
 *
 * Frontend output class
 *
 * @since 1.0.0
 */

if ( ! class_exists( 'WPZOOM_WC_Secondary_Image_Frontend' ) ) {

	class WPZOOM_WC_Secondary_Image_Frontend {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		public function __construct() {
			
			if ( ! is_admin() ) {
				
				add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
				
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'output_secondary_product_thumbnail' ), 15 );
				
				add_filter( 'post_class', array( $this, 'set_product_post_class' ), 21, 3 );
			}

		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Enqueue WCSPT front-end styles and scripts.
		 */
		public function load_frontend_scripts() {

			wp_enqueue_style(
				'wpzoom-wc-spi-style', 
				WPZOOM_WC_SPI_URL . 'assets/css/wc-secondary-product-image.css', 
				array(), 
				WPZOOM_WC_SPI_VER 
			);
		}

		public function output_secondary_product_thumbnail() {
			echo $this->add_secondary_product_thumbnail();
		}
		
		/*
		* Output the secondary product thumbnail.
		*
		* @param string $size (default: 'woocommerce_thumbnail').
		* @param int    $deprecated1 Deprecated since WooCommerce 2.0 (default: 0).
		* @param int    $deprecated2 Deprecated since WooCommerce 2.0 (default: 0).
		* @return string
		*/
		public function add_secondary_product_thumbnail( $size = 'woocommerce_thumbnail', $deprecated1 = 0, $deprecated2 = 0 ) {

			global $product;

			$image_ids = $this->get_gallery_img_ids( $product );

			$image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

			$classes          = 'attachment-' . $image_size . ' wpzoom-wc-spi-secondary-img wpzoom-wc-spi-transition';
			$secondary_img_id = get_post_meta( $product->get_id(), 'product_wpzoom-product-secondary-image_thumbnail_id', true );

			if( !empty( $secondary_img_id ) ) {
				return wp_get_attachment_image( $secondary_img_id, $image_size, false, array( 'class' => $classes ) );
			}
			elseif ( $image_ids ) {
				$secondary_img_id = apply_filters( 'wpzoom_wc_spi_reveal_last_img', false ) ? end( $image_ids ) : reset( $image_ids );				
				return wp_get_attachment_image( $secondary_img_id, $image_size, false, array( 'class' => $classes ) );
			}
		}


		
		/**
		 * Returns the gallery image ids.
		 *
		 * @param WC_Product $product
		 * @return array
		 */
		public function get_gallery_img_ids( $product ) {
			if ( method_exists( $product, 'get_gallery_image_ids' ) ) {
				$image_ids = $product->get_gallery_image_ids();
			} else {
				// Deprecated in WC 3.0.0
				$image_ids = $product->get_gallery_attachment_ids();
			}
			
			return $image_ids;
		}

		/**
		 * Add wcspt-has-gallery class to products that have at least one gallery image.
		 *
		 * @param array $classes
		 * @param array $class
		 * @param int $post_id
		 * @return array
		 */
		public function set_product_post_class( $classes, $class, $post_id ) {

			if ( ! $post_id || get_post_type( $post_id ) !== 'product' ) {
				return $classes;
			}
			
			global $product;
			
			if ( is_object( $product ) ) {
				
				$secondary_img_id = get_post_meta( $product->get_id(), 'product_wpzoom-product-secondary-image_thumbnail_id', true );
				$image_ids = $this->get_gallery_img_ids( $product );
				
				if ( $image_ids || $secondary_img_id ) {
					$classes[] = 'wpzoom-wc-spi-has-enabled';
				}
			}
			
			return $classes;
		}

	}
	new WPZOOM_WC_Secondary_Image_Frontend;
}