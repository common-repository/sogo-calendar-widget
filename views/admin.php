<div class="wrapper">

    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:',$this->loc); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

    <p><label for="<?php echo $this->get_field_id( 'days' ); ?>"><?php _e( 'Days List:',$this->loc ); ?></label>
        <textarea class="widefat" id="<?php echo $this->get_field_id( 'days' ); ?>"
                  name="<?php echo $this->get_field_name( 'days' ); ?>" ><?php echo $days; ?></textarea>

    <p><label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:',$this->loc ); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" >
            <?php   $args = array(
                'public'   => true
            );

            $output = 'object'; // names or objects, note names is the default
            $post_types = get_post_types( $args, $output );

            foreach ( $post_types  as $obj ) {
                echo '<option value="'.$obj->name.'" '. selected($post_type,$obj->name, false ) .'>' . __($obj->name,$this->loc) . '</option>';
            }
            ?>
        </select> </p>

    <p><label for="<?php echo $this->get_field_id( 'text_before_details' ); ?>"><?php _e( 'Text before event details:',$this->loc); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'text_before_details' ); ?>"
               name="<?php echo $this->get_field_name( 'text_before_details' ); ?>"
               type="text" value="<?php echo $text_before_details; ?>" /></p>
    <p><label for="<?php echo $this->get_field_id( 'cf' ); ?>"><?php _e( 'Custom Field:',$this->loc); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'cf' ); ?>" name="<?php echo $this->get_field_name( 'cf' ); ?>"
               type="text" value="<?php echo $cf; ?>" />
    </p>
    <p><label for="<?php echo $this->get_field_id( 'field_format' ); ?>"><?php _e( 'Field Format:',$this->loc); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'field_format' ); ?>" name="<?php echo $this->get_field_name( 'field_format' ); ?>"
               type="text" value="<?php echo $field_format; ?>" />
    </p>

</div><!-- /wrapper -->