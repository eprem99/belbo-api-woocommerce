jQuery(document).ready(function ($) {
    $('.btn-calendar').click(function (e) { 
        e.preventDefault();
        const store = $('.store-title').attr('data-store-id');
        const lng = $('.store-title').attr('data-lng');
        const products = new Array();
        $('.matched-products-list .matched-product-item').each(function (index, element) {
              if($(this).hasClass('active')){
                products.push($(this).attr('data-product-id'));
              }
        });
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/wp-admin/admin-ajax.php',
            data: {action: 'belbo_add_dates', store: store, products: products, lang: lng},
            beforeSend: function() {
                $("#bybalbo").addClass('loader');
            },
            success: function(data){
                if(data.success == true){
                    dates = [];
                    $.each(data.data.days, function(key,value) {
                        const timestamp = value;
                        const year = timestamp.substr(0,4);
                        const month = timestamp.substr(4,2);
                        const day = timestamp.substr(6,2);
                        dates.push(year+"-"+month+"-"+day);
                        
                    }); 
                    calendarTimes(data.store, data.product, dates[0]);
                    calendarAdd(dates);
                }else{
                    alert("You cannot book on this date");
                }

            },
            error: function(){
                alert("Something went wrong");
            },
            complete: function() {
               $('#bybalbo').removeClass('loader');
            }
        });
    });

    function calendarAdd(dates){
        var $btn = $('body').pignoseCalendar({
            select: onSelectHandler,
            apply: onApplyHandler,
            modal: true, 
            lang: 'de',
            week: 6,
            buttons: true,
            selectOver: true,
            reverse: false,
            enabledDates: dates,
            date: dates[0],   
        });

        $('body').trigger('click');
    }

    function onSelectHandler(date, context) {

        if (date[0] !== null) {
            const dats = date[0].format('YYYY-MM-DD');
            calendarTime(dats);
        }
    }
    function onApplyHandler(date, context) {
        const dats = date[0].format('YYYY-MM-DD');
        const time = [];
        $("#calendar-time .time_list.active").each(function (index, element) {
            time.push($(this).text())
        });
        if (date[0] !== null && time[0] != '' || date[0] !== null && time[0] != null) {
            add_to_cart(dats, time[0]);
        }else{
            alert('Please select Time');
        }
    }

    function calendarTimes(store, product, time){
        const lng = $('.store-title').attr('data-lng');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/wp-admin/admin-ajax.php',
            data: {action: 'belbo_add_time', store: store, product: product, data: time, lang: lng},
            beforeSend: function() {
                $("#calendar-time").addClass('loader');
                $("#calendar-time").html('');
            },
            success: function(data){
                if(data.success == true){
                    
                    $.each(data.data.times, function(key,value) {
                        $.each(value, function(i, e) {
                            console.log(e.startDate)
                            $("#calendar-time").each(function (index, element) {
                                $(this).append("<div class='time_list'>"+e.startDate+"</div>")
                            });
                        })                       
                    });
                    $("#calendar-time .time_list").each(function (index, element) {
                        console.log(index)
                        $(element).click(function(){
                            $("#calendar-time .time_list,active").removeClass("active")
                            $(element).addClass("active")
                        })
                    });
                }else{
                    alert("You cannot book on this date");
                }

            },
            error: function(){
                alert("Something went wrong");
            },
            complete: function() {
                $('#calendar-time').removeClass('loader');
            }
        });
    }


    function calendarTime(date){
        const store = $('.store-title').attr('data-store-id');
        const lng = $('.store-title').attr('data-lng');
        const product = new Array();
        $('.matched-products-list .matched-product-item').each(function (index, element) {
              if($(this).hasClass('active')){
                product.push($(this).attr('data-product-id'));
              }
        });
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/wp-admin/admin-ajax.php',
            data: {action: 'belbo_add_time', store: store, products: product, data: date, lang: lng},
            beforeSend: function() {
                $("#calendar-time").addClass('loader');
                $("#calendar-time").html('');
            },
            success: function(data){
                if(data.success == true){
                    $.each(data.data.times, function(key,value) {
                        $.each(value, function(i, e) {
                            console.log(e.startDate)
                            $("#calendar-time").each(function (index, element) {
                                $(this).append("<div class='time_list'>"+e.startDate+"</div>")
                            });
                        })                       
                    });

                    $("#calendar-time .time_list").each(function (index, element) {
                        console.log(index)
                        $(element).click(function(){
                            $("#calendar-time .time_list,active").removeClass("active")
                            $(element).addClass("active")
                        })
                    });
                }else{
                    alert("You cannot book on this date");
                }

            },
            error: function(){
                alert("Something went wrong");
            },
            complete: function() {
                $('#calendar-time').removeClass('loader');
            }
        });
    }

    function add_to_cart(date, time){
        const store = $('.store-title').attr('data-store-id');
        const lng = $('.store-title').attr('data-lng');
        const products = new Array();
        $('.matched-products-list .matched-product-item').each(function (index, element) {
              if($(this).hasClass('active')){
                products.push($(this).attr('data-product-id'));
              }
        });
            if(date != "undefined" && time != "undefined" && store != "undefined" && products != "undefined"){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/wp-admin/admin-ajax.php',
                data: {action: 'belbo_add_cart', date: date, time: time, store: store, products: products, lang: lng},
                beforeSend: function() {
                    $(".pignose-calendar-button-apply").addClass('loader');
                },
                success: function(data){
                    if(data.success == true){
                        if(data.data.result == 'OK'){
                            var bb = data.data.url;
                            window.location.href = bb;
                        }else{
                            alert("You cannot book on this date");
                        }
                    }else{
                            alert("You cannot book on this date");
                    }

                },
                error: function(){
                    alert("Something went wrong");
                },
                complete: function() {
                    $(".pignose-calendar-button-apply").removeClass('loader');
                }
            });
        }else{
            alert('Please select services!')
        }
    }
});