<?php
/**
 * Context:
 *
 * @var DateTimeImmutable $current_date
 */
?>
<section id="time-track-shortcode">
	<h2>
		<?php echo $current_date->format( 'Y월 m월 d일' ); ?> 시간 추적
	</h2>
	<table id="time-track-table">
		<colgroup>
			<col class="title">
			<col class="project">
			<col class="begin-end">
			<col class="estimated-evaluated">
			<col class="timer">
		</colgroup>
		<thead>
		<tr>
			<th>제목</th>
			<th>프로젝트</th>
			<th>시작<br>종료</th>
			<th>예상<br>실제</th>
			<th>제어</th>
		</tr>
		</thead>
		<tbody id="no-time-tracks" style="display: none;">
		<tr>
			<td colspan="5">추적건이 아직 없습니다.</td>
		</tr>
		</tbody>
		<tbody id="load-time-tracks">
		<tr>
			<td colspan="5">
				<img src="<?php echo esc_url( site_url( WPINC . '/images/spinner.gif' ) ); ?>" alt="spinner image">
				조회 중 ...
			</td>
		</tr>
		</tbody>
		<tbody id="time-track-items">
		</tbody>
	</table>
	<button id="add-new-track">새 시간 추적</button>
</section>

<script type="text/template" id="tmpl-time-tracking-row">
	<?php include __DIR__ . '/time-track-shortcode-row.ejs'; ?>
</script>

<?php include __DIR__ . '/time-track-shortcode-dialog.php'; ?>
