<?php
/*
Plugin Name: Sogo Calendar Widget
Plugin URI: http://sogo.co.il/plugins/sogo-calendar-widget
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z5H7VVZLSPYUE
Description: A Monthly Calendar widget, highly configurable, enable you to select a post type and field for the date to be used
Version: 2.1
Author: Oren Havshush
Author URI: http://sogo.co.il
Author Email: oren@sogo.co.il
License:

  Copyright 2011 Sogo Recent Entries (support@sogo.co.il)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class sogo_calendar_widget extends WP_Widget
{
    private $plugin_name, $plugin_slug, $loc, $post_type, $text_before_details, $heading, $meta_filed,$field_format ;
    /*--------------------------------------------------*/
    /* Constructor
    /*--------------------------------------------------*/

    /**
     * The widget constructor. Specifies the classname and description, instantiates
     * the widget, loads localization files, and includes necessary scripts and
     * styles.
     */
    function __construct()
    {

        // Define constants used throughout the plugin
        $this->init_plugin_constants();
        load_textdomain($this->loc, dirname(__FILE__) . '/lang/' . get_locale() . '.mo');
        $widget_opts = array(
            'classname' => $this->plugin_slug,
            'description' => __("This is a simple plugin, that allows you to choose one of your blog's authors (or a random pick),and display its latest posts (as many posts as you'dd like).", $this->loc)
        );
        parent::__construct(
            $this->plugin_slug, // Base ID
            __($this->plugin_name, $this->loc), // Name
            array(__('Sogo Calendar Widget', $this->loc), 'classname' => $this->plugin_slug,) // Args


        );

        add_action('wp_ajax_nopriv_cal_next_month', array($this, 'cal_next_month'));
        add_action('wp_ajax_cal_next_month', array($this, 'cal_next_month'));

        // Load JavaScript and stylesheets
        $this->register_scripts_and_styles(); //no need for that

    } // end constructor

    /*--------------------------------------------------*/
    /* API Functions
    /*--------------------------------------------------*/

    /**
     * Outputs the content of the widget.
     *
     * @args            The array of form elements
     * @instance
     */
    function widget($args, $instance)
    {

        extract($args, EXTR_SKIP);


        echo $before_widget;

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
        $days = empty($instance['days']) ? 'Sun, Mon, Tue, Wed, Tur, Fri, Sat' : $instance['days'];
        $post_type = empty($instance['post_type']) ? '' : $instance['post_type'];
        $text_before_details = empty($instance['text_before_details']) ? '' : $instance['text_before_details'];
        $cf = empty($instance['cf']) ? 'post_date' : $instance['cf'];
        $field_format = empty($instance['field_format']) ? 'timestamp' : $instance['field_format'];
        $days = explode(',', $days);

        $translation_array = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'heading' => $days,
            'post_type' => $post_type,
            'meta_field' => $cf,
            'field_format' => $field_format,
            'text_before_details' => $text_before_details
        );
        wp_localize_script($this->plugin_slug, 'cal', $translation_array);

        $this->post_type = $post_type;
        $this->heading = $days;
        $this->meta_filed = $cf;
        $this->field_format = $field_format;
        $this->text_before_details = $text_before_details;
        $this->get_monthly_posts(time());
        if ($title) echo $before_title . $title . $after_title;
        echo $this->draw_calendar(date('n'), date('Y'));

        echo $after_widget;

    } // end widget


    private function get_rand_author()
    {
        $users = get_users('blog_id=1&orderby=nicename&role=author');
        if ($users) {
            $rand_key = array_rand($users);
            return $users[$rand_key];
        }
        return false;
    }

    /**
     * Processes the widget's options to be saved.
     *
     * @new_instance    The previous instance of values before the update.
     * @old_instance    The new instance of values to be generated via the update.
     */
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags(stripslashes($new_instance['title']));
        $instance['days'] = strip_tags(stripslashes($new_instance['days']));
        $instance['post_type'] = strip_tags(stripslashes($new_instance['post_type']));
        $instance['cf'] = strip_tags(stripslashes($new_instance['cf']));
        $instance['field_format'] = strip_tags(stripslashes($new_instance['field_format']));
        $instance['text_before_details'] = strip_tags(stripslashes($new_instance['text_before_details']));

        return $instance;

    } // end widget

    /**
     * Generates the administration form for the widget.
     *
     * @instance    The array of keys and values for the widget.
     */
    function form($instance)
    {

        $instance = wp_parse_args(
            (array)$instance,
            array(
                'title' => '',
                'days' => '',
                'post_type' => '',
                'text_before_details' => '',
                'cf' => '',
                'field_format' => '',


            )
        );

        $title = strip_tags(stripslashes($instance['title']));
        $days = strip_tags(stripslashes($instance['days']));
        $post_type = strip_tags(stripslashes($instance['post_type']));
        $text_before_details = strip_tags(stripslashes($instance['text_before_details']));
        $cf = strip_tags(stripslashes($instance['cf']));
        $field_format = $instance['field_format'];


        // Display the admin form
        include(dirname(__FILE__) . '/views/admin.php');

    } // end form

    /*--------------------------------------------------*/
    /* Private Functions
    /*--------------------------------------------------*/

    /**
     * Initializes constants used for convenience throughout
     * the plugin.
     */
    private function init_plugin_constants()
    {
        $this->loc = 'oh';
        $this->plugin_name = 'Sogo Calendar Widget';
        $t = __('Sogo Calendar Widget', $this->loc); // we do that so the poedit will scan this
        $this->plugin_slug = 'sogo_calendar_widget';


    } // end init_plugin_constants

    /**
     * Registers and enqueues stylesheets for the administration panel and the
     * public facing site.
     */
    private function register_scripts_and_styles()
    {
        if (is_admin()) {
            $this->load_file($this->plugin_name, 'css/admin.css');
        } else {
            $url = plugins_url(__FILE__);
            wp_enqueue_script($this->plugin_slug, plugins_url('js/scripts.js', __FILE__), array('jquery'), '1.0', true);
            wp_enqueue_style($this->plugin_slug, plugins_url('css/cal.css', __FILE__), '1.0');
        }

    } // end register_scripts_and_styles

    /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name    The    ID to register with WordPress
     * @file_path        The path to the actual file
     * @is_script        Optional argument for if the incoming file_path is a JavaScript source file.
     */
    private function load_file($name, $file_path, $is_script = false)
    {
        $url = plugins_url($file_path, __FILE__);
        $file = dirname(__FILE__) . '/' . $file_path;
        if (file_exists($file)) {
            if ($is_script) {
                wp_register_script($name, $url);
                wp_enqueue_script($name);
            } else {
                wp_register_style($name, $url);
                wp_enqueue_style($name);
            } // end if
        } // end if

    } // end load_file

    function get_monthly_posts($date)
    {
        $field_format = (isset($_POST['field_format'])) ? $_POST['field_format'] : $this->field_format;
        $month_start = strtotime('first day of this month', $date);
        $month_end = strtotime('last day of this month', $date);

        if($field_format !== 'timestamp'){
            $month_start = date($field_format,$month_start);
            $month_end = date($field_format,$month_end);
        }
        $post_type = (isset($_POST['post_type'])) ? $_POST['post_type'] : $this->post_type;
        $meta_filed = (isset($_POST['meta_field'])) ? $_POST['meta_field'] : $this->meta_filed;
//var_dump(array($month_start, $month_end));
        $r = get_posts(apply_filters('widget_posts_args',
            array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => $meta_filed,
                        'value' => array($month_start, $month_end),
                        'compare' => 'between'
                    )
                )
            )
        ));


        $events = array();
        if ($meta_filed != '') {
            if (!empty($r)) {
                foreach ($r as $p) {
                    $d = get_post_meta($p->ID, $meta_filed, true);
                    if ($d) {
                        if($field_format =='timestamp'){
                            $day = date('d',$d);
                        }else{
                            $df=DateTime::createFromFormat($field_format, $d);
                            if($df){
                                $day = intval(date_format($df,'d'));
                            }

                        }
                        //$d = $field_format =='timestamp'? $d:  DateTime::createFromFormat($field_format, $d);
                        $events[$day][] = $p;
                    }

                }
            }
        }
       // var_dump($events);
        return $events;


    }

    function draw_calendar($month, $year)
    {

        $events = $this->get_monthly_posts(strtotime("1-$month-$year"));
//         var_dump($events);
        $event_details = '';
        $text_before_details = isset($_POST['text_before_details']) ? $_POST['text_before_details'] : $this->text_before_details;
        $today = date('d-n-Y');

        //  print_r($this->events);
        $next = is_rtl() ? '<' : '>';
        $prev = is_rtl() ? '>' : '<';

        $calendar = "<div class='calendar-wrapper'>";
        $calendar .= "<h2>" . date_i18n("M Y", strtotime("1-$month-$year")) . "</h2>";
        $calendar .= "<div class='month-nav'>
            <a href='#' data-month='1-$month-$year' id='cal-next'> $next </a>
            <a data-month='1-$month-$year' href='#' id='cal-prev'> $prev </a>
            </div>";
        /* draw table */
        $calendar .= '<table cellpadding="0" cellspacing="0" class="calendar">';

        /* table headings */
        if ((empty($this->heading))) {
            if (isset($_POST['heading'])) {
                $headings = $_POST['heading'];
            } else {
                $headings = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
            }
        } else {
            $headings = $this->heading;
        }
//        $headings = (empty($this->heading))? array('Sun','Mon','Tue','Wed','Thu','Fri','Sat'): $this->heading;

        $calendar .= '<tr class="calendar-row"><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';

        /* days and weeks vars now ... */
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter = 0;
        $dates_array = array();

        /* row for week one */
        $calendar .= '<tr class="calendar-row">';

        /* print "blank" days until the first of the current week */
        for ($x = 0; $x < $running_day; $x++):
            $calendar .= '<td class="calendar-day-np"> </td>';
            $days_in_this_week++;
        endfor;
        /* keep going with days.... */

        for ($list_day = 1; $list_day <= $days_in_month; $list_day++):

            $today_class = ($today == "$list_day-$month-$year") ? '  today ' : '';
            $has_events = '';
            $data_item = '';

            if (isset($events[$list_day])) {
                $has_events = " has_events";
                $data_item = " data-cal-day='$list_day'";
                $event_details .= "<div id='cal-day-" . $list_day . "' class='event-details'>";
                foreach ($events[$list_day] as $event_day) {
                    $event_details .= "<div><h3><a href='" . esc_url(get_permalink($event_day->ID)) . "'>" . $event_day->post_title . "</a></h3></div>";
                }
                $event_details .= '</div>';

            }

            $calendar .= '<td ' . $data_item . ' class="calendar-day' . $today_class . ' ' . $has_events . '">';
            /* add in the day number */

            //    $calendar.= '<div class="day-number ">'.$list_day.'</div>';
            $calendar .= '<div class="day-number ">' . $list_day . '</div>';


            /** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
            //        $calendar.= str_repeat('<p> </p>',2);

            $calendar .= '</td>';
            if ($running_day == 6):
                $calendar .= '</tr>';
                if (($day_counter + 1) != $days_in_month):
                    $calendar .= '<tr class="calendar-row">';
                endif;
                $running_day = -1;
                $days_in_this_week = 0;
            endif;
            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        endfor;

        /* finish the rest of the days in the week */
        if ($days_in_this_week < 8):
            for ($x = 1; $x <= (8 - $days_in_this_week); $x++):
                $calendar .= '<td class="calendar-day-np"> </td>';
            endfor;
        endif;

        /* final row */
        $calendar .= '</tr>';

        /* end the table */
        $calendar .= '</table>';


        $calendar .= '<div class="footer-calendar"><span>' . $text_before_details . '</span>' . $event_details . '</div>';
        $calendar .= '</div>';
        /* all done, return result */
        return $calendar;
    }


    public function cal_next_month()
    {
        if (!isset($_POST['month'])) {
            return false;
        }
        $date = $_POST['month'];
        if (isset($_POST['prev'])) {
            $current = date(strtotime("$date -1 month"));
        } else {
            $current = date(strtotime("$date +1 month"));
        }

        echo $this->draw_calendar(date('n', $current), date('Y', $current));
        exit();
    }


} // end class
add_action('widgets_init', create_function('', 'register_widget("sogo_calendar_widget");'));
?>