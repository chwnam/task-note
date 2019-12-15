/* global jQuery */
(function ($) {
    var dialog = $('#time-track-dialog')
        , table = $('#time-track-table')
        , opt = window.hasOwnProperty('timeTrackShortcode') ? window.timeTrackShortcode : {
            ajaxUrl: '/wp-admin/admin-ajax.php',
            action: '',
            nonce: ''
        }
        , template = wp.template('time-tracking-row')
        , tracks = []
        , timerAnchor
        , timerHandle
        , timerValue
    ;

    function redraw() {
        var items = $('#time-track-items');

        tracks.sort(function (a, b) {
            if (a.begin_timestamp === b.begin_timestamp) {
                return 0;
            }
            return a.begin_timestamp > b.begin_timestamp ? 1 : -1;
        });

        if (tracks.length) {
            $('#no-time-tracks').hide();
            items.hide().html('');
            $.each(tracks, function (idx, elem) {
                items.append(template(elem));
            });
            items.show();
        } else {
            $('#no-time-tracks').show();
        }
    }

    function setDialogForm(data) {
        var status = $('#dialog_status', dialog)
            , text = ''
        ;

        data = $.extend({
            nonce: opt.nonce,
            action: 'request_time_track_shortcode_save',
            evaluated: '',
            evaluated_raw: null,
            estimated: null,
            begin_timestamp: null
        }, data);

        $.each(data, function (key, val) {
            $('#' + key, dialog).val(val);
        });

        if (data.hasOwnProperty('status')) {
            var estimated, evaluated;
            if (data.status === 'publish') {
                text = data.evaluated;
                evaluated = parseInt(data.evaluated_raw);
                estimated = parseInt(data.estimated) * 60;
                if (evaluated && estimated) {
                    if (estimated > evaluated) {
                        text = formattedText(evaluated - estimated) + ' 먼저 완료';
                    } else {
                        text = formattedText(evaluated - estimated) + ' 더 걸림'
                    }
                } else {
                    text = '(계획된 시간 없음)';
                }

            } else if (data.status === 'pending') {
                text = '(진행중)';
            } else {
                text = '(계획된 작업)';
            }
        }

        status.text(text);
    }

    function beginTimer() {
        timerAnchor = table.find('a.timer');
        if (timerAnchor.length) {
            timerValue = parseInt(timerAnchor.data('beginTimestamp')) + (parseInt(timerAnchor.data('estimated')) * 60);
            if (timerHandle) {
                clearInterval(timerHandle);
            }
            timerHandle = setInterval(function () {
                var value = Math.floor(Date.now() / 1000) - timerValue;
                if (value > 0) {
                    timerAnchor.addClass('past-deadline');
                } else {
                    timerAnchor.addClass('before-deadline');
                }
                timerAnchor.text(formattedText(value));
            }, 1000);
        }
    }

    function formattedText(value) {
        var hours, minutes, seconds;
        value = Math.abs(value);

        hours = Math.floor(value / 3600);
        value = value % 3600;

        minutes = Math.floor(value / 60);
        seconds = Math.floor(value % 60);

        if (hours === 0) {
            hours = '';
        } else if (hours > 9) {
            hours = hours.toString();
        } else {
            hours = '0' + hours.toString();
        }
        minutes = minutes > 9 ? minutes.toString() : '0' + minutes.toString();
        seconds = seconds > 9 ? seconds.toString() : '0' + seconds.toString();

        return (hours ? hours + '시간 ' : '') + minutes + '분 ' + seconds + '초'
    }

    table.on('mouseenter', '.track-title', function (e) {
        $(e.currentTarget).find('.row-actions').addClass('visible');
    });

    table.on('mouseleave', '.track-title', function (e) {
        $(e.currentTarget).find('.row-actions').removeClass('visible');
    });

    table.on('click', '.edit', function (e) {
        var postId = e.currentTarget.dataset.postId.toString()
            , i
        ;
        e.preventDefault();
        for (i = 0; i < tracks.length; ++i) {
            if (tracks[i].hasOwnProperty('post_id') && tracks[i].post_id.toString() === postId) {
                setDialogForm(tracks[i]);
                dialog.dialog('open');
                break;
            }
        }
    });

    table.on('click', '.delete', function (e) {
        var postId;
        e.preventDefault();
        postId = e.currentTarget.dataset.postId;
        if (confirm('정말 이 항목을 삭제할까요?')) {
            $.ajax(opt.ajaxUrl, {
                method: 'post',
                data: {
                    nonce: opt.nonce,
                    action: 'request_time_track_shortcode_delete',
                    post_id: postId
                },
                success: function (response) {
                    if (response.success) {
                        var tr = $(e.currentTarget).closest('tr');
                        tr.fadeOut(function () {
                            tr.remove();
                        });
                        tracks = tracks.filter(function (item) {
                            return item.hasOwnProperty('post_id') && item.post_id.toString() !== postId;
                        });
                    }
                }
            });
        }
    });

    table.on('click', 'a.timer', function (e) {
        var target = $(e.currentTarget)
            , postId = target.data('postId').toString()
        ;
        e.preventDefault();
        if (confirm('이 작업을 종료하시겠습니까?')) {
            $.ajax(opt.ajaxUrl, {
                method: 'post',
                data: {
                    action: 'request_time_track_shortcode_finish',
                    nonce: opt.nonce,
                    post_id: postId
                },
                success: function (response) {
                    var i;
                    if (response.success) {
                        for (i = 0; i < tracks.length; ++i) {
                            if (tracks[i].hasOwnProperty('post_id') && tracks[i].post_id.toString() === postId) {
                                tracks[i] = response.data;
                                break;
                            }
                        }
                    }
                    setDialogForm({
                        post_id: '',
                        title: '',
                        project_id: '',
                        content: '',
                        begin_hour: '',
                        begin_minute: '',
                        end_hour: '',
                        end_minute: '',
                        estimated: ''
                    });
                    redraw();
                    beginTimer();
                }
            });
        }
    });

    $('#delete-the-begin').on('click', function (e) {
        e.preventDefault();
        $('#begin_hour,#begin_minute', dialog).val('');
    });

    $('#delete-the-end').on('click', function (e) {
        e.preventDefault();
        $('#end_hour,#end_minute', dialog).val('');
    });

    $('#add-new-track').on('click', function (e) {
        var now = new Date()
            , formValue = {}
        ;

        e.preventDefault();

        if ($('#begin_hour', dialog).val().trim().length === 0) {
            formValue.begin_hour = now.getHours();
        }

        if ($('#begin_minute', dialog).val().trim().length === 0) {
            formValue.begin_minute = now.getMinutes();
        }

        setDialogForm(formValue);

        dialog.dialog('open');
    });

    $(document).ready(function () {
        dialog.dialog({
            autoOpen: false,
            modal: true,
            width: '500px',
            height: 'auto',
            buttons: [
                {
                    'text': '저장',
                    'click': function (e) {
                        var ongoing = tracks.filter(function (item) {
                                return item.hasOwnProperty('status') && item.status.toString() === 'pending';
                            })
                            , beginHour = $('#begin_hour', dialog)
                            , beginMinute = $('#begin_minute', dialog)
                            , endHour = $('#end_hour', dialog)
                            , endMinute = $('#end_minute', dialog)
                            , isStarted
                            , isFinished
                            , startVal
                            , finishVal
                        ;
                        e.preventDefault();

                        isStarted = beginHour.val().length && beginMinute.val().length;
                        isFinished = endHour.val().length && endMinute.val().length;

                        if (ongoing.length && ongoing[0].post_id.toString() !== $('#post_id', dialog).val() && isStarted && !isFinished) {
                            alert('현재 진행중인 작업이 있습니다. 시작 시간이 없거나 완전히 종료된 작업만 등록 가능합니다.');
                            return;
                        }

                        if (isFinished) {
                            finishVal = parseInt(endHour.val()) * 60 + parseInt(endMinute.val());
                            startVal = parseInt(beginHour.val()) * 60 + parseInt(beginMinute.val());
                            if (startVal > finishVal) {
                                alert('종료 시각이 시작 시간보다 일찍입니다.');
                                return;
                            }
                        }

                        $.ajax(opt.ajaxUrl, {
                            method: 'post',
                            data: $('#time-track-dialog-form').serialize(),
                            success: function (response) {
                                var postId = $('#time-track-dialog-form > #post_id').val()
                                    , i
                                ;
                                if (response.success) {
                                    for (i = 0; i < tracks.length; ++i) {
                                        if (tracks[i].hasOwnProperty('post_id') && tracks[i].post_id.toString() === postId) {
                                            break;
                                        }
                                    }
                                    if (i < tracks.length) {
                                        tracks[i] = response.data;
                                    } else {
                                        tracks.push(response.data);
                                    }
                                    dialog.dialog('close');
                                    setDialogForm({
                                        post_id: '',
                                        title: '',
                                        project_id: '',
                                        content: '',
                                        begin_hour: '',
                                        begin_minute: '',
                                        end_hour: '',
                                        end_minute: '',
                                        estimated: ''
                                    });
                                    redraw();
                                    beginTimer();
                                }
                            }
                        });
                    },
                },
                {
                    'text': '취소',
                    'click': function () {
                        dialog.dialog('close');
                    },
                },
            ]
        });

        $.ajax(opt.ajaxUrl, {
            method: 'get',
            data: {
                action: 'request_time_track_shortcode_list',
                nonce: opt.nonce
            },
            beforeSend: function () {
                $('#no-time-tracks').hide();
                $('#time-track-items').hide();
                $('#load-time-tracks').show();
            },
            success: function (response) {
                if (response.success) {
                    tracks = response.data;
                    redraw();
                    beginTimer();
                } else {
                    alert(response.data[0].message);
                }
            },
            error: function (jqXhr) {
                alert(jqXhr.responseText);
            },
            complete: function () {
                $('#load-time-tracks').hide();
            }
        });
    });
})(jQuery);
