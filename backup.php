<?php

/*
Plugin Name: Twitter Widget
Description: Embeds Twitter in pages and posts with shortcodes
*/

wp_enqueue_style( 'twitter_style', plugins_url('rss_style.css', __FILE__));


add_shortcode( 'twitter_embed', 'twitter_embed_content' );

function twitter_embed_content($atts) {

    $atts = twitter_defaults($atts);

    $users = explode(" ", $atts['user']);
    $num_users = sizeof($users);
    
    // start a buffer so echo can be used
    ob_start();
    echo '<table>';

    for($i = 0; $i < $num_users; $i++) {
        // fetch feed using WP
        $rss = fetch_feed('http://twitrss.me/twitter_user_to_rss/?user='.$users[$i]);

        if (!is_wp_error($rss)) {
        
        

            // get items
            $rss_items = $rss->get_items(0,$atts['number']);


            // iterate over all items
            foreach($rss_items as $rss_item) {

                // vars
                //$title = $rss_item->get_title();
                $link = $rss_item->get_link();
                $date = $rss_item->get_date();
            
                $content = $rss_item->get_description();
                $content_len = strlen($content);

                if ($content) {
                    echo '<tr><td>';
                    echo '<a href="';
                    echo $link;
                    echo '">';
                    echo $content;
                    echo '</a>';
                    echo '<br />';
                    echo "@".$users[$i]." | ".$date;
                    echo '</td></tr>';
                    echo '<tr><td> </td></tr>';
                }
            }
        }
    }
    echo '</table>';
    // return buffer
    $output_string = ob_get_contents();;
    ob_end_clean();
    return $output_string;

}

// sets attributes to parameters or defaults if undeclared
function twitter_defaults($atts) {
    return shortcode_atts( array(
        'user' => 'autodesk',
        'number' => 5,
        'chars' => 250,
        'max_chars' => 500,
        'roll' => 'yes',
    ), $atts );
}

?>