<?php

	/* LOOP */

	global $product, $isb_set;

	$isb_sale_flash = false;

	if ( $product->is_type( 'simple' ) || $product->is_type('external') ) {

		$sale_price_dates_from = get_post_meta( get_the_ID(), '_sale_price_dates_from', true );
		$sale_price_dates_to = get_post_meta( get_the_ID(), '_sale_price_dates_to', true );

		if ( !empty( $sale_price_dates_from ) && !empty( $sale_price_dates_to ) ) {
			$current_time = current_time( 'mysql', $gmt = 0 );
			$newer_date = strtotime( $current_time );

			$since = $newer_date - $sale_price_dates_from;

			if ( 0 > $since ) {
				$isb_price['time'] = $sale_price_dates_from;
				$isb_price['time_mode'] = 'start';
			}

			if ( !isset($isb_price['time']) ) {
				$since = $newer_date - $sale_price_dates_to;
				if ( 0 > $since ) {
					$isb_price['time'] = $sale_price_dates_to;
					$isb_price['time_mode'] = 'end';
				}
			}
		}

		if ( $product->get_price() > 0 && ( $product->is_on_sale() || isset($isb_price['time']) ) !== false ) {

			$isb_price['type'] = 'simple';

			$isb_price['id'] = get_the_ID();

			$isb_price['regular'] = floatval( $product->get_regular_price() );

			$isb_price['sale'] = floatval( $product->get_sale_price() );

			$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];

			$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

			$isb_sale_flash = true;

		}

	}
	else if ( $product->is_type( 'variable' ) ) {

		$isb_variations = $product->get_available_variations();
		$isb_check = 0;
		$isb_check_time = 0;

		foreach( $isb_variations as $var ) {

			$curr_product[$var['variation_id']] = new WC_Product_Variation( $var['variation_id'] );

			$sale_price_dates_from = get_post_meta( $var['variation_id'], '_sale_price_dates_from', true );
			$sale_price_dates_to = get_post_meta( $var['variation_id'], '_sale_price_dates_to', true );

			if ( !empty( $sale_price_dates_from ) && !empty( $sale_price_dates_to ) ) {
				$current_time = current_time( 'mysql', $gmt = 0 );
				$newer_date = strtotime( $current_time );

				$since = $newer_date - $sale_price_dates_from;

				if ( 0 > $since ) {
					$check_time = $sale_price_dates_from;
					$check_time_mode = 'start';
				}

				if ( !isset($check_time) ) {
					$since = $newer_date - $sale_price_dates_to;
					if ( 0 > $since ) {
						$check_time = $sale_price_dates_to;
						$check_time_mode = 'end';
					}
				}

				if ( $check_time > $isb_check_time ) {
					$isb_price['time'] = $check_time;
					$isb_price['time_mode'] = $check_time_mode;
				}
			}

			if ( $curr_product[$var['variation_id']]->is_on_sale() ) {

				$isb_var_regular_price = $curr_product[$var['variation_id']]->regular_price;
				$isb_var_sales_price = $curr_product[$var['variation_id']]->sale_price;

				$isb_diff = $isb_var_regular_price - $isb_var_sales_price ;

				if ( $isb_diff > $isb_check ) {
					$isb_check = $isb_diff;
					$isb_var = $var['variation_id'];
				}
			}

		}

		if ( isset( $isb_var ) ) {

			$isb_price['type'] = 'variable';

			$isb_price['id'] = $var['variation_id'];

			$isb_price['regular'] = floatval( $curr_product[$isb_var]->get_regular_price() );

			$isb_price['sale'] = floatval( $curr_product[$isb_var]->get_sale_price() );

			$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];

			$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

			$isb_sale_flash = true;

		}

	}

	if ( $isb_sale_flash === true ) {

		$isb_curr_set = $isb_set;
		if ( is_array($isb_curr_set) ) {
			$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
		}
		else {
			$isb_class = 'isb_style_pop isb_red isb_right';
		}

		$include = WC_Improved_Sale_Badges_Free::isb_get_path() . 'includes/styles/' . $isb_curr_set['style'] . '.php';
		include($include);

	}

?>