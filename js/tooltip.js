jQuery(document).ready(function($) {
    $('.anniversary-tooltip').hover(function() {
        $(this).attr('data-title', $(this).attr('title')).removeAttr('title');
    }, function() {
        $(this).attr('title', $(this).attr('data-title'));
    });
});
