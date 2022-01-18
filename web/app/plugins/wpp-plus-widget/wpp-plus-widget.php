<?php
/*
   Plugin Name: WPP Plus Widget
   Plugin URI: http://8wired.jp/
   Description: Wordpress Popular Postsのデータを利用したカテゴリ毎の人気記事を表示するウィジェット
   Version: 0.4.7
   Author: 8wired.jp
   Author URI: http://8wired.jp/
   License: GPL2 http://www.gnu.org/licenses/gpl.html
*/

/*
  更新内容：
  - rangeオプションの初期値を修正
*/
   
class WPPPlusWidget extends WP_Widget {

  var $_depth = 1;
  var $_params = 'limit=10
range=last7days
order_by=views
thumbnail_width=100
thumbnail_height=100
excerpt_length=50
stats_comments=0';
  var $_wpppw_start = '<ul class="wpp-list">';
  var $_wpppw_end   = '</ul>';
  var $_post_html   = '<li>{thumb} {title} {stats}</li>';

  function __construct() {
    parent::__construct( 'wpppw',
                         'WPP Plus Widget',
                         array(
                               'classname'   => 'wpp-plus-widget',
                               'description' => 'Wordpress Popular PostsのカスタムWidget')
                         );
  }

  function widget( $args, $instance ) {
    if( !function_exists( 'wpp_get_mostpopular' ) )
      return;
    if( $instance['hide_in_home'] == 'y' and is_home() )
      return;
    if( $instance['hide_in_category'] == 'y' and is_category() )
      return;

    extract( $args );
    $title     = $instance['title'];
    $title_under = $instance['title_under'];
    $depth     = is_numeric( $instance['depth']) ? $instance['depth'] : $this->_depth;
    $params    = $instance['params'] ? $instance['params'] : $this->_params;
    $exclude_current_post = $instance['exclude_current_post'] == 'y' ? 'y' : 'n';
    $wpppw_start = $instance['wpppw_start'] ? $instance['wpppw_start'] : $this->_wpppw_start;
    $wpppw_end = $instance['wpppw_end'] ? $instance['wpppw_end'] : $this->_wpppw_end;
    $post_html = $instance['post_html'] ? $instance['post_html'] : $this->_post_html;

    $post_args = array( 'wpp_start' => $wpppw_start,
                        'wpp_end'   => $wpppw_end,
                        'post_html' => $post_html );

    // creating output-html
    $rows = explode( "\n", $params );
    foreach( $rows as $row ) {
      list( $key, $value ) = explode( "=", $row );
      $post_args[ $key ] = $value;
    }

    $term_id = "";
    $cat_name = "";

    // getting the current category or categories
    if( is_single() or is_category() or is_tag() ) {
      if( is_tag() ) {
        $the_query = new WP_Query( array( 'tag_id' => get_query_var( 'tag_id' ), 'posts_per_page' => 1 ) );
        while( $the_query -> have_posts() ) {
          $the_query -> the_post();
          $cat = get_the_category();
          $term_id = $cat[0]->cat_ID;
          $cat_name = $cat[0]->name;
          break;
        }
        wp_reset_postdata();
      } elseif( is_category() ) {
        $cat= get_queried_object();
        $term_id = $cat->term_id;
        $cat_name = $cat->name;
      } elseif( is_single() ) {
        $cat = get_the_category();
        $term_id = $cat[0]->cat_ID;
        $cat_name = $cat[0]->name;
        if( $exclude_current_post == 'y' ) {
          if( $post_args['pid'] )
            $post_args['pid'] .= ",".get_the_ID();
          else
            $post_args['pid'] = get_the_ID();
        }
      }

      if( $depth == 0 ) {
        $cat_name = "";
      } else {
        if( $term_id ) {
          $roots = $this->_category_roots( $term_id );
          if( count( $roots ) > $depth - 1 ) {
            $term_id = $roots[ $depth - 1 ]['term_id'];
            $cat_name = $roots[ $depth - 1 ]['name'];
          }
          $categories = get_categories( "child_of=" . $term_id );
          $cats = array( $term_id );
          foreach( $categories as $category )
            $cats[] = $category->term_id;
          $post_args['taxonomy'] = 'category';
          $post_args['term_id'] = join( ',', $cats );
        }
      }
    }

    echo $before_widget;
    if( $title ) {
      if( $title_under and $cat_name )
        $title = str_replace( '{category}', $cat_name, $title_under );
      echo $before_title . apply_filters( 'widget_title', $title ) . $after_title;
    }

    //if(!isset($post_args['cat']))
    //  $post_args['cat'] = "0";

    wpp_get_mostpopular( $post_args );
    echo $after_widget;
  }

  private function _category_roots( $id ) {
    $roots = array();
    for( ; ; ) {
      $cat = get_category( $id );
      $roots[] = array(
                       'term_id' => $id,
                       'name' => $cat->name
                       );
      if($cat->parent === 0)
        break;
      $id = $cat->parent;
    }
    return array_reverse($roots);
  }

  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = trim( $new_instance['title'] );
    $instance['title_under'] = trim( $new_instance['title_under'] );
    $depth = trim( $new_instance['depth'] );
    $instance['depth'] = is_numeric( $depth ) ? $depth : $this->_depth;
    $params = str_replace( "\r\n", "\n", trim($new_instance['params'] ) );
    $params = str_replace( "\r", "\n", $params );
    if( function_exists( 'mb_convert_kana' ) )
      $params = mb_convert_kana( $params, "a", "UTF-8" );
    $instance['params'] = $params;
    $instance['wpppw_start'] = isset( $new_instance['wpppw_start'] ) ? trim( $new_instance['wpppw_start'] ) : $this->_wpppw_start;
    $instance['wpppw_end'] = isset( $new_instance['wpppw_end'] ) ? trim( $new_instance['wpppw_end'] ) : $this->_wpppw_end;
    $instance['post_html'] = trim( $new_instance['post_html'] );
    $instance['exclude_current_post'] = $new_instance['exclude_current_post'] == 'y' ? 'y' : 'n';
    $instance['hide_in_home'] = $new_instance['hide_in_home'] == 'y' ? 'y' : 'n';
    $instance['hide_in_category'] = $new_instance['hide_in_category'] == 'y' ? 'y' : 'n';
    return $instance;
  }

  public function form( $instance ) {
    $title = $instance['title'];
    $title_under = $instance['title_under'];
    $depth = isset( $instance['depth'] ) ? $instance['depth'] : $this->_depth;
    $params = $instance['params'] ? $instance['params'] : $this->_params;
    $wpppw_start = isset( $instance['wpppw_start']) ? $instance['wpppw_start'] : $this->_wpppw_start;
    $wpppw_end = isset( $instance['wpppw_end']) ? $instance['wpppw_end'] : $this->_wpppw_end;
    $post_html = isset( $instance['post_html']) ? $instance['post_html'] : $this->_post_html;
    $exclude_current_post = $instance['exclude_current_post'] == "y" ? "y" : "n";
    $hide_in_home = $instance['hide_in_home'] == "y" ? "y" : "n";
    $hide_in_category = $instance['hide_in_category'] == "y" ? "y" : "n";
    ?>
    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">タイトル:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $title ); ?>" /></p>
    <p><label for="<?php echo $this->get_field_id( 'title_under' ); ?>">タイトル（下層）:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title_under'); ?>" name="<?php echo $this->get_field_name( 'title_under' ); ?>" type="text" value="<?php esc_attr_e( $title_under ); ?>" /><br />
{category}：カテゴリ名に置換されます</p>
    <p><label for="<?php echo $this->get_field_id( 'depth' ); ?>">カテゴリの深さ:</label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'depth' ); ?>" name="<?php echo $this->get_field_name( 'depth' ); ?>">
    <?php for( $i = 0; $i <= 10; $i++ ) { ?>
    <option<?php if( $i == $depth ) { echo ' selected'; } ?>><?php echo $i; ?></option>
    <?php } ?>
    </select></p>
    <p><label for="<?php echo $this->get_field_id( 'text' ); ?>">パラメータ:</label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'params' ); ?>" name="<?php echo $this->get_field_name( 'params' ); ?>" rows="8"><?php echo $params; ?></textarea></p>
    <hr style="margin:1.5em 0 1em;">
    <strong>フォーマット設定</strong>
    <p><label for="<?php echo $this->get_field_id( 'wpppw-start' ); ?>">記事一覧の前後:</label><br />
    <input class="widefat" id="<?php echo $this->get_field_id( 'wpppw-start' ); ?>" name="<?php echo $this->get_field_name( 'wpppw_start' ); ?>" type="text" value="<?php esc_attr_e( $wpppw_start ); ?>" style="width:48% !important;" />
    <input class="widefat" id="<?php echo $this->get_field_id( 'wpppw-end' ); ?>" name="<?php echo $this->get_field_name( 'wpppw_end' ); ?>" type="text" value="<?php esc_attr_e( $wpppw_end ); ?>" style="width:48% !important;" /></p>
    <p><label for="<?php echo $this->get_field_id( 'post-html' ); ?>">記事フォーマット:</label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'post-html' ); ?>" name="<?php echo $this->get_field_name( 'post_html' ); ?>" rows="5"><?php esc_attr_e( $post_html ); ?></textarea></p>
    <hr style="margin:1.5em 0 1em;">
    <strong>オプション</strong>
    <p><input id="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>" name="<?php echo $this->get_field_name( 'exclude_current_post' ); ?>" type="checkbox" value="y"<?php if( $exclude_current_post == 'y') echo ' checked'; ?> /> <label for="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>">表示記事を除外する</label><br />
    <input id="<?php echo $this->get_field_id( 'hide_in_home' ); ?>" name="<?php echo $this->get_field_name( 'hide_in_home' ); ?>" type="checkbox" value="y"<?php if( $hide_in_home == 'y' ) echo ' checked'; ?> /> <label for="<?php echo $this->get_field_id( 'hide_in_home' ); ?>">トップページで表示しない</label><br />
    <input id="<?php echo $this->get_field_id( 'hide_in_category' ); ?>" name="<?php echo $this->get_field_name( 'hide_in_category' ); ?>" type="checkbox" value="y"<?php if( $hide_in_category == 'y' ) echo ' checked'; ?> /> <label for="<?php echo $this->get_field_id( 'hide_in_category' ); ?>">カテゴリページで表示しない</label></p>
    <?php
  }

}
add_action( 'widgets_init', function(){register_widget( 'WPPPlusWidget' );});


class WPPPWCategoryPosts extends WP_Widget {

  var $_depth = 1;
  var $_num_posts = 5;
  var $_posts_start = '<ul class="category-posts">';
  var $_posts_end   = '</ul>';
  var $_post_html   = '<li>{thumb} {title}</li>';
  var $_img_width   = 100;
  var $_img_height  = 100;

  public function __construct() {
    $widget_options = array( 'description' => '表示している記事と同じカテゴリの新着記事を表示' );
    parent::__construct('wpppw-category-posts', '新着記事', $widget_options);
  }

  public function widget( $args, $instance ) {
    if( $instance['hide_in_home'] == 'y' and is_home() )
      return;
    if( $instance['hide_in_category'] == 'y' and is_category() )
      return;

    extract( $args );
    $title       = $instance['title'];
    $title_under = $instance['title_under'];
    $depth       = is_numeric( $instance['depth'] ) ? $instance['depth'] : $this->_depth;
    $num_posts   = is_numeric( $instance['num_posts'] ) ? $instance['num_posts'] : $this->_num_posts;
    $posts_start = $instance['posts_start'] ? $instance['posts_start'] : $this->_posts_start;
    $posts_end   = $instance['posts_end'] ? $instance['posts_end'] : $this->_posts_end;
    $post_html   = $instance['post_html'] ? $instance['post_html'] : $this->_post_html;
    $noimg_url   = $instance['noimg_url'];
    $img_width   = $instance['img_width'] ? $instance['img_width'] : $this->_img_width;
    $img_height  = $instance['img_height'] ? $instance['img_height'] : $this->_img_height;
    $exclude_current_post = $instance['exclude_current_post'] == 'y' ? 'y' : 'n';
    
    $args = array( 'posts_per_page' => $num_posts );

    $term_id = "";
    $cat_name = "";

    // getting the current category or categories
    if( is_single() or is_category() or is_tag() ) {
      if( is_tag()) {
        $the_query = new WP_Query( array( 'tag_id' => get_query_var( 'tag_id' ), 'posts_per_page' => 1 ) );
        while ( $the_query -> have_posts() ) {
          $the_query -> the_post();
          $cat = get_the_category();
          $term_id = $cat[0]->cat_ID;
          $cat_name = $cat[0]->name;
          break;
        }
        wp_reset_postdata();
      } elseif( is_category() ) {
        $cat= get_queried_object();
        $term_id = $cat->term_id;
        $cat_name = $cat->name;
      } elseif( is_single() ) {
        $cat = get_the_category();
        $term_id = $cat[0]->cat_ID;
        $cat_name = $cat[0]->name;
        if( $exclude_current_post == 'y' )
          $args['post__not_in'] = array(get_the_ID());
      }

      if( $depth == 0 ) {
        $cat_name = "";
      } else {
        if( $term_id ) {
          $roots = $this->_category_roots( $term_id );
          if( count( $roots ) > $depth - 1 ) {
            $term_id = $roots[ $depth - 1 ]['term_id'];
            $cat_name = $roots[ $depth - 1 ]['name'];
          }
          $categories = get_categories( "child_of=".$term_id );
          $cats = array( $term_id );
          foreach( $categories as $category )
            $cats[] = $category->term_id;
          $args['category__in'] = $cats;
        }
      }
    }

    $the_query = new WP_Query( $args );

    echo $before_widget;
    if( $title ) {
      if( $title_under and $cat_name )
        $title = str_replace( '{category}', $cat_name, $title_under );
      echo $before_title.apply_filters( 'widget_title', $title ) . $after_title;
    }

    if( $the_query->have_posts()) {
      echo $posts_start;
      while( $the_query->have_posts() ) {
        $the_query->the_post();
        if(preg_match("/{thumb:([^}]+)}/i", $post_html, $matches)) {
          $thumb_tag = $matches[0];
          $thumb_size = $matches[1];
        } else {
          $thumb_tag = '{thumb}';
          $thumb_size = 'thumbnail';
        }
        if( has_post_thumbnail() ) {
          list($src, $width, $height) = wp_get_attachment_image_src( get_post_thumbnail_id(), $thumb_size );
          $thumb = '<img src="' . $src.'" width="' . $img_width . '" height="' . $img_height . '" title="no image" alt="no image" />';
          $thumb_url = $src;
        } else {
          $thumb = '<img src="' . $noimg_url.'" width="' . $img_width . '" height="' . $img_height . '" title="no image" alt="no image" />';
          $thumb_url = $noimg_url;
        }
        $category = get_the_category();
        $table = array('{title}'     => '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>',
                       '{url}'       => get_the_permalink(),
                       $thumb_tag    => '<a href="' . get_the_permalink() . '">' . $thumb . '</a>',
                       '{thumb_url}' => $thumb_url,
                       '{date}'      => get_the_time( 'Y/m/d' ),
                       '{excerpt}'   => get_the_excerpt(),
                       '{comments}'  => get_comments_number(),
                       '{category}'  => '<a href="' . get_category_link( $category[0]->cat_ID ) . '">' . $category[0]->name . '</a>'
                       );
        $search = array_keys($table);
        $replace = array_values($table);
        echo str_replace( $search, $replace, $post_html );
      }
      echo $posts_end;
    } else {
      echo '<p>新しい記事は見つかりませんでした。</p>';
    }
    echo $after_widget;

    wp_reset_postdata();
  }

  private function _category_roots( $id ) {
    $roots = array();
    for( ; ; ) {
      $cat = get_category( $id );
      $roots[] = array( 'term_id' => $id, 'name' => $cat->name );
      if( $cat->parent === 0)
        break;
      $id = $cat->parent;
    }
    return array_reverse( $roots );
  }

  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = trim( $new_instance['title'] );
    $instance['title_under'] = trim( $new_instance['title_under'] );
    $depth = trim( $new_instance['depth'] );
    $instance['depth'] = is_numeric( $depth ) ? $depth : $this->_depth;
    $num_posts = trim( $new_instance['num_posts'] );
    $instance['num_posts'] = is_numeric( $num_posts ) ? $num_posts : $this->_num_posts;
    $instance['posts_start'] = isset( $new_instance['posts_start'] ) ? trim( $new_instance['posts_start'] ) : $this->_posts_start;
    $instance['posts_end'] = isset( $new_instance['posts_end'] ) ? trim( $new_instance['posts_end'] ) : $this->_posts_end;
    $instance['post_html'] = trim( $new_instance['post_html'] );
    $instance['noimg_url'] = trim( $new_instance['noimg_url'] );
    $img_width = trim( $new_instance['img_width'] );
    $instance['img_width'] = is_numeric( $img_width ) ? $img_width : $this->_img_width;
    $img_height = trim( $new_instance['img_height'] );
    $instance['img_height'] = is_numeric( $img_height ) ? $img_height : $this->_img_height;
    $instance['exclude_current_post'] = $new_instance['exclude_current_post'] == 'y' ? 'y' : 'n';
    $instance['hide_in_home'] = $new_instance['hide_in_home'] == 'y' ? 'y' : 'n';
    $instance['hide_in_category'] = $new_instance['hide_in_category'] == 'y' ? 'y' : 'n';
    return $instance;
  }

  public function form( $instance ) {
    $title = $instance['title'];
    $title_under = $instance['title_under'];
    $depth = isset( $instance['depth'] ) ? $instance['depth'] : $this->_depth;
    $num_posts = isset( $instance['num_posts'] ) ? $instance['num_posts'] : $this->_num_posts;
    $posts_start = isset( $instance['posts_start'] ) ? $instance['posts_start'] : $this->_posts_start;
    $posts_end = isset( $instance['posts_end'] ) ? $instance['posts_end'] : $this->_posts_end;
    $post_html = isset( $instance['post_html'] ) ? $instance['post_html'] : $this->_post_html;
    $noimg_url = isset( $instance['noimg_url'] ) ? $instance['noimg_url'] : plugins_url().'/wordpress-popular-posts/no_thumb.jpg';
    $img_width = isset( $instance['img_width'] ) ? $instance['img_width'] : $this->_img_width;
    $img_height = isset( $instance['img_height'] ) ? $instance['img_height'] : $this->_img_height;
    $exclude_current_post = $instance['exclude_current_post'] == "y" ? "y" : "n";
    $hide_in_home = $instance['hide_in_home'] == "y" ? "y" : "n";
    $hide_in_category = $instance['hide_in_category'] == "y" ? "y" : "n";
    ?>
    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">タイトル:</label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $title ); ?>" /></p>
    <p><label for="<?php echo $this->get_field_id( 'title_under' ); ?>">タイトル（下層）:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title_under'); ?>" name="<?php echo $this->get_field_name( 'title_under' ); ?>" type="text" value="<?php esc_attr_e( $title_under ); ?>" /></p>
    <p><label for="<?php echo $this->get_field_id('depth'); ?>">カテゴリの深さ:</label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'depth' ); ?>" name="<?php echo $this->get_field_name( 'depth' ); ?>">
    <?php for( $i = 0; $i <= 10; $i++ ) { ?>
    <option<?php if( $i == $depth ) {echo ' selected';} ?>><?php echo $i; ?></option>
    <?php } ?>
    </select></p>
    <p><label for="<?php echo $this->get_field_id( 'num_posts' ); ?>">記事数:</label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>">
    <?php for( $i = 1; $i <= 10; $i++ ) { ?>
    <option<?php if( $i == $num_posts ) {echo ' selected';} ?>><?php echo $i; ?></option>
    <?php } ?>
    </select></p>
    <hr style="margin:1.5em 0 1em;">
    <strong>フォーマット設定</strong>
    <p><label for="<?php echo $this->get_field_id( 'posts-start' ); ?>">記事一覧の前後:</label><br />
    <input class="widefat" id="<?php echo $this->get_field_id( 'posts-start' ); ?>" name="<?php echo $this->get_field_name( 'posts_start' ); ?>" type="text" value="<?php esc_attr_e( $posts_start ); ?>" style="width:48% !important;" />
    <input class="widefat" id="<?php echo $this->get_field_id( 'posts-end' ); ?>" name="<?php echo $this->get_field_name( 'posts_end' ); ?>" type="text" value="<?php esc_attr_e( $posts_end ); ?>" style="width:48% !important;" /></p>
    <p><label for="<?php echo $this->get_field_id( 'post-html' ); ?>">記事フォーマット:</label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'post-html' ); ?>" name="<?php echo $this->get_field_name( 'post_html' ); ?>" rows="5"><?php esc_attr_e( $post_html ); ?></textarea></p>
    <hr style="margin:1.5em 0 1em;">
    <strong>サムネイル</strong>
    <p><label for="<?php echo $this->get_field_id( 'noimg-url' ); ?>">noimageのURL:</label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'noimg-url' ); ?>" name="<?php echo $this->get_field_name( 'noimg_url' ); ?>" type="text" value="<?php esc_attr_e( $noimg_url ); ?>" maxlength="255" /></p>
    <p><label for="<?php echo $this->get_field_id( 'img-size' ); ?>">画像サイズ（横幅/高さ）:</label><br />
    <input id="<?php echo $this->get_field_id( 'img-width' ); ?>" name="<?php echo $this->get_field_name( 'img_width' ); ?>" type="text" value="<?php esc_attr_e( $img_width ); ?>" size="5" maxlength="4" /> / <input id="<?php echo $this->get_field_id( 'img-height' ); ?>" name="<?php echo $this->get_field_name( 'img_height' ); ?>" type="text" value="<?php esc_attr_e( $img_height ); ?>" size="5" maxlength="4" /></p>
    <hr style="margin:1.5em 0 1em;">
    <strong>オプション</strong>
    <p><input id="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>" name="<?php echo $this->get_field_name( 'exclude_current_post' ); ?>" type="checkbox" value="y"<?php if( $exclude_current_post == 'y') echo ' checked'; ?> /> <label for="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>">表示記事を除外する</label><br />
    <input id="<?php echo $this->get_field_id( 'hide_in_home' ); ?>" name="<?php echo $this->get_field_name( 'hide_in_home' ); ?>" type="checkbox" value="y"<?php if( $hide_in_home == 'y' ) echo ' checked'; ?> /> <label for="<?php echo $this->get_field_id( 'hide_in_home' ); ?>">トップページで表示しない</label><br />
    <input id="<?php echo $this->get_field_id( 'hide_in_category' ); ?>" name="<?php echo $this->get_field_name( 'hide_in_category' ); ?>" type="checkbox" value="y"<?php if( $hide_in_category == 'y' ) echo ' checked'; ?> /> <label for="<?php echo $this->get_field_id( 'hide_in_category' ); ?>">カテゴリページで表示しない</label></p>
    <?php
  }

}
add_action( 'widgets_init', function(){register_widget( 'WPPPWCategoryPosts' );});

?>
