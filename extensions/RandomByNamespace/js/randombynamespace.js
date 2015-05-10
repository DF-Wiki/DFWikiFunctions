jQuery(function($){
    var toggle = function (state) {
        $('.randombynamespace-list ul li span[data-id]').each(function(i, e) {
            if (state)
                $(e).text('(' + $(e).attr('data-id') + ')');
            else
                $(e).text('');
        });
    };
    $('#randombynamespace-toggle-ids').change(function(){
        toggle($(this).prop('checked'));
    });
    toggle($('#randombynamespace-toggle-ids').prop('checked'));
});
