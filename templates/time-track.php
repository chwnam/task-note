<?php
/**
 * @var array $projects
 * @var string $project_slug
 * @var string $tracking_name
 * @var string $untagged
 * @var string $untitled
 */
?>

<div id="time-track-panel">
	<div id="info-area">
		<p>
			<a
				href="#"
				id="project-tag"
				role="button"
				data-slug="line-engineering"
				data-untagged="<?php echo esc_attr( $untagged ); ?>"
			><?php echo esc_html( $projects[ $project_slug ] ?? $untagged ); ?></a>
			<span id="project-status-desc"></span>
		</p>
		<p id="edit-project-tag-area" style="display:none;">
			<label
				for="project-tag-select"
				class="screen-reader-text"
			>프로젝트 태그 선택</label>
			<select id="project-tag-select">
				<?php foreach ( $projects as $slug => $name ): ?>
					<option
						value="<?php echo esc_attr( $slug ); ?>"
						<?php checked( $slug, $project_slug ); ?>
					><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<a
				href="#"
				id="project-tag-apply"
				class="edit-button apply"
				role="button"
			>선택</a>
			<a
				href="#"
				id="project-tag-cancel"
				class="edit-button cancel"
				role="button"
			>취소</a>
		</p>

		<p><a
				id="tracking-title"
				href="#"
				role="button"
				data-untitled="<?php echo esc_attr( $untitled ); ?>"
			><?php echo esc_html( $tracking_name ); ?></a></p>
		<p id="edit-tracking-title-area" style="display:none;">
			<label
				for="new-tracking-title"
				class="screen-reader-text"
			>새 트래킹 이름</label>
			<input
				id="new-tracking-title"
				name="new_tracking_title"
				type="text"
				class="text"
				value=""
			>
			<a
				href="#"
				id="tracking-title-apply"
				class="edit-button apply"
				role="button"
			>선택</a>
			<a
				href="#"
				id="tracking-title-cancel"
				class="edit-button cancel"
				role="button"
			>취소</a>
		</p>

		<p id="tracking-timer">00:00:00</p>
	</div>
	<div id="button-area">
		<button
			id="time-track-panel-button"
			type="button"
			data-status="initial"
		><span class="dashicons dashicons-controls-play"></span></button>
	</div>
	<div class="wp-clearfix"></div>
</div>
