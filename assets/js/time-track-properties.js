/* global jQuery */
(function ($) {
    $('#time_track_date_year, #time_track_date_month, #time_track_date_day').on('change', function () {
        // TODO: 날짜가 바뀌면 요일 변경
    });

    $('#time_track_date_year, #time_track_date_month, #time_track_date_day, #time_track_begin_hour, #tn_time_track_begin_minute, #time_track_end_hour, #time_track_end_minute').on('change', function () {
        // TODO: 시작, 종료 시간이 변경되면 작업 시간 변경
    });

    $('#timespan').on('change', function () {
        // TODO: 작업 시간의 변경시 종료 시간 변경
    });

    $('form#post').on('submit', function () {
        // TODO: 폼 요소의 정확성 확인 후 폽 제출
    });
})(jQuery);