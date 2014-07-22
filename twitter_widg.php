<?php

/*
Plugin Name: Twitter Widget
Description: Embeds Twitter in pages and posts with shortcodes
*/

wp_enqueue_style( 'twitter_style', plugins_url('twitter_style.css', __FILE__));


add_shortcode( 'twitter_embed', 'twitter_embed_content' );

function twitter_embed_content($atts) {

    $atts = twitter_defaults($atts);

    $users = explode(" ", $atts['user']);
    
    $number = $atts['number'];

    $total = $atts['total'];

    $width = $atts['width'];

    $tweets = [];

    foreach($users as $user) {
        // fetch feed using WP
        $rss = fetch_feed('http://twitrss.me/twitter_user_to_rss/?user='.$user);

        if (!is_wp_error($rss)) {
        
            // get items
            $rss_items = $rss->get_items(0,$atts['number']);

            $max = 0;
            // iterate over all items
            foreach($rss_items as $rss_item) {
                
                // vars
                //$title = $rss_item->get_title();
                $link = $rss_item->get_link();
                $id = array_pop(explode('/', $link));
                $date = $rss_item->get_date();
                $content = $rss_item->get_description();
                $tag = "https://twitter.com/".$user;

                $date_key = strtotime($date);

                $tweets[$link] = [
                'user' => $user, 
                'date_key' => $date_key, 
                'date' => $date, 
                'content' => $content,
                'link' => $link,
                'tag' => $tag,
                'id' => $id,
                ];
                $max++;
                if ($max == $number) {
                    break;   
                } 
            }
        }
    }

    usort($tweets, 'compare_date_keys');

    // start a buffer so echo can be used
    ob_start();

    if (!empty($tweets)) {
        echo '<table class="twitter_table" style="width: '.$width.'px;">';

        $max = 0;

        foreach ($tweets as $tweet) {
            if ($tweet['content']) {
                echo '<tr><td class="twitter_tweet">';
                echo '<a class="twitter_text" href="'.$tweet['link'].'">';
                echo '<div class="twitter_circle_fun"></div>';
                $hashtag = '\1</a><a class="twitter_tag_link" href="http://twitter.com/search?q=%23\2">#\2</a><a class="twitter_text" href="'.$tweet['link'].'">';
                $handle = '\1</a><a class="twitter_tag_link" href="http://twitter.com/\2">@\2</a><a class="twitter_text" href="'.$tweet['link'].'">';
                $content = $tweet['content'];
                $content = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', $hashtag, $content);
                $content = preg_replace('/(^|\s)@(\w*[a-zA-Z_]+\w*)/', $handle, $content);
                echo $content;
                echo '</a>';
                echo '<div class="twitter_follow">';
                echo '<a href="https://twitter.com/intent/follow?screen_name=';
                echo $tweet['user'];
                echo '">';
                echo "<button class='twitter_button'><img class='twitter_icon' src='".plugins_url('twitter-256.png', __FILE__)."'></img>follow</button>";
                echo '</a>';

                echo '<a href="https://twitter.com/intent/retweet?tweet_id=';
                echo $tweet['id'];
                echo '">';
                echo "<button class='twitter_button'><img class='twitter_icon' src='".plugins_url('icon-retweet-white.png', __FILE__)."'></img>retweet</button>";
                echo '</a>';

                echo '<script src="https://platform.twitter.com/widgets.js"></script>';
                echo '</div>';
                echo '</td></tr><tr><td class="twitter_info">';
                echo "<div class='twitter_date'>".$tweet['date']."</div>";
                echo "<div class='twitter_tag'>";
                echo "<a class='twitter_tag_link' href='".$tweet['tag']."'>";
                echo "@".$tweet['user'];
                echo "</a>";

               

                echo "</div>";
                echo '</td></tr>';
                echo '<tr><td class="twitter_gap_td"> </td></tr>';
                $max++;
                if ($max == $total) {
                    break;
                }
            }
        }

        echo '</table>';
    }


    // return buffer
    $output_string = ob_get_contents();;
    ob_end_clean();
    return $output_string;

}

// sets attributes to parameters or defaults if undeclared
function twitter_defaults($atts) {
    return shortcode_atts( array(
        'user' => 'autodesk',
        'total' => 10,
        'number' => 4,
        'width' => 300,
    ), $atts );
}

function compare_date_keys($a, $b)
{
    $key_a = $a['date_key'];
    $key_b = $b['date_key'];
    if ($key_a == $key_b) {
        return 0;
    }
    return ($key_a > $key_b) ? -1 : 1;
}

?>