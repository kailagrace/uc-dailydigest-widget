<p>
    <label for="<?php echo $this->get_field_id( 'feed_url' ); ?>"><?php _e("Select Feed"); ?>:</label><br/>
    <select class="widefat" id="<?php echo $this->get_field_id( 'feed_url' ); ?>" name="<?php echo $this->get_field_name( 'feed_url' ); ?>">

        <?php
        foreach($feed_urls as $name => $url):
        ?>
        <option value="<?php echo $url; ?>" <?php if($feed_url == $url) echo 'selected="selected"'; ?>>
            <?php echo $name; ?>
        </option>
        <?php
        endforeach;
        ?>

    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e("Number of posts to show"); ?>:</label>
    <input type="text" size="3" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" id="<?php echo $this->get_field_id( 'num_posts' ); ?>" value="<?php echo $num_posts; ?>"/>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e("Exclude Categories"); ?>:</label><br/>
    <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'exclude_categories' ); ?>" id="<?php echo $this->get_field_id( 'exclude_categories' ); ?>" value="<?php echo $exclude_categories; ?>"/><br/>
    <span class="uconn-daily-digest-widget-small">Semi-colon separated list of category names.</span>
</p>
