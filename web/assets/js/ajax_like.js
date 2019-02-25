$(document).on('click', 'button.ajax-like', function(e){
    var $link = $(this);
    $.ajax({
        url: $link.attr('data-link'),
        type: "GET",
        async: true,
        success: function (data) {
            $("span#likes-count-"+$link.attr('data-id')).html(data == 0 ? '' : data);
        }
    });
});