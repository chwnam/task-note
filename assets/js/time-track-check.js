/* global jQuery */
(function ($) {
    var timeTrackPanel = $('#time-track-panel'),
        projectTagSelect = $('#project-tag-select'),
        newTrackingTitle = $('#new-tracking-title'),
        trackingTimer = $('#tracking-timer'),
        button = $('#time-track-panel-button');

    var projectTag = $('#project-tag'),
        projectStatusDesc = $('#project-status-desc'),
        editProjectTagArea = $('#edit-project-tag-area'),
        trackingTitle = $('#tracking-title'),
        editTrackingTitleArea = $('#edit-tracking-title-area');

    var tnTimeTrackCheck = window.hasOwnProperty('tnTimeTrackCheck') ? window.tnTimeTrackCheck : {
        ajaxUrl: '',
        nonce: '',
    };

    timeTrackPanel.on('getCurrentTimeTracking', function () {

    }).on('updateCurrentTimeTracking', function (e, params) {
        var data = $.extend({
            nonce: tnTimeTrackCheck.nonce,
            action: 'update_current_time_tracking',
        }, params.data || {});

        params = $.extend({
            method: 'post',
            data: data,
            success: function (r) {
                console.log(r);
            },
            error: function (jqXhr) {
            },
            complete: function (jqXhr) {
            }
        }, params);

        $.ajax(tnTimeTrackCheck.ajaxUrl, params);
    });

    new function () {
        projectTag.on('click', function (e) {
            if (button.data('status') === 'initial') {
                return false;
            }
            projectTagSelect.val(projectTag.data('slug'));
            projectTag.hide();
            projectStatusDesc.hide();
            editProjectTagArea.show();
            e.preventDefault();
        });

        $('#project-tag-apply').on('click', function (e) {
            projectTag
                .data('slug', projectTagSelect.val())
                .text(projectTagSelect.find('option:selected').text());
            projectTag.show();
            projectStatusDesc.show();
            editProjectTagArea.hide();
            e.preventDefault();
        });

        $('#project-tag-cancel').on('click', function (e) {
            projectTag.show();
            projectStatusDesc.show();
            editProjectTagArea.hide();
            e.preventDefault();
        });
    };

    new function () {
        trackingTitle.on('click', function (e) {
            if (button.data('status') === 'initial') {
                return false;
            }
            newTrackingTitle.val(trackingTitle.text());
            trackingTitle.hide();
            editTrackingTitleArea.show();
            newTrackingTitle.select();
            e.preventDefault();
        });

        $('#tracking-title-apply').on('click', function (e) {
            trackingTitle.text(newTrackingTitle.val());
            trackingTitle.show();
            editTrackingTitleArea.hide();
            e.preventDefault();
        });

        $('#tracking-title-cancel').on('click', function (e) {
            trackingTitle.show();
            editTrackingTitleArea.hide();
            e.preventDefault();
        });
    };

    new function () {
        var tracker = false,
            buttonTimer = false,
            buttonSpan = button.find('span'),
            durationStore,
            timespan = 0;

        function formatTrackerTime(value) {
            var hours, minutes, seconds;

            hours = Math.floor(value / 3600);
            value = value % 3600;

            minutes = Math.floor(value / 60);
            seconds = value % 60;

            hours = hours > 9 ? hours.toString() : '0' + hours.toString();
            minutes = minutes > 9 ? minutes.toString() : '0' + minutes.toString();
            seconds = seconds > 9 ? seconds.toString() : '0' + seconds.toString();

            trackingTimer.text(hours + ':' + minutes + ':' + seconds);
        }

        function startTracker() {
            buttonSpan.removeClass('dashicons-controls-play').addClass('dashicons-clock');
            button.data('status', 'tracking');

            timeTrackPanel.triggerHandler('updateCurrentTimeTracking', {});

            tracker = setInterval(function () {
                ++timespan;
                formatTrackerTime(timespan);
            }, 1000);
        }

        function stopTracker() {
            buttonSpan.removeClass('dashicons-clock').addClass('dashicons-yes');
            button.data('status', 'completed');
            if (tracker) {
                clearTimeout(tracker);
                tracker = false;
            }
        }

        function resetTracker() {
            buttonSpan.removeClass('dashicons-yes').addClass('dashicons-controls-play');
            button.data('status', 'initial');

            projectTag.data('slug', '').text(projectTag.data('untagged'));
            projectTag.show();
            projectStatusDesc.show();
            editProjectTagArea.hide();

            trackingTitle.text(trackingTitle.data('untitled'));
            trackingTitle.show();
            editTrackingTitleArea.hide();

            timespan = 0;
            formatTrackerTime(timespan);
        }

        button.on('mousedown', function () {
            if (!buttonTimer) {
                buttonTimer = setTimeout(function () {
                    switch (button.data('status')) {
                        case 'initial':
                            startTracker();
                            break;
                        case 'tracking':
                            stopTracker();
                            break;
                        case 'completed':
                            resetTracker();
                            break;
                    }
                    durationStore = button[0].style.transitionDuration;
                    button[0].style.transitionDuration = '0s';
                    buttonTimer = false;
                }, 1000);
            }
        }).on('mouseup', function () {
            setTimeout(function () {
                button[0].style.transitionDuration = durationStore;
            }, 10);
            clearTimeout(buttonTimer);
            buttonTimer = false;
        });
    };
})(jQuery);