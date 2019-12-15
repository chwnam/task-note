<?php

function tn_template( string $template_name, array $context = [], bool $echo = true ): ?string {
	$path = plugin_dir_path( TASK_NOTE_MAIN ) . 'templates/' . trim( $template_name, '/' );
	if ( is_readable( $path ) ) {
		if ( ! empty( $context ) ) {
			extract( $context, EXTR_SKIP );
		}
		if ( ! $echo ) {
			ob_start();
		}
		/** @noinspection PhpIncludeInspection */
		include $path;
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	return null;
}


function format_datetime( int $timestamp, string $format = null ): string {
	if ( ! $format ) {
		$format = 'Y-m-d H:i:s';
	}

	// note: 5.3 부터 가능
	$date = wp_date( $format, $timestamp );

	if ( ! is_string( $date ) ) {
		$date = '';
	}

	return $date;
}


function format_timespan( int $timespan ): string {
	if ( $timespan > HOUR_IN_SECONDS ) {
		$hour     = intval( $timespan / HOUR_IN_SECONDS );
		$timespan %= HOUR_IN_SECONDS;
	} else {
		$hour = 0;
	}

	if ( $hour ) {
		$fmt = '%02$1d 시간 %02d$2d분 %02$2d초';
	} else {
		$fmt = '%02$2d분 %02$2d초';
	}

	$minute = intval( $timespan / MINUTE_IN_SECONDS );

	$second = $timespan % MINUTE_IN_SECONDS;

	return sprintf( $fmt, $hour, $minute, $second );
}
