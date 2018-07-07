<?php
/**
 * If you are looking for the loop that's handling the single post page (single.php), check out loop-single.php
 **/


// $global_flag_to_hide_no_post_to_display - comes from page-category-big-grid.php and is a flag to hide the 'No posts to display' message if on category page there are between 1 and 5  posts
global $loop_module_id, $loop_sidebar_position, $global_flag_to_hide_no_post_to_display;

///if we are in wordpress loop; used by quotes in blocks to check if the blocks are displayed in blocks or in loop
td_global::$is_wordpress_loop = true;

$td_template_layout = new td_template_layout($loop_sidebar_position);

if (empty($loop_module_id)) {  //not sure if we need a default here
    $loop_module_id = 1;
}

$td_module_class = td_api_module::_helper_get_module_class_from_loop_id($loop_module_id);


//disable the grid for some of the modules
$td_module = td_api_module::get_by_id($td_module_class);
if ($td_module['uses_columns'] === false) {
    $td_template_layout->disable_output();
}

global $category, $wp_query, $wpdb;

$category_name = $wp_query->query['category_name'];
$category_name = explode('/', $category_name);
$main_cat = $category_name[0];
//print_r($category_name);

/*echo '<pre>';
print_r($wp_query);
echo '</pre>';*/

$cureent_cat =  get_query_var('category_name' ); 


$post_table = $wpdb->prefix.'posts';
$post_meta = $wpdb->prefix.'postmeta';
$terms_table = $wpdb->prefix.'terms';
$term_taxonomy_table = $wpdb->prefix.'term_taxonomy';
$term_relationships_table = $wpdb->prefix.'term_relationships';

$sql = "SELECT DISTINCT pm.meta_value as genre  FROM {$post_meta} as pm INNER JOIN {$post_table} as p ON p.ID=pm.post_id INNER JOIN {$term_relationships_table} as tr ON tr.object_id=p.ID INNER JOIN {$term_taxonomy_table} as tx ON tx.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$terms_table} as trm ON trm.term_id=tx.term_id WHERE p.post_type ='post' AND p.post_status='publish' AND tx.taxonomy='category' AND trm.slug='{$cureent_cat}' AND pm.meta_key='GENRE' ORDER BY genre ASC ";

$total_genres = $wpdb->get_results($sql);

$regionsql = "SELECT DISTINCT pm.meta_value as region  FROM {$post_meta} as pm INNER JOIN {$post_table} as p ON p.ID=pm.post_id INNER JOIN {$term_relationships_table} as tr ON tr.object_id=p.ID INNER JOIN {$term_taxonomy_table} as tx ON tx.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$terms_table} as trm ON trm.term_id=tx.term_id WHERE p.post_type ='post' AND p.post_status='publish' AND tx.taxonomy='category' AND trm.slug='{$cureent_cat}' AND pm.meta_key='REGION' ORDER BY region ASC ";

$total_regions = $wpdb->get_results($regionsql);

//echo $wp_query->request;

//$wpdb->show_errors;
 //$wpdb->print_error();

//print_r($total_genres);
if(empty($total_rating))
{
    $total_rating =  '0';
}

 $current_category = get_query_var( 'category_name');
 $cterm = get_term_by('slug', $current_category, 'category');
?>

<div class="section__left">

    <?php if($cterm->parent == '0'){ ?>

        <div class="search">
            <form action="<?php //echo get_category_by_slug( $main_cat ); ?>" id="search-frm" method="GET">

                <div class="search i-mb-10">
                    <i class="ico is-search i-left"></i>
                    <input type="search" name="tsearch" class="input" value="<?php if(isset( $_GET['tsearch'])){ echo $_GET['tsearch']; } ?>" placeholder="Search">
                </div><!-- END search -->          
                
            </form>
        </div>

        <table class="table  <?php  echo $main_cat; ?>-main">
            <thead>
                <tr>

                    <th>
                        <?php _e('Console'); ?>
                        <span class="sorter" data-field="console" data-desc="1">
                        <a href="?orderby=tname&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fa fa-sort"></i></a>
                        </span>
                    </th>

                    <th>
                        <?php  _e('Total Roms');  ?>
                        <span class="sorter" data-field="name">
                            <a href="?orderby=roms&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fas fa-sort"></i></a>
                        </span>
                    </th>
                    
                     <th>
                        <?php  _e('Total Downloads');  ?>
                        <span class="sorter" data-field="name">
                            <a href="?orderby=tdownload&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fas fa-sort"></i></a>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $term = get_term_by('slug', $current_category, 'category');

                    $tsearchsql = '';
                    if(isset($_GET['tsearch'])){
                        $tsearch = $_GET['tsearch'];
                        $tsearchsql = " AND trm.name LIKE '%$tsearch%'";
                    }

                    $ordersql = '';

                    if(isset($_GET['order']) && !empty($_GET['order'])){
                         $order = $_GET['order'];
                    }else{
                         $order = 'ASC';
                    }

                    if(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'roms'){
                         $ordersql = " ORDER BY pcount  $order ";
                    }elseif(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'tdownload'){
                         $ordersql = " ORDER BY total_downloads  $order ";
                    }else{
                         $ordersql = " ORDER BY trm.name $order ";
                    }

                    $tid = $term->term_id;

                    $termssql = "SELECT trm.*, tx.count AS pcount, sum(pm.meta_value) as total_downloads FROM {$post_meta} as pm INNER JOIN {$post_table} as p ON p.ID=pm.post_id INNER JOIN {$term_relationships_table} as tr ON tr.object_id=p.ID INNER JOIN {$term_taxonomy_table} as tx ON tx.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$terms_table} as trm ON trm.term_id=tx.term_id WHERE p.post_type ='post' AND p.post_status='publish' AND pm.meta_key='Downloads' AND tx.parent='{$tid}' AND tx.taxonomy='category' $tsearchsql GROUP BY trm.term_id  $ordersql ";


                    $categories = $wpdb->get_results($termssql);

                    if ($categories) {
                        foreach ($categories as $key => $category) {

                            $catslug = $category->slug;
                            $downloadsql = "SELECT DISTINCT sum(pm.meta_value)  FROM {$post_meta} as pm INNER JOIN {$post_table} as p ON p.ID=pm.post_id INNER JOIN {$term_relationships_table} as tr ON tr.object_id=p.ID INNER JOIN {$term_taxonomy_table} as tx ON tx.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$terms_table} as trm ON trm.term_id=tx.term_id WHERE p.post_type ='post' AND p.post_status='publish' AND tx.taxonomy='category' AND trm.slug='{$catslug}' AND pm.meta_key='Downloads' ";

                            $total_downloads = $wpdb->get_var($downloadsql);
                           ?>
                           <tr>
                                <td><a href="<?php echo get_category_link( $category->term_id ); ?>"><?php echo $category->name; ?></a></td>
                                <td><?php echo $category->pcount; ?></td>
                                <td><?php echo $total_downloads; ?></td>
                            </tr>
                           <?php 
                        }
                    } 
                ?>                   
            </tbody>
        </table>

     <?php }else{ ?>

         <div class="search">
            <form action="<?php //echo get_category_by_slug( $main_cat ); ?>" id="search-frm" method="GET">

                <div class="search i-mb-10  <?php  if($main_cat == 'roms'){ ?>wide-input<?php  } ?>">
                    <i class="ico is-search i-left"></i>
                    <input type="search" name="search" class="input" value="<?php if(isset( $_GET['search'])){ echo $_GET['search']; } ?>" placeholder="Search">
                </div><!-- END search -->

                <?php  if($main_cat == 'roms'){ ?>
                    <div class="select i-mb-10 search is-left-10">
                        <select name="genre" onchange="jQuery('#search-frm').submit();">
                            <option value="">Select Genre</option>
                            <?php 
                            if(!empty($total_genres))
                            {
                                foreach ($total_genres as $key => $total_genre) {

                                 $selected = '';
                                 if(isset( $_GET['genre']) && $_GET['genre'] == $total_genre->genre){  $selected = 'selected'; }
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $total_genre->genre; ?>"><?php echo $total_genre->genre; ?></option>
                                <?php 
                                }
                            }
                            ?>                   
                        </select>
                    </div>

                    <div class="select i-mb-10 search is-left-10">
                        <select name="region"  onchange="jQuery('#search-frm').submit();">
                            <option value="">Select Region</option>
                            <?php 
                            if(!empty($total_regions))
                            {
                                foreach ($total_regions as $key => $total_region) {

                                 $selected = '';
                                 if(isset( $_GET['region']) && $_GET['region'] == $total_region->region){  $selected = 'selected'; }
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $total_region->region; ?>"><?php echo $total_region->region; ?></option>
                                <?php 
                                }
                            }
                            ?>                    
                        </select>
                    </div>

                <?php  } ?>
                
            </form>
        </div>

        <table class="table  <?php  echo $main_cat; ?>">
            <thead>
                <tr>
                    <th>
                        <?php  if($main_cat == 'emulators'){ _e('File Name'); }else{ _e('Game Title'); } ?>
                        <span class="sorter" data-field="name">
                            <a href="?orderby=name&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fas fa-sort"></i></a>
                        </span>
                    </th>

                    <?php if($main_cat == 'emulators'){ ?>
                        <th>
                            <?php _e('Console'); ?>
                            <span class="sorter" data-field="rating" data-desc="1">
                            <a href="?orderby=console&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fa fa-sort"></i></a>
                            </span>
                        </th>
                        <th>
                            <?php _e('Platform'); ?>
                            <span class="sorter" data-field="rating" data-desc="1">
                            <a href="?orderby=platform&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fa fa-sort"></i></a>
                            </span>
                        </th>
                    <?php } ?>
                    <th>
                        
                        <?php _e('Rating'); ?>
                        <span class="sorter" data-field="rating" data-desc="1">
                        <a href="?orderby=rating&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fa fa-sort"></i></a>
                        </span>
                    </th>
                    <th>                    
                        <?php _e('Dowloads'); ?>
                        <span class="sorter" data-field="downloads" data-desc="1">
                        <a href="?orderby=dowloads&order=<?php if(isset( $_GET['order']) && $_GET['order']== 'ASC'){ echo 'DESC'; }else{  echo 'ASC';; } ?>"><i class="fa fa-sort"></i></a>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    if (have_posts()) {
                        while ( have_posts() ) : the_post();
                           ?>
                           <tr>
                                <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                                 <?php if($main_cat == 'emulators'){ ?>
                                    <td><?php echo get_post_meta($post->ID,'Console', true); ?></td>
                                    <td><?php echo get_post_meta($post->ID,'Platform-Value', true); ?></td>
                                <?php } ?>
                                <td><?php echo roms_get_post_rating($post->ID); ?></td>
                                <td><?php echo get_post_meta($post->ID,'Downloads', true); ?></td>
                            </tr>
                           <?php 
                       endwhile;

                    } else {
                        /**
                         * no posts to display. This function generates the __td('No posts to display').
                         * the text can be overwritten by the themplate using the global @see td_global::$custom_no_posts_message
                         */

                        echo td_page_generator::no_posts();
                    }

                ?>                   
            </tbody>
        </table>
     <?php } ?>
</div>
        