<?php
/*
Plugin Name: GY EDD ADD-ON
Plugin URI: 
Description: Add functionality for custom fields and taxonomy
Version: 
Author: Glezer Yaacov
Author URI: www.jts.co.il
License: 
License URI: 
*/

const GOOD_WORKTYPES = array('maman','final','exam','summery','seminarion');
add_action('admin_menu', 'gy_plugin_setup_menu');
 
function gy_plugin_setup_menu(){
    $menu_slug = 'gy-edd-add-on';

    add_menu_page( 'GY Plugin Page', 'GY EDD ADD-ON', 'manage_options', $menu_slug,'gy_init');
    add_submenu_page( $menu_slug, 'Update worktype to not-set', 'Update default work-type', 'manage_options', $menu_slug."_no_worktype_0", 'gy_update_default_worktype' );
    add_submenu_page( $menu_slug, 'Remove double tax', 'Remove double work-type', 'manage_options', $menu_slug."_1", 'remove_double_worktype' );
    add_submenu_page( $menu_slug, 'Remove double cat', 'Remove double category', 'manage_options', $menu_slug."_2", 'remove_double_category' );
    add_submenu_page( $menu_slug, 'Update work type', 'Update work type', 'manage_options', '', 'gy_find_term' );    
    add_filter( 'pre_get_posts', 'gy_exclude_posts' );
    add_submenu_page(  $menu_slug, 'No Worktype admin list','No WorkType', 'manage_options', 'edit.php?post_type=download&gy_setting=true');
   

}
/***********************************************/
function gy_exclude_posts( $query ) {
    if ( $query->is_main_query() && is_admin()) {
        if(($_REQUEST['post_type']=="download")&&($_REQUEST['gy_setting']=="true")) {
           $query->set( 'tax_query' , array(
                                            array(
                                                'taxonomy' => 'worktype',
                                                'field'    => 'slug',
                                                'terms'    => GOOD_WORKTYPES,
                                                'operator' => 'NOT IN',
                                                )));
        }
    }
}
/**********************************************/
 
function gy_init(){
    ?>
    <h1>GY EDD ADD-ON Plugin</h1>
    <?php
    $terms =  gy_worktype_is_set();
    if($terms) {
        echo "<h3>Setting custom taxonomy already done.</h3>";
        echo "<h3>Active worktypes:</h3>";
        echo "<table style='direction:rtl;'>";
        echo "<tr><th>טקסונומיה</th><th>מספר פוסטים</th></tr>";
        foreach ($terms as $term) {
            echo "<tr><td>$term->name</td><td>$term->count</td></tr>";
        }
        echo "</table>";
        echo "<div>".str_replace(")",")</br>",print_r($terms,true))."</div>";
    } else {
        ?>
        <div>
            <h3>Custom taxonomy 'worktype' is not set yet.</h3>
            <p>After setting-up the custom taxonimies, please continue with one of the menu options.</p>
            <form method="post">
                <input type="submit" id="start-setup" name="start-setup" class="button" value="START">
            </form>
        </div>
        <?php
        if(isset($_POST['start-setup'])) {
            echo "<h3>Setting custom taxonomy 'worktype'</h3>";
            gy_set_taxonomy('worktype');
            gy_show_tax_table('worktype');
            echo "<h3>Setting custom taxonomy 'compcat'</h3>";
            gy_set_taxonomy('compcat');
            gy_show_tax_table('compcat');
        }
    }
}

/******************/
function gy_worktype_is_set() {
    $terms = get_terms( array(
                         'taxonomy' => 'worktype',
                         'hide_empty' => false 
                        ));
    if($terms && !is_wp_error($terms)) {
        return $terms;
    } else {
        return null;
    }
}

/******************************************/
function gy_update_default_worktype() {
    if  (gy_worktype_is_set()) {
    ?>
    <h1>GY EDD ADD-ON Plugin - Update initial worktype</h1>
    <h3>This step update all posts with the default worktype - 'טרם נקבע'</h3>
    <div>
        <p>To minimize posible server overload, it will be done for 2000 posts each time.</p>
        <form method="post">
            <input type="submit" id="start-setup" name="start-setup" class="button" value="UPDATE">
        </form>
    </div>
<?php
        if(isset($_POST['start-setup'])) {
            gy_update_tax_on_all_posts('n-s-y','worktype');
        }    
    } else {
    ?>
        <h1>GY EDD ADD-ON Plugin 'worktype' is not set yet</h1>
        <h3>Please run setup before any other step</h3>
    <?php
    }
    
}
/***************************************/
function gy_show_tax_table($taxonomy) {
    $terms = get_terms( array(
                         'taxonomy' => $taxonomy,
                         'hide_empty' => false 
                        ));
    if($terms && !is_wp_error($terms)) {
        echo "<h3>Custom taxonomy $taxonomy values:</h3>";
        echo "<table style='direction:rtl;'>";
        echo "<tr><th>טקסונומיה</th><th>מספר פוסטים</th></tr>";
        foreach ($terms as $term) {
            echo "<tr><td>$term->name</td><td>$term->count</td></tr>";
        }
        echo "</table>";
        echo "<div>".str_replace(")",")</br>",print_r($terms,true))."</div>";
    } else {
        return null;
    }
}
/***********************************************/
function gy_set_taxonomy($tax_name=NULL) {
    if ($tax_name) {
        wp_insert_term(
            'עבודה',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'עבודה כללית',
                'slug'        => 'generic_work',
                'parent'      => 0,
            )
        );
        wp_insert_term(
            'ממ"ן',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'מטלת מנחה',
                'slug'        => 'maman',
                'parent'      => 0,
            )
        );
        wp_insert_term(
            'מבחן',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'מבחן/בוחן/מבדק',
                'slug'        => 'exam',
                'parent'      => 0,
            )
        );
        wp_insert_term(
            'עבודת סמינריון',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'סמינריון, עבודה סמינריונית, פרה-סמינריון',
                'slug'        => 'seminarion',
                'parent'      => 0,
            )
        );
        wp_insert_term(
            'סיכום',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'סיכום קורס, סיכום למבחן, סיכום נושא',
                'slug'        => 'summery',
                'parent'      => 0,
            )
        );
        wp_insert_term(
            'עבודת גמר',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'פרויקט, עבודת גמר, עבודה מסכמת',
                'slug'        => 'final',
                'parent'      => 0,
            )
        );
        wp_insert_term(
            'טרם נקבע',   // the term 
            $tax_name, // the taxonomy
            array(
                'description' => 'סוג עבודה זמני',
                'slug'        => 'n-s-y',
                'parent'      => 0,
            )
        );
    } else {
        echo "No tax selected";
    }
}
/*********************************************/
function gy_find_term(){

    ?>
    <h1>GY EDD ADD-ON - find post by term and update 'worktype'</h1>
    <h2>Enter a search term, choose worktype and click on the <strong>SEARCH</strong> button</h3>
    <h3>Please pay attention: the worktype you select is the one you want to update. </h3>
    <form method="post">
        <label for "worktype_select">Select the work type you want to search and update</label>
        <?php
        $terms = get_terms( array(
                         'taxonomy' => 'worktype',
                         'hide_empty' => false 
                        ));
		if ( count( $terms ) > 0 ) {
			echo "<select name='worktype' id='worktype_select' name='worktype' class='postform'>";
				$labels = edd_get_taxonomy_labels( 'worktype' );
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'easy-digital-downloads' ), strtolower( $labels['name'] ) ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_POST['worktype'] ) && $_POST['worktype'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select><br>";
		}
        ?>

        <input type="search" id="gy-post-search-input" name="search-term" onkeyup='saveValue(this);'>
        <input type="submit" id="search-submit" name="search-submit" class="button" value="Search"><br>
        <input type="submit" id="gy-update-posts" name="gy-update-posts" class="button" value="Update posts">
    </form>
    <script type="text/javascript">
        document.getElementById("gy-post-search-input").value = getSavedValue("gy-post-search-input");    // set the value to this input
//        document.getElementById("txt_2").value = getSavedValue("txt_2");   // set the value to this input
        /* Here you can add more inputs to set value. if it's saved */

        //Save the value function - save it to localStorage as (ID, VALUE)
        function saveValue(e){
            var id = e.id;  // get the sender's id to save it . 
            var val = e.value; // get the value. 
            localStorage.setItem(id, val);// Every time user writing something, the localStorage's value will override . 
        }

        //get the saved value function - return the value of "v" from localStorage. 
        function getSavedValue  (v){
            if (!localStorage.getItem(v)) {
                return "Search term";// You can change this to your defualt value. 
            }
            return localStorage.getItem(v);
        }
    </script>
    <?php

    if(isset($_POST['search-term']) && isset($_POST['search-submit']) && isset($_POST['worktype'])) {
        $worktype = $_POST['worktype'];
        $phrase = $_POST['search-term'];
        echo "<p>search result of $phrase not in worktype $worktype</p>";
        $posts = gy_show_downloads_by_term($phrase,$worktype,5);
    }
    if(isset($_POST['search-term']) && isset($_POST['gy-update-posts']) && isset($_POST['worktype'])) {
        $worktype = $_POST['worktype'];
        $phrase = $_POST['search-term'];
        $posts = gy_show_downloads_by_term($phrase,$worktype,5);
        echo "<p>Updating worktype:$worktype with phrase $phrase on ".count($posts)." posts</p>";
        gy_update_tax_on_all_posts($worktype,'worktype',$posts);
    }
}
/******************************/

function cc_post_title_filter($where, $wp_query) {
    global $wpdb;
    if ( $search_term = $wp_query->get( 'cc_search_post_title' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like( $search_term ) . '%\'';
//        $where .= ' AND ' . $wpdb->posts . '.post_title CONTAINS \'%' . $wpdb->esc_like( $search_term ) . '%\'';        
    }
    return $where;
}
/*****************************/

function gy_show_downloads_by_term($search_term,$worktype,$numberposts) {
    $args = array(
        'post_type'     => 'download',
        'post_status'   => 'publish',
        'numberposts'   => $numberposts,
        'cc_search_post_title' => $search_term,
        'suppress_filters' => FALSE,
        'orderby'       => 'title',
        'order'         => 'ASC',
        'tax_query' => array ( 
                            array( 'taxonomy' => 'worktype',
                                    'field' => 'slug',
                                    'terms'    => array($worktype),
                                    'operator' => 'NOT IN'))
    );
    
    // Get all posts
    
    add_filter( 'posts_where', 'cc_post_title_filter', 10, 2 );
    $posts = get_posts($args);
    remove_filter( 'posts_where', 'cc_post_title_filter', 10 );
    $num_of_posts = count($posts);
    if ($num_of_posts>0) {
        echo "<h3>Downloads where worktype is <strong>not</strong><em> $worktype </em>and term is: $search_term .</h3>";
        echo "<div style='direction:ltr'><p>showing first $num_of_posts posts</p>";

        foreach ($posts as $post) {
            $post_id = $post->ID;
            $post_title = $post->post_title;
    
            $categories = gy_get_custom_tax($post_id,'download_category','name','array');
            echo "<p><a href='/wp-admin/post.php?post=$post_id&action=edit' target='_blank'>$post_id $post_title</a>: ".print_r($categories,true)."</p>";  
        }
        echo "<p>Pressing the <strong>update</strong> button will update worktype <strong>$worktype</strong> to all the posts in the list.</p>";
        return $posts;
    } else {
        echo "<h2>No relevant posts found, please check your search term.";
    }
}



/*********************************************/
function remove_double_worktype() {
    $tax_name = 'n-s-y';
    echo "<h2>Removing worktype '$tax_name' from posts with multiple worktypes</h2>";
    remove_double_taxonomy('worktype',$tax_name,GOOD_WORKTYPES);
}
/*********************************************/
function remove_double_category() {
    $tax_name = 'mmn';
    echo "Removing category '$tax_name' from posts";
    $cats = get_terms('download_category');
    $good_cats = array();
    foreach($cats as $cat) {
        if($cat->slug!=$tax_name) $good_cats[] = $cat->slug;
    }
    remove_double_taxonomy('download_category',$tax_name,$good_cats);
}
/************************************/
function remove_double_taxonomy($taxonomy,$tax_name,$terms_in){
    
    if (!$taxonomy && !$tax_name) return ;
    $args = array(
        'post_type'     => 'download',
        'post_status'   => 'publish',
        'numberposts'   => 2000,
        'orderby'       => 'title',
        'order'         => 'ASC',
        'tax_query' => array ( 
                            array( 'taxonomy' => $taxonomy,
                                    'field' => 'slug',
                                    'terms' => $tax_name),
                            array( 'taxonomy' => $taxonomy,
                                    'field' => 'slug',
                                    'terms' => $terms_in,
                                    'operator' => 'IN'),
                            'relation' => 'AND',
                            )
    );
    
// Get all posts

    $posts = get_posts($args);
    echo "<div style='direction:ltr'><p>Found ".count($posts)." posts.</p>";

// print_r($posts);

    foreach ($posts as $post) {
        $post_id = $post->ID;
        $post_title = $post->post_title;

        $categories = gy_get_custom_tax($post_id,$taxonomy,'slug','array');
        echo "<p><a href='/downloads/$post->post_name' target='_blank'>$post_id $post_title</a>: ".print_r($categories,true)."</p>";  
        // More validation to ensure only desired posts are changed:
        
        if (count($categories)>1) {
            echo "<h4>post_ID:$post_id $post_title :".count($categories)." taxonomies($tax_name)</h4>";         
            if (in_array($tax_name,$categories)  ) {
                wp_remove_object_terms($post_id, $tax_name,$taxonomy );
                echo "<p>*** <em>before</em>:".print_r($categories,true)."  <em>after</em>:".print_r(gy_get_custom_tax($post_id,$taxonomy,'name','array'),true)."</p>";  
            }
        }
    }
    echo "</div>";
}
//add_shortcode('kill_target','update_all_posts',true);


/*********************************************/
// Update all post
function gy_update_tax_on_all_posts($tax_value,$tax_name,$posts=null) {
    
    $term_id = term_exists($tax_value, $tax_name);

    if ($term_id['term_id'] !==0 && $term_id['term_id'] !==null) {
        $cat_id = ($term_id['term_id']);
    }
    if (!$posts) {
        $args = array(
            'post_type'     => 'download',
            'post_status'   => 'publish',
            'numberposts'   => -1,
            'orderby'       => 'title',
            'order'         => 'ASC',
            'tax_query' => array ( 
                                array( 'taxonomy' => $tax_name,
                                        'field' => 'slug',
                                        'terms'    => 0)),//array('maman','exam','summery','seminarion','final','n-s-y'),
                                        //'operator' => 'NOT IN',))
        );
    
        $posts = get_posts($args);
        echo "<h3>posts without $tax_name: $target_cat</h3><div style='direction:ltr'><p>posts left:".count($posts)."</p>";

 
        $args = array(
            'post_type'     => 'download',
            'post_status'   => 'publish',
            'numberposts'   => 2000,
            'orderby'       => 'title',
            'order'         => 'ASC',
            'tax_query' => array ( 
                                array( 'taxonomy' => $tax_name,
                                        'field' => 'slug',
                                        'terms'    => array('maman','exam','summery','seminarion','final','n-s-y'),
                                        'operator' => 'NOT IN',))
            );
        $posts = get_posts($args);
        $local_posts = true;
    }
    
    foreach ($posts as $post) {
        $post_id = $post->ID;
        $post_title = $post->post_title;

        $custom_tax = gy_get_custom_tax($post_id,$tax_name,'name','array');
        echo "<p>$post_id - <span><a href='/downloads/$post->post_name' target='_blank'>$post_title</a></span><span>".print_r($custom_tax,true);  

        // More validation to ensure only desired posts are changed:
        
        if ((count($custom_tax)<1 && $local_posts)||!$local_posts) {

            $set_rc = print_r(wp_set_post_terms( $post_id,array($cat_id),$tax_name),true);           
            echo " worktypes:". count(gy_get_custom_tax($post_id,$tax_name,'name','array')).",RC=$set_rc</span></p>";
//            if (in_array($target_cat,$categories)  ) {
//                wp_remove_object_terms($post_id, $target_cat, 'download_category' );
 //               echo "<p>*** <em>before</em>:".print_r($categories,true)."  <em>after</em>:".print_r(gy_get_custom_tax($post_id,'download_category','name','array'),true)."</p>";  
//            }
        }
    }
    echo "</div>";
}
/***********************************************
 * GY change order of meta and content
 *****************************************/

function gy_fes_show_custom_fields( $content ) {
	global $post;

	if (isset($post->post_type) && ( $post->post_type != 'download' )) {
		return $content;
	}
	if ( !is_single($post) ) {
		return $content;
	}

	$show_custom = EDD_FES()->helper->get_option( 'fes-show-custom-meta', false );
	$form_id     = EDD_FES()->helper->get_option( 'fes-submission-form', false );

	if ( ! $show_custom || ! $form_id ) {
		return $content;
	}
	$form = EDD_FES()->helper->get_form_by_id( $form_id, $post->ID );
	$html = $form->display_fields();
	$excerpt_title = "<h3>תקציר העבודה</h3>";

	return "<div class='gy_edd_fes_table'>" . $html . "</div>" . $excerpt_title . "<div class='gy-download-content'>".$content . "</div>";
}
remove_filter( 'the_content', 'fes_show_custom_fields' );
add_filter( 'the_content', 'gy_fes_show_custom_fields' );

/***************************************
 * GY page title shortcode
 */
function page_title_sc( ){
   return get_the_title();
}
add_shortcode( 'page_title', 'page_title_sc' );

/****************************************
 * GY exclude empty fields from meta table
 */

add_filter( 'fes_submission_form_display_fields_fields', 'gy_exclude_empty_fields',10,3);
function gy_exclude_empty_fields($fields, $t, $user_id ) {
    $new_fields = array();
    foreach ( $fields as $field ) {
        $has_value = $field->get_field_value_frontend( $field->save_id, $user_id );
		if ( $field->is_public() && ($has_value!="")) {
		    array_push($new_fields,$field);
		}
	}    
	return $new_fields;
}

// GY - add "ILS" symbol by using edd hook edd_ils_currency_filter_after

add_filter( 'edd_ils_currency_filter_after', 'gy_add_ils_symbol',10,3);
add_filter( 'edd_ils_currency_filter_before', 'gy_add_ils_symbol',10,3);
function gy_add_ils_symbol($formatted, $currency, $price ) {
    return '&#8362;'.$price;
}

/********************************************
 * add taxonomies : work type and complex category
 **/
//hook into the init action and call create_topics_nonhierarchical_taxonomy when it fires
 
add_action( 'init', 'gy_create_topics_nonhierarchical_taxonomy', 0 );
 
function gy_create_topics_nonhierarchical_taxonomy() {
 
// Labels part for the GUI
 
  $labels = array(
    'name' => _x( 'Work types', 'taxonomy general name' ),
    'singular_name' => _x( 'Work type', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Work types' ),
    'popular_items' => __( 'Popular Work types' ),
    'all_items' => __( 'All Work types' ),
    'parent_item' => __( 'Parent work type' ),
    'parent_item_colon' => __( 'Parent work type' ),
    'edit_item' => __( 'Edit Work type' ), 
    'update_item' => __( 'Update Work type' ),
    'add_new_item' => __( 'Add New Work type' ),
    'new_item_name' => __( 'New Work type Name' ),
    'separate_items_with_commas' => __( 'Separate Work types with commas' ),
    'add_or_remove_items' => __( 'Add or remove Work types' ),
    'choose_from_most_used' => __( 'Choose from the most used Work types' ),
    'menu_name' => __( 'Work types' ),
  ); 
 
// Now register the taxonomy 
 
  register_taxonomy('worktype','download',array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'worktype' ),
  ));
  

  // Labels part for the GUI
 
  $labels = array(
    'name' => _x( 'complex category', 'taxonomy general name' ),
    'singular_name' => _x( 'complex category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search complex categories' ),
    'popular_items' => __( 'Popular complex categories' ),
    'all_items' => __( 'All complex categories' ),
    'parent_item' => __( 'Parent complex categories' ),
    'parent_item_colon' => __( 'Parent complex categories' ),
    'edit_item' => __( 'Edit complex category' ), 
    'update_item' => __( 'Update complex category' ),
    'add_new_item' => __( 'Add New complex category' ),
    'new_item_name' => __( 'New complex category Name' ),
    'separate_items_with_commas' => __( 'Separate complex category with commas' ),
    'add_or_remove_items' => __( 'Add or remove complex category' ),
    'choose_from_most_used' => __( 'Choose from the most used complex category' ),
    'menu_name' => __( 'complex category' ),
  ); 
 
// Now register the taxonomy 
 
  register_taxonomy('complexcategory','download',array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'complexcategory' ),
  ));
}
/***********************************/

function gy_get_custom_tax($post_id,$tax_name,$field='name',$format='string') {

	$regs = get_the_terms($post_id,$tax_name);
	$on_reg = '';
	                     
	if ( $regs && ! is_wp_error( $regs ) ) : 
		$reg_links = array();
 		foreach ( $regs as $term ) {
			$reg_links[] = $term->$field;
		}
		$on_reg = join( ", ", $reg_links );
	endif;
	if ($format!='string') return $reg_links;
	return $on_reg;
}

/**********************************************************
 * used for debug only
 * */
 
add_shortcode( 'post_taxo', 'gy_post_taxonomies' );
function gy_post_taxonomies() {
    global $post_id;
 //   $tax = get_post_taxonomies($post_id);
 //   $tax = get_the_terms($post_id,'worktype');
    $score = get_post_custom_values('score',$post_id);
    $tax = gy_get_custom_tax($post_id,'worktype');
    $cat = gy_get_custom_tax($post_id,'download_category');
    $new_score = preg_match('/ציון\s\d{2,3}/',single_post_title('',false),$matches);
    $new_score = preg_replace('/ציון\s/','',$matches[0]);
    $compcat = get_the_terms($post_id,'complexcategory');
    $orig_t2 = get_post_custom_values('original_title',$post_id);
    $gy_cats = "<div>gy_cats:".print_r(get_post_custom_values('gy_cats',$post_id),true)."</div>";// "sprintf("<div>%s1</div>",get_the_terms($post_id,'gy_cats'));

    $taxo = "<div class='gy-tax' style='direction:ltr;'><h4>CC:".$post_id." ".print_r($compcat,true).":CC</h4><h4>OT: ".print_r($orig_t2,true)." :TO</h4>".$gy_cats."<div>tax1:".print_r($tax,true).
            "</div><div>tax2:".print_r($comcat,true)."</div><div>score:".print_r($score,true).
            "</div><div>matches:".print_r($matches,true)."</div><div>new score:$new_score</div><div>cats:".print_r($cat,true)."</div></div>"; 
    return $taxo;
}

/**********************************************************
 * strip phrases from title and move to custom fields
 * */
 
add_action('save_post','gy_update_score',60,1);

function gy_update_score($post_id) {
    if ( wp_is_post_revision( $post_id ) ) return;
    if (get_post_type($post_id) == 'download') {
        
        $post_title = get_the_title($post_id);

        $upd = gy_strip_title($post_id,get_the_title($post_id),'\,?\sציון\s','ציון','\d{2,3}','score');
        $upd = gy_strip_title($post_id,get_the_title($post_id),'\s?ממ"?ן\s','ממ"ן','\d{2}','maman');
/*        if ($upd) {
            $new_title = $upd['title'];
            $new_value = $upd['value'];
            $my_post = array(
                'ID'           => $post_id,
                'post_title'   => $new_title.print_r($upd,true)." [** $new_value ** ## $new_title ##]",
//                    'post_content' => "<h2>$post_title</h2><h2>new title:$new_title<h2><p> new score:[$new_score]</p><p> score value:[[$score_value]]</p><p>update:[[[$upd]]]</p>",
                );
            remove_action('save_post','gy_update_score',50);
            wp_update_post( $my_post );
            add_action('save_post','gy_update_score',50,1);                
                
            $update = update_post_meta( $post_id, 'score', $new_value);


        } else {
            update_post_meta( $post_id, 'score', -99);
        }
*/        
        if (!get_post_custom_values('original_title',$post_id)) update_post_meta( $post_id, 'original_title', $post_title);
        
        $worktype = gy_get_custom_tax($post_id,'worktype');
        if (!$worktype) $worktype = "עבודה";
        $categories = gy_get_custom_tax($post_id,'download_category','name','array');
        if (!empty( $categories ) ) {
            //$categories = array('טרם נקבע');
            $parent_term = term_exists( $worktype, 'complexcategory' ); // array is returned if taxonomy is given
            $parent_term_id = intval($parent_term['term_id']);         // get numeric term id
            wp_set_post_terms( $post_id,'','complexcategory');
            foreach ($categories as $category) { 
                if ($workype!='טרם נקבע') {
                    update_download_metas($post_id,$worktype,$category,$parent_term_id,true);
                }
            }
        }
    }
}

function gy_strip_title($post_id,$title,$key_pattern,$new_key,$value_pattern,$field_name) {
    
        $current_field = get_post_custom_values($field_name,$post_id);
        $current_field_value = is_array($currant_field)? $currant_field[0] : $currant_field;
        $pattern = "/$key_pattern$value_pattern/";
        $new_title = preg_replace($pattern,'',$title);

        $field_found = preg_match($pattern,$title,$matches);
        $new_value = ($field_found) ? preg_replace("/$key_pattern/",'',$matches[0]) : 0;
        if (($new_value > 0) && ((!is_numeric($current_field_value)||($current_field_value==0)))) {
            $my_post = array(
                'ID'           => $post_id,
                'post_title'   => $new_title.", $new_key $new_value",
//                    'post_content' => "<h2>$post_title</h2><h2>new title:$new_title<h2><p> new score:[$new_score]</p><p> score value:[[$score_value]]</p><p>update:[[[$upd]]]</p>",
                );
            remove_action('save_post','gy_update_score',60);
            wp_update_post( $my_post );
            add_action('save_post','gy_update_score',60,1);                
                
            $update = update_post_meta( $post_id, $field_name, $new_value);
            return $update;//('title'=>$new_title,'value'=>$new_field);
        } else {
            return null;
        }
}

/**************************/
function update_download_metas ($post_id,$worktype,$category,$parent_term_id,$append='false') {
    $comp_cat =   $worktype.' ב'.$category;
    $term_id = term_exists($comp_cat, 'complexcategory' );
        
    ob_start();
    var_dump($term_id);
    var_dump($category);
    $result = ob_get_clean();
    
    if ($term_id['term_id'] !==0 && $term_id['term_id'] !==null) {
        $cat_id = ($term_id['term_id']);
    } else {
        $new_cat = wp_insert_term(
            $comp_cat,   // the term 
            'complexcategory', // the taxonomy
            array(
                'parent'      => $parent_term_id,
                'description' => 'דף זה מכיל את כל העבודות ב'.$category.' מסוג '.$worktype.'.',
            )
        );
        
        $cat_id = ($new_cat->term_id);
    }
    
    wp_set_post_terms( $post_id,$cat_id,'complexcategory',$append);
        
    update_post_meta( $post_id, 'gy_cats', "($cat_id)(($comp_cat))((($parent_term_id)))".print_r($new_cat,true).print_r($term_id,true).$result);
}


/*********************************************/

function edd_add_download_filters_2() {
	global $typenow;

	// Checks if the current post type is 'download'
	if ( $typenow == 'download') {
		$terms = get_terms( 'worktype' );
		if ( count( $terms ) > 0 ) {
			echo "<select name='worktype' id='worktype' class='postform'>";
				$labels = edd_get_taxonomy_labels( 'worktype' );
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'easy-digital-downloads' ), strtolower( $labels['name'] ) ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_GET['worktype'] ) && $_GET['worktype'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}

		$terms = get_terms( 'complexcategory' );
		if ( count( $terms ) > 0) {
			echo "<select name='complexcategory' id='complexcategory' class='postform'>";
				$labels = edd_get_taxonomy_labels( 'complexcategory' );
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'easy-digital-downloads' ), strtolower( $labels['name'] ) ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_GET['complexcategory']) && $_GET['complexcategory'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}
	}

}
add_action( 'restrict_manage_posts', 'edd_add_download_filters_2', 100 );



/*************************************** not active *************************/

//add_filter( 'the_title','gy_create_complex_title',10,2);
function gy_create_complex_title( $title,$post_id ) {

	    if ((get_post_status($post_id)=='publish')&&(get_post_type($post_id) == 'download')){
            $worktype = gy_get_custom_tax($post_id,'worktype');
            if ($worktype) {
                $reg = '/ממ\"?|\”?ן/';
                $mm = 'ממ';
                $ns = 'ן';
                $reg = "/".$mm.'\"?|\”?'.$ns."/u";
                $has_maman = preg_match($reg,$title);
                if($has_maman && ($worktype=='ממ"ן')) {
                    $worktype = "*".$worktype."*";
                } else {
                    $worktype = "#$worktype#$has_maman#";
                }
            } else {
                $worktype= '##NSY##';
            }
            $score = get_post_custom_values('score',$post_id);
            $score = ($score!='') ? ' בציון '.$score[0] : '';
            $title.= ($worktype || $score) ? " (".$worktype.$score.")" : '';
//		    return $title."(".$worktype.$score.")";
	    }
		return $title;
} 


/****
 * add_action( 'save_post', 'gy_title_replace', 10, 3 );
function gy_title_replace( $post_id, $post ) {
    if ( (! wp_is_post_revision( $post_id )|| $post->post_title =="" )&& ( 'download' == get_post_type() )) {

        remove_action('save_post', 'gy_title_replace');

        if (($post->post_date == $post->post_modified ) &&( ! $post->post_title)) {
            global $_POST;
			$new_title_score = get_post_meta($post_id,'score', $post_id,true);
			$new_title_airline = get_post_meta($post_id,'airline_key', $post_id,true);
//			$new_title_reg = get_field('aircraft_registry_key', $post_id);
//            $new_title_airline = get_field('airline_key', $post_id);
            $registered_year = date("y");
			$count_posts = wp_count_posts('aircraft');
			$next_count = $count_posts->publish + 1;
			$new_count = sprintf("%04d", $next_count);
            $new_title = $new_title_airline.' '.$new_title_reg;
			
            $my_post = array(
                'ID'         => $post_id,
                'post_title' => $new_title,
                'post_name'  => $new_title_reg
            );
            wp_update_post( $my_post );
		 }
		 
/**********************************************************


function gy_update_score2($post_id) {
    if ( wp_is_post_revision( $post_id ) ) return;
    if (get_post_type($post_id) == 'download') {
        
        $post_title = get_the_title($post_id);


        $score = get_post_custom_values('score',$post_id);
        $score_value = is_array($score)? $score[0] : $score;
        $pattern = '/\sציון\s\d{2,3}/';
        $new_title = preg_replace($pattern,'',$post_title);
        $categories = gy_get_custom_tax($post_id,'download_category');
        $score_found = preg_match($pattern,$post_title,$matches);
        $new_score = ($score_found) ? preg_replace('/\sציון\s/','',$matches[0]) : 0;
        if ($new_score > 0) {
            if (!is_numeric($score_value)||($score_value==0)) {

                $my_post = array(
                    'ID'           => $post_id,
                    'post_title'   => $new_title,  // [** $new_score ** ## $score_value ##]",
                    'post_content' => "<h2>$post_title</h2><h2>new title:$new_title<h2><p> new score:[$new_score]</p><p> score value:[[$score_value]]</p><p>update:[[[$upd]]]</p>",
                );
                remove_action('save_post','gy_update_score',50);
                wp_update_post( $my_post );
                add_action('save_post','gy_update_score',50,1);                
                
                $upd= update_post_meta( $post_id, 'score', $new_score);


            } else {
                update_post_meta( $post_id, 'score', $score_value);
            }
        }
        if (!get_post_custom_values('original_title',$post_id)) update_post_meta( $post_id, 'original_title', $post_title);
        $worktype = gy_get_custom_tax($post_id,'worktype');
        $parent_term = term_exists( $worktype, 'complexcategory' ); // array is returned if taxonomy is given
        $parent_term_id = intval($parent_term['term_id']);         // get numeric term id
        
        if ( !empty( $categories ) ) {
            foreach ($categories as $category) { 
            update_dl_metas($post_id,$worktype,$category,$parent_term_id);
            }
        } else {
            $category = "לא מצאתי";
            update_dl_metas($post_id,$worktype,$category,$parent_term_id);
        }
    }
}


function gy_update_score_OLD($post_id) {
    if ( wp_is_post_revision( $post_id ) ) return;
    if (get_post_type($post_id) == 'download') {
        
        $post_title = get_the_title($post_id);
        if gy_strip_title($post_id,$post_title,$key_pattern,$value_pattern,$field_name) 
        $score = get_post_custom_values('score',$post_id);
        $score_value = is_array($score)? $score[0] : $score;
        $pattern = '/\sציון\s\d{2,3}/';
        $new_title = preg_replace($pattern,'',$post_title);

        $score_found = preg_match($pattern,$post_title,$matches);
        $new_score = ($score_found) ? preg_replace('/\sציון\s/','',$matches[0]) : 0;
        if ($new_score > 0) {
            if (!is_numeric($score_value)||($score_value==0)) {

                $my_post = array(
                    'ID'           => $post_id,
                    'post_title'   => $new_title,// [** $new_score ** ## $score_value ##]",
//                    'post_content' => "<h2>$post_title</h2><h2>new title:$new_title<h2><p> new score:[$new_score]</p><p> score value:[[$score_value]]</p><p>update:[[[$upd]]]</p>",
                );
                remove_action('save_post','gy_update_score',50);
                wp_update_post( $my_post );
                add_action('save_post','gy_update_score',50,1);                
                
                $upd= update_post_meta( $post_id, 'score', $new_score);


            } else {
                update_post_meta( $post_id, 'score', $score_value);
            }
        }
        if (!get_post_custom_values('original_title',$post_id)) update_post_meta( $post_id, 'original_title', $post_title);
        
        $worktype = gy_get_custom_tax($post_id,'worktype');
        if (!$worktype) $worktype = "עבודה";
        $categories = gy_get_custom_tax($post_id,'download_category','name','array');
        $parent_term = term_exists( $worktype, 'complexcategory' ); // array is returned if taxonomy is given
        $parent_term_id = intval($parent_term['term_id']);         // get numeric term id
        
        if (empty( $categories ) ) $categories = array('טרם נקבע');
        wp_set_post_terms( $post_id,'','complexcategory');
        foreach ($categories as $category) { 
            update_download_metas($post_id,$worktype,$category,$parent_term_id,true);
        }
    }
}
****/