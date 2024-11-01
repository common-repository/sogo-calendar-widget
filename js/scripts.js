(function($) {

// as the page loads, call these scripts
    $(document).ready(function($) {
        function cal_get_loader(){
            $('.calendar-wrapper').replaceWith('<div class="calendar-wrapper"><div class="bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div></div>');
        }

        if( $('#cal-next').length > 0 ){
            set_cal_object();
        }

        function set_cal_object(){

            $('.event-details').hide();
            $('.calendar-day.has_events').click(function(){

                $('.event-details').hide();
                var item = $(this).data('cal-day');
                $('#cal-day-'+item).show();
            })
            $('#cal-next').click(function(e){
                e.preventDefault();
                cal_get_loader();
                jQuery.post(
                    cal.ajaxurl,
                    {
                        'action': 'cal_next_month',
                        'month':   $(this).data('month'),
                        'heading':    cal.heading,
                        'post_type': cal.post_type,
                        'meta_field': cal.meta_field,
                        'field_format': cal.field_format,
                        'text_before_details': cal.text_before_details


                    },
                    function(response){
                        $('.calendar-wrapper').replaceWith(response);
                        set_cal_object();
                    }
                );
            });

            $('#cal-prev').click(function(e){
                e.preventDefault();
                cal_get_loader();
                jQuery.post(
                    cal.ajaxurl,
                    {
                        'action': 'cal_next_month',
                        'month':   $(this).data('month'),
                        'prev':   true,
                        'heading':    cal.heading,
                        'post_type': cal.post_type,
                        'meta_field': cal.meta_field,
                        'field_format': cal.field_format,
                        'text_before_details': cal.text_before_details

                    },
                    function(response){
                        $('.calendar-wrapper').replaceWith(response);
                        set_cal_object();
                    }
                );
            });
        }




    }); /* end of as page load scripts */



})(jQuery);/**
 * Created by Oren on 1/21/14.
 */
