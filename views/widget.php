<h3 class="widget-title"><?php echo $feed_title; ?></h3>

<ul>
    <?php
    $current_category = "";
    foreach($posts->news as $news_post):

        if($news_post->category != $current_category):
            $first = ($current_category == "") ? true : false;
            $current_category = trim($news_post->category);
    ?>
    <li class="ucdd-category-title<?php echo ($first) ? ' first' : ''; ?>"><?php echo $current_category; ?></li>
    <?php
        endif;
    ?>

    <li><a href="<?php echo $news_post->url; ?>"><?php echo $news_post->title; ?></a></li>
    <?php
    endforeach;
    ?>
</ul>
