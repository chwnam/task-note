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
