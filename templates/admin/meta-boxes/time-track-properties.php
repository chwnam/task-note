<?php
/**
 * Context:
 *
 * @var int    $year
 * @var int    $month
 * @var int    $day
 * @var string $dow
 *
 * @var int    $begin_hour
 * @var int    $begin_minute
 *
 * @var int    $end_hour
 * @var int    $end_minute
 *
 * @var int    $timespan
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
                        min="2019"
                        value="<?php echo esc_attr( $year ); ?>"
                ><label for="time_track_date_year">년</label></span>

            <span><input
                        id="time_track_date_month"
                        name="time_track_date[month]"
                        type="number"
                        min="1"
                        max="12"
                        value="<?php echo esc_attr( $month ); ?>"
                ><label for="time_track_date_month">월</label></span>

            <span><input
                        id="time_track_date_day"
                        name="time_track_date[day]"
                        type="number"
                        min="1"
                        max="31"
                        value="<?php echo esc_attr( $day ); ?>"
                ><label for="time_track_date_day">일</label></span>

            <label><span id="time_track_date_day_of_week"><?php echo esc_html( $dow ); ?></span>요일</label>
        </td>
    </tr>

    <tr>
        <th scope="row">시작 시간</th>
        <td>
            <span><input
                        id="time_track_begin_hour"
                        name="time_track_begin[hour]"
                        type="number"
                        min="0"
                        max="23"
                        value="<?php echo esc_attr( $begin_hour ); ?>"
                ><label for="time_track_begin_hour">시</label></span>

            <span><input
                        id="tn_time_track_begin_minute"
                        name="time_track_begin[minute]"
                        type="number"
                        min="0"
                        max="59"
                        value="<?php echo esc_attr( $begin_minute ); ?>"
                ><label for="tn_time_track_begin_minute">분</label></span>
        </td>
    </tr>

    <tr>
        <th scope="row">종료 시간</th>
        <td>
            <span><input
                        id="time_track_end_hour"
                        name="time_track_end[hour]"
                        type="number"
                        min="0"
                        max="23"
                        value="<?php echo esc_attr( $end_hour ); ?>"
                ><label for="time_track_end_hour">시</label></span>

            <span><input
                        id="time_track_end_minute"
                        name="time_track_end[minute]"
                        type="number"
                        min="0"
                        max="59"
                        value="<?php echo esc_attr( $end_minute ); ?>"
                ><label for="time_track_end_minute">분</label></span>
        </td>
    </tr>

    <tr>
        <th scope="row">작업 시간</th>
        <td>
            <input
                    id="timespan"
                    type="number"
                    min="0"
                    value="<?php echo esc_attr( intval( $timespan / 60 ) ); ?>"
            ><label for="timespan">분</label>
        </td>
    </tr>
</table>

<?php wp_nonce_field( 'time_track_properties', 'time_track_nonce', false ); ?>
