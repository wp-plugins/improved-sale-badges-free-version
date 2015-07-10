<?php

	/*
	 * Improved Sale Badges Settings
	 */
	class WC_Isb_Settings_Free {

		public static $isb_style;
		public static $isb_style_special;
		public static $isb_color;
		public static $isb_position;

		public static function init() {

			self::$isb_style = array(
				'isb_style_pop' => __( 'Pop', 'isbwoo') . ' SVG',
				'isb_style_xmas_1' => __( 'Bonus - Christmas 1', 'isbwoo') . ' SVG'
			);

			self::$isb_color = array(
				'isb_green' => __( 'Green', 'isbwoo'),
				'isb_orange' => __( 'Orange', 'isbwoo'),
				'isb_red' => __( 'Red', 'isbwoo'),
				'isb_marine' => __( 'Marine', 'isbwoo'),
			);
			self::$isb_position = array(
				'isb_left' => __( 'Left', 'isbwoo'),
				'isb_right' => __( 'Right', 'isbwoo')
			);

			add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::isb_add_settings_tab', 50 );
			add_action( 'woocommerce_settings_tabs_isb', __CLASS__ . '::isb_settings_tab' );
			add_action( 'woocommerce_update_options_isb', __CLASS__ . '::isb_save_settings' );
			add_action( 'woocommerce_admin_field_isb_preview', __CLASS__ . '::isb_preview', 10 );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::isb_scripts' );
			add_action( 'wp_ajax_isb_respond', __CLASS__ . '::isb_respond' );

		}

		public static function isb_scripts($hook) {
			if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' || $_GET['page'] == 'woocommerce_settings') && $_GET['tab'] == 'isb' ) {
				wp_enqueue_style( 'isb-style', plugins_url( 'assets/css/admin.css', dirname(__FILE__) ) );
				wp_enqueue_script( 'isb-admin', plugins_url( 'assets/js/admin.js', dirname(__FILE__) ), true );

				$curr_args = array(
					'ajax' => admin_url( 'admin-ajax.php' ),
				);
				wp_localize_script( 'isb-admin', 'isb', $curr_args );
			}
		}

		public static function isb_add_settings_tab( $settings_tabs ) {
			$settings_tabs['isb'] = __( 'Improved Sale Badges', 'isbwoo' );
			return $settings_tabs;
		}

		public static function isb_settings_tab() {
			woocommerce_admin_fields( self::isb_get_settings() );
		}

		public static function isb_save_settings() {
			woocommerce_update_options( self::isb_get_settings() );
		}

		public static function isb_preview( $field ) {
			global $woocommerce;
		?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
					<?php echo '<img class="help_tip" data-tip="' . esc_attr( $field['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />'; ?>
				</th>
				<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
					<div id="isb_preview">
				<?php
					global $isb_set;

					$isb_set['style'] = get_option( 'wc_settings_isb_style', 'isb_style_pop' );
					$isb_set['color'] = get_option( 'wc_settings_isb_color', 'isb_red' );
					$isb_set['position'] = get_option( 'wc_settings_isb_position', 'isb_right' );

					$isb_price['type'] = 'simple';
					$isb_price['id'] = get_the_ID();
					$isb_price['regular'] = 32;
					$isb_price['sale'] = 27;
					$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
					$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

					if ( is_array($isb_set) ) {
						$isb_class = $isb_set['style'] . ' ' . $isb_set['color'] . ' ' . $isb_set['position'];
					}
					else {
						$isb_class = 'isb_style_pop isb_red isb_right';
					}

					$isb_curr_set = $isb_set;

					$include = WC_Improved_Sale_Badges_Free::$path . 'includes/styles/' . $isb_set['style'] . '.php';
					include($include);
				?>
					</div>
				</td>
			</tr>
		<?php
		}

		public static function isb_get_settings() {

			$settings = array(
				'section_settings_title' => array(
					'name'     => __( 'Improved Sale Badges for WooCommerce - Settings', 'isbwoo' ),
					'type'     => 'title',
					'desc'     => __( 'Setup shop sale badges. Select style and configure basic appearance options.', 'isbwoo' ) . ' Get Premium Version at this link <a href="http://bit.ly/1GN7IR4" target="_blank">http://bit.ly/1GN7IR4</a>! Get more awesome plugins by <a href="http://mihajlovicnenad.com" target="_blank">http://mihajlovicnenad.com</a> here <a href="http://bit.ly/1IreccI" target="_blank">http://bit.ly/1IreccI</a>!',
					'id'       => 'isb_settings_title'
				),
				'wc_settings_isb_preview' => array(
					'name'    => __( 'Badge Preview', 'isbwoo' ),
					'type'    => 'isb_preview',
					'desc'    => __( 'Quick sale badge style preview.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_preview',
					'desc_tip' =>  true
				),
				'wc_settings_isb_style' => array(
					'name'    => __( 'Badge Style', 'isbwoo' ),
					'type'    => 'select',
					'desc'    => __( 'Select sale badge style.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_style',
					'default' => 'basic',
					'desc_tip' =>  true,
					'options' => self::$isb_style,
					'css' => 'width:300px;margin-right:12px;'
				),
				'wc_settings_isb_color' => array(
					'name'    => __( 'Badge Color', 'isbwoo' ),
					'type'    => 'select',
					'desc'    => __( 'Select sale badge color.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_color',
					'default'     => 'red',
					'desc_tip' =>  true,
					'options' => self::$isb_color,
					'css' => 'width:300px;margin-right:12px;'
				),
				'wc_settings_isb_position' => array(
					'name'    => __( 'Badge Position', 'isbwoo' ),
					'type'    => 'select',
					'desc'    => __( 'Select sale badge position.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_position',
					'default'     => 'left',
					'desc_tip' =>  true,
					'options' => self::$isb_position,
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_settings_end' => array(
					'type' => 'sectionend',
					'id' => 'isb_settings_end'
				),
				'section_advanced_title' => array(
					'name'     => __( 'Improved Sale Badges for WooCommerce - Advanced', 'isbwoo' ),
					'type'     => 'title',
					'desc'     => __( 'Setup advanced options for the plugin.', 'isbwoo' ),
					'id'       => 'isb_advanced_title'
				),
				'wc_settings_isb_archive_action' => array(
					'name' => __( 'Override Default Product Archive Action', 'isbwoo' ),
					'type' => 'text',
					'desc' => __( 'Change default init action on product archives. Use actions initiated in your content-product.php file. Use this option if the badges do not appear on your product archive pages.', 'isbwoo' ),
					'id'   => 'wc_settings_isb_archive_action',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'wc_settings_isb_single_action' => array(
					'name' => __( 'Override Default Single Product Action', 'isbwoo' ),
					'type' => 'text',
					'desc' => __( 'Change default init action on single product pages. Use actions initiated in your content-single-product.php file. Use this option if the badges do not appear on your single product pages.', 'isbwoo' ),
					'id'   => 'wc_settings_isb_single_action',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_advanced_end' => array(
					'type' => 'sectionend',
					'id' => 'isb_advanced_end'
				)
			);

			return apply_filters( 'wc_isb_settings', $settings );
		}

		public static function isb_respond() {
			if ( !isset($_POST['data']) ) {
				die();
				exit;
			}
			
			$isb_set = array(
				'style' => ( $_POST['data'][0] !== '' ? $_POST['data'][0] : get_option( 'wc_settings_isb_style', 'isb_style_pop' ) ),
				'color' => ( $_POST['data'][1] !== '' ? $_POST['data'][1] : get_option( 'wc_settings_isb_color', 'isb_red' ) ),
				'position' => ( $_POST['data'][2] !== '' ? $_POST['data'][2] : get_option( 'wc_settings_isb_position', 'isb_left' ) ),
				'type' => 'simple'
			);

			if ( isset($_POST['data'][3]) ) {
				$isb_set['special'] = $_POST['data'][3];
			}
			if ( isset($_POST['data'][4]) ) {
				$isb_set['special_text'] = $_POST['data'][4];
			}

			$isb_price['type'] = 'simple';
			$isb_price['regular'] = 32;
			$isb_price['sale'] = 27;
			$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
			$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

			if ( is_array($isb_set) ) {
				$isb_class = ( isset($isb_set['special']) && $isb_set['special'] !== '' ? $isb_set['special'] : $isb_set['style'] ) . ' ' . $isb_set['color'] . ' ' . $isb_set['position'];
			}
			else {
				$isb_class = 'isb_style_pop isb_red isb_right';
			}

			$isb_curr_set = $isb_set;

			if ( isset($isb_set['special']) && $isb_set['special'] !== '' ) {
				$include = WC_Improved_Sale_Badges_Free::$path . 'includes/specials/' . $isb_set['special'] . '.php';
			}
			else {
				$include = WC_Improved_Sale_Badges_Free::$path . 'includes/styles/' . $isb_set['style'] . '.php';
			}
			

			ob_start();

			include($include);

			$html = ob_get_clean();

			die($html);
			exit;

		}

	}

	add_action( 'init', array( 'WC_Isb_Settings_Free', 'init' ), 999 );

?>