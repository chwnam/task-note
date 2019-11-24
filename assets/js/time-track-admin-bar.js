jQuery(function ($) {
    $('#wp-admin-bar-time-track').on('click', function (e) {
        var menu = $(e.currentTarget)
            , panel = $('#time-track-panel')
        ;

        e.preventDefault();
        menu.toggleClass('active');
        panel.toggleClass('active');
    });
});