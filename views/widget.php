<h3 class="widget-title"><?php echo $feed_title; ?></h3>

<ul>
    <?php
    $current_category = "";
    foreach($posts as $news_post):

        if($news_post->category != $current_category):
            $first = ($current_category == "") ? true : false;
            $current_category = trim($news_post->category);
    ?>
    <li class="ucdd-category-title<?php echo ($first) ? ' first' : ''; ?>"><?php echo $current_category; ?></li>
    <?php
        endif;
    ?>

    <li>
        <a href="<?php echo $news_post->url; ?>" target="_blank"><?php echo htmlentities(strip_tags($news_post->title)); ?></a><br/>
        <?php
            $excerpt = htmlentities(strip_tags($news_post->content));
            $excerpt_length = strlen($excerpt);
            $last_word = ($excerpt_length > 200) ? strpos($excerpt, ' ', 150) : 0;
            echo ($excerpt_length > 200) ? substr($excerpt, 0, $last_word) . '... <a href="' . $news_post->url . '" target="_blank">[Read More]</a>' : $excerpt;
        ?>

    </li>
    <?php
    endforeach;
    ?>
</ul>
