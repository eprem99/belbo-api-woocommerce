jQuery(document).ready(function ($) {
    $('#belbo_run_stores').click(function (e) { 
        $('#main').addClass('loader');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'admin-ajax.php',
            data: {action: 'belbo_conect_locations'},
            success: function(data){

            },
            error: function(){

            },
            complete: function() {
                $('#main').removeClass('loader');
            }
        });
    });

    $('#belbo_run_services').click(function (e) { 
        $('#main').addClass('loader');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'admin-ajax.php',
            data: {action: 'belbo_conect_products'},
            success: function(data){

            },
            error: function(){

            },
            complete: function() {
                $('#main').removeClass('loader');
            }
        });
        
    });
});