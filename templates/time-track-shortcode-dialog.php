<?php
/**
 * Context:
 *
 * @var DateTimeImmutable $current_date
 */
?>
<div id="time-track-dialog" style="display: none;">
	<form id="time-track-dialog-form">
		<input type="hidden" id="post_id" name="post_id" value="">
		<input type="hidden" id="nonce" name="nonce" value="">
		<input type="hidden" id="action" name="action" value="">
		<input type="hidden" id="date" name="date" value="<?php echo esc_attr( $current_date->format( 'Y-m-d' ) ); ?>">
		<table>
			<tr>
				<th>
					<label for="title">제목</label>
				</th>
				<td>
					<input
						id="title"
						name="title"
						value=""
						type="text"
						class="text"
						required="required"
					>
				</td>
			</tr>
			<tr>
				<th>
					<label for="project">프로젝트</label>
				</th>
				<td>
					<?php
					wp_dropdown_categories(
						[
							'taxonomy'         => TNCT::PROJECT_TAG,
							'name'             => 'project_id',
							'id'               => 'project_id',
							'show_option_none' => '-- 프로젝트 선택 --'
						]
					);
					?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="content">작업 메모</label>
				</th>
				<td>
				<textarea
					id="content"
					name="content"
					rows="6"
				> </textarea>
				</td>
			</tr>
			<tr>
				<th>시작</th>
				<td>
					<input
						id="begin_hour"
						name="begin_hour"
						type="number"
						class="text time-input"
						size="2"
						min="0"
						max="23"
					>
					<label
						for="begin_hour"
						class="time-input"
					>시</label>

					<input
						id="begin_minute"
						name="begin_minute"
						type="number"
						class="text time-input"
						size="2"
						min="0"
						max="59"
					>
					<label
						for="begin_minute"
						class="time-input"
					>분</label>

					<a
						id="delete-the-begin"
						href="#"
						role="button"
					>삭제</a>
				</td>
			</tr>
			<tr>
				<th>종료</th>
				<td>
					<input
						id="end_hour"
						name="end_hour"
						type="number"
						class="text time-input"
						size="2"
						min="0"
						max="23"
					>
					<label
						for="end_hour"
						class="time-input"
					>시</label>

					<input
						id="end_minute"
						name="end_minute"
						type="number"
						class="text time-input"
						size="2"
						min="0"
						max="59"
					>
					<label
						for="end_minute"
						class="time-input"
					>분</label>

					<a
						id="delete-the-end"
						href="#"
						role="button"
					>삭제</a>

				</td>
			</tr>
			<tr>
				<th>예상</th>
				<td>
					<input
						id="estimated"
						name="estimated"
						type="number"
						class="text time-input"
						size="2"
						min="5"
						step="5"
						required="required"
					>
					<label
						for="estimated"
						class="time-input"
					>분</label>
				</td>
			</tr>
			<tr>
				<th>실제</th>
				<td id="dialog_status"> </td>
			</tr>
		</table>
	</form>
</div>