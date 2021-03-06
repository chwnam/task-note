/* global jQuery */
(function ($) {
    var tnTimeTrackCheck = window.hasOwnProperty('tnTimeTrackCheck') ? window.tnTimeTrackCheck : {
        ajaxUrl: ''
        , nonce: ''
    };

    var tracker = new function () {
        var $this = this
            , panel = $('#time-track-panel')
            , handlers = {}
        ;

        this.init = function () {
            $.ajax(tnTimeTrackCheck.ajaxUrl, {
                method: 'get',
                data: {
                    action: 'time_track_checkpoint',
                    checkpoint: 'get',
                    nonce: tnTimeTrackCheck.nonce
                }
            }).done(function (response) {
                if (response.success && response.data.track_id) {
                    $this.handle('resumed', [response]);
                }
            });
        };

        this.start = function () {
            $.ajax(tnTimeTrackCheck.ajaxUrl, {
                method: 'post',
                data: {
                    action: 'time_track_checkpoint',
                    checkpoint: 'start',
                    nonce: tnTimeTrackCheck.nonce
                }
            }).done(function (response) {
                $this.handle('started', [response]);
            });
        };

        this.stop = function () {
            $.ajax(tnTimeTrackCheck.ajaxUrl, {
                method: 'post',
                data: {
                    action: 'time_track_checkpoint',
                    checkpoint: 'stop',
                    nonce: tnTimeTrackCheck.nonce
                }
            }).done(function (response) {
                $this.handle('stopped', [response]);
            });
        };

        this.update = function () {
            $.ajax(tnTimeTrackCheck.ajaxUrl, {
                method: 'post',
                data: {
                    action: 'time_track_checkpoint',
                    checkpoint: 'update',
                    nonce: tnTimeTrackCheck.nonce,
                    track_title: title.get(),
                    project_slug: project.get()
                }
            }).done(function (response) {
                $this.handle('updated', response);
            });
        };

        this.reset = function () {
            this.handle('reset');
        };

        this.handle = function (name, args) {
            if (handlers.hasOwnProperty(name)) {
                $(handlers[name]).each(function (idx, elem) {
                    elem.apply(null, args);
                });
            }
        };

        this.addHandler = function (name, handler) {
            if (!handlers.hasOwnProperty(name)) {
                handlers[name] = [];
            }
            handlers[name].push(handler);

            return this;
        };

        this.addHandler('transition', function (status) {
            switch (status) {
                case 'initial':
                    $this.reset();
                    break;

                case 'started':
                    $this.start();
                    break;

                case 'stopped':
                    $this.stop();
                    break;
            }
        });

        this.getCurrentStatus = function () {
            return button.getStatus();
        }
    };

    var project = new function () {
        var $this = this
            , text = $('#project-tag')
            , select = $('#project-tag-select')
            , edit = $('#edit-project-tag-area')
        ;

        this.update = function () {
            return this.set(select.val())
        };

        this.get = function () {
            return text.data('slug');
        };

        this.set = function (slug) {
            var opt = select.find('option[value="' + slug + '"]');
            if (opt.length) {
                opt.attr('checked', 'checked');
                text.data('slug', opt.val()).text(opt.text());
            } else {
                select.val('');
                text.data('slug', '').text(text.data('untagged'));
            }
            return this;
        };

        this.showEditForm = function () {
            if ('started' === tracker.getCurrentStatus()) {
                select.val(text.data('slug'));
                text.hide();
                edit.show();
            }
        };

        this.applyEditForm = function () {
            if ('started' === tracker.getCurrentStatus()) {
                this.update();
                text.show();
                edit.hide();
            }
        };

        this.cancelEditForm = function () {
            text.show();
            edit.hide();
        };

        text.on('click', function (e) {
            e.preventDefault();
            $this.showEditForm();
        });

        $('#project-tag-apply').on('click', function (e) {
            e.preventDefault();
            $this.applyEditForm();
            tracker.update();
        });

        $('#project-tag-cancel').on('click', function (e) {
            e.preventDefault();
            $this.cancelEditForm();
        });

        tracker.addHandler('started', function (response) {
            if (response.success) {
                $this.set(response.data.project_slug);
            }
        });

        tracker.addHandler('resumed', function (response) {
            if (response.success && response.data.project_slug) {
                $this.set(response.data.project_slug);
            }
        });

        tracker.addHandler('reset', function (response) {
            $this.set('');
        });
    };

    var title = new function () {
        var $this = this
            , text = $('#tracking-title')
            , input = $('#new-tracking-title')
            , edit = $('#edit-tracking-title-area')
        ;

        this.get = function () {
            return text.text();
        };

        this.set = function (value) {
            if (value.trim().length) {
                text.text(value);
            } else {
                text.text(text.data('untitled'));
            }
            return this;
        };

        this.showEditForm = function () {
            if ('started' === tracker.getCurrentStatus()) {
                text.hide();
                input.val(text.text());
                edit.show();
                input.select();
            }
        };

        this.applyEditForm = function () {
            if ('started' === tracker.getCurrentStatus()) {
                text.text(input.val()).show();
                edit.hide();
            }
        };

        this.cancelEditForm = function () {
            text.show();
            edit.hide();
        };

        text.on('click', function (e) {
            e.preventDefault();
            $this.showEditForm();
        });

        $('#tracking-title-apply').on('click', function (e) {
            e.preventDefault();
            $this.applyEditForm();
            tracker.update();
        });

        $('#tracking-title-cancel').on('click', function (e) {
            e.preventDefault();
            $this.cancelEditForm();
        });

        tracker.addHandler('started', function (response) {
            if (response.success && response.data.track_title) {
                $this.set(response.data.track_title);
            }
        });

        tracker.addHandler('resumed', function (response) {
            if (response.success && response.data.track_title) {
                $this.set(response.data.track_title);
            }
        });

        tracker.addHandler('reset', function (response) {
            $this.set('');
        });
    };

    var timer = new function () {
        var $this = this
            , elem = $('#tracking-timer')
            , begin = 0
            , intervalHandle = null
        ;

        this.setBegin = function (value) {
            begin = parseInt(value);
            return this;
        };

        this.getBegin = function () {
            return begin;
        };

        this.reset = function () {
            elem.text('--:--:--');
            return this;
        };

        this.start = function (begin) {
            this.stop().setBegin(begin);
            intervalHandle = setInterval(function () {
                elem.text($this.getFormattedText());
            }, 1000);
            return this;
        };

        this.stop = function () {
            if (intervalHandle) {
                clearInterval(intervalHandle);
                intervalHandle = null;
            }
            return this;
        };

        this.getFormattedText = function () {
            var hours
                , minutes
                , seconds
                , value = (Date.now() / 1000) - this.getBegin()
            ;

            hours = Math.floor(value / 3600);
            value = value % 3600;

            minutes = Math.floor(value / 60);
            seconds = Math.floor(value % 60);

            hours = hours > 9 ? hours.toString() : '0' + hours.toString();
            minutes = minutes > 9 ? minutes.toString() : '0' + minutes.toString();
            seconds = seconds > 9 ? seconds.toString() : '0' + seconds.toString();

            return hours + ':' + minutes + ':' + seconds;
        };

        tracker.addHandler('transition', function (status) {
            if ('initial' === status) {
                $this.reset();
            }
        });

        tracker.addHandler('started', function (response) {
            if (response.success) {
                $this.start(response.data.track_begin);
            }
        });

        tracker.addHandler('stopped', function (response) {
            if (response.success) {
                $this.stop();
            }
        });

        tracker.addHandler('resumed', function (response) {
            var begin;

            if (response.success && response.data.track_begin) {
                begin = parseInt(response.data.track_begin);
            }

            if (begin) {
                $this.start(begin);
            }
        });

        tracker.addHandler('reset', function (response) {
            $this.reset();
        });
    };

    var button = new function () {
        var $this = this
            , btn = $('#time-track-panel-button')
            , statuses = ['initial', 'started', 'stopped']
            , pushHandler = null
            , duration = null
            , span = btn.find('span')
        ;

        this.setStatus = function (status) {
            var classes = {
                initial: 'dashicons-controls-play',
                started: 'dashicons-clock',
                stopped: 'dashicons-yes'
            };

            if (statuses.indexOf(status) > -1) {
                btn.data('status', status);
                span.removeClass(Object.values(classes).join(' ')).addClass(classes[status]);
            }
            return this;
        };

        this.getStatus = function () {
            return btn.data('status');
        };

        this.transition = function () {
            switch (this.getStatus()) {
                case 'initial':
                    $this.setStatus('started');
                    break;

                case 'started':
                    $this.setStatus('stopped');
                    break;

                case 'stopped':
                    $this.setStatus('initial');
                    break;
            }
            tracker.handle('transition', [this.getStatus()]);
        };

        btn.on('mousedown touchstart', function () {
            if (!pushHandler) {
                pushHandler = setTimeout(function () {
                    $this.transition();
                    duration = btn[0].style.transitionDuration;
                    btn[0].style.transitionDuration = '0s';
                    pushHandler = null;
                }, 1500);
            }
        }).on('mouseup touchend', function (e) {
            setTimeout(function () {
                btn[0].style.transitionDuration = duration;
            }, 10);
            clearTimeout(pushHandler);
            pushHandler = null;
        });

        tracker.addHandler('resumed', function (response) {
            if (response.success && response.data.track_id) {
                $this.setStatus('started');
            }
        });
    };

    $(document).ready(function () {
        tracker.init();
    });
})(jQuery);

// TODO: 에러 케이스에 대해 잘 대비
// TODO: NONCE 값 틀어질 경우에 유저에게 잘 피드백 해 줘야 함.
// TODO: 최소 작업 시간
// TODO: 한 작업 내 세션 추가
