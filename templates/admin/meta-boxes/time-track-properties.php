<?php
/**
 * Context:
 *
 * @var int $year
 * @var int $month
 * @var int $day
 * @var string $dow
 *
 * @var int $begin_hour
 * @var int $begin_minute
 *
 * @var int $end_hour
 * @var int $end_minute
 *
 * @var int $timespan
 */
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row">날짜</th>
		<td>
            <span><input
					id="time_track_date_year"
					name="time_track_date[year]"
					type="number"
					class="datetime-input"
					min="2019"
					value="<?php echo esc_attr( $year ); ?>"
				><label
					for="time_track_date_year"
					class="datetime-label"
				>년</label></span>

			<span><input
					id="time_track_date_month"
					name="time_track_date[month]"
					type="number"
					class="datetime-input"
					min="1"
					max="12"
					value="<?php echo esc_attr( $month ); ?>"
				><label
					for="time_track_date_month"
					class="datetime-label"
				>월</label></span>

			<span><input
					id="time_track_date_day"
					name="time_track_date[day]"
					type="number"
					class="datetime-input"
					min="1"
					max="31"
					value="<?php echo esc_attr( $day ); ?>"
				><label
					for="time_track_date_day"
					class="datetime-label"
				>일</label></span>

			<label
				class="datetime-label"
			><span id="time_track_date_day_of_week"><?php echo esc_html( $dow ); ?></span>요일</label>
		</td>
	</tr>

	<tr>
		<th scope="row">시작 시간</th>
		<td>
            <span><input
					id="time_track_begin_hour"
					name="time_track_begin[hour]"
					type="number"
					class="datetime-input"
					min="0"
					max="23"
					value="<?php echo esc_attr( $begin_hour ); ?>"
				><label
					for="time_track_begin_hour"
					class="datetime-label"
				>시</label></span>

			<span><input
					id="tn_time_track_begin_minute"
					name="time_track_begin[minute]"
					type="number"
					class="datetime-input"
					min="0"
					max="59"
					value="<?php echo esc_attr( $begin_minute ); ?>"
				><label
					for="tn_time_track_begin_minute"
					class="datetime-label"
				>분</label></span>

			<label class="datetime-label"><span class="description">24시간제로 입력</span></label>
		</td>
	</tr>

	<tr>
		<th scope="row">종료 시간</th>
		<td>
            <span><input
					id="time_track_end_hour"
					name="time_track_end[hour]"
					type="number"
					class="datetime-input"
					min="0"
					max="23"
					value="<?php echo esc_attr( $end_hour ); ?>"
				><label
					for="time_track_end_hour"
					class="datetime-label"
				>시</label></span>

			<span><input
					id="time_track_end_minute"
					name="time_track_end[minute]"
					type="number"
					class="datetime-input"
					min="0"
					max="59"
					value="<?php echo esc_attr( $end_minute ); ?>"
				><label
					for="time_track_end_minute"
					class="datetime-label"
				>분</label></span>

			<label class="datetime-label"><span class="description">24시간제로 입력</span></label>
		</td>
	</tr>

	<tr>
		<th scope="row">작업 시간</th>
		<td>
			<input
				id="timespan"
				type="number"
				class="datetime-input"
				min="0"
				value="<?php echo esc_attr( intval( $timespan / 60 ) ); ?>"
			><label
				for="timespan"
				class="datetime-label"
			>분</label>
		</td>
	</tr>
</table>

<?php wp_nonce_field( 'time_track_properties', 'time_track_nonce', false ); ?>

<style>
	.datetime-input {
		width: 5.4em;
	}

	.datetime-label {
		margin-left: 0.3em;
		margin-right: 0.6em;
	}
</style>