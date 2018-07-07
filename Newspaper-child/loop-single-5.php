<?php
/**
 * single Post template 5
 **/

if (have_posts()) {
    the_post();
    global $post;
    $td_mod_single = new td_module_single($post);


    ?>

    <article id="post-<?php echo $td_mod_single->post->ID;?>" class="<?php echo join(' ', get_post_class());?>" <?php echo $td_mod_single->get_item_scope();?>>
        <div class="td-post-header">

            <?php echo $td_mod_single->get_category(); ?>

            <header class="td-post-title">
                <?php echo $td_mod_single->get_title();?>


                <?php if (!empty($td_mod_single->td_post_theme_settings['td_subtitle'])) { ?>
                    <p class="td-post-sub-title"><?php echo $td_mod_single->td_post_theme_settings['td_subtitle'];?></p>
                <?php } ?>


                <div class="td-module-meta-info">
                    <?php echo $td_mod_single->get_author();?>
                    <?php echo $td_mod_single->get_date(false);?>
                    <?php echo $td_mod_single->get_comments();?>
                    <?php echo $td_mod_single->get_views();?>
                </div>

            </header>

        </div>

        <?php echo $td_mod_single->get_social_sharing_top();?>


        <div class="td-post-content">

            <div class="post-details">
            <div class="post-left">
                <?php
            the_post_thumbnail( 'full' );
            ?>
             <?php echo $td_mod_single->get_social_sharing_bottom();?>
            </div>

             <?php 
             ob_start();
             echo gd_rating_function ($post); 
             $rating = ob_get_clean();
             ?>

            <div class="post-right">
                <div class="post-info"><label>Emulator:</label> <?php the_title(); ?></div>
                <div class="post-info"><label>User rating:</label> <?php echo gd_rating_function ($post);  ?></div>
                <?php 
                $meta_keys = array('File-size', 'Genre', 'Region', 'Console', 'Platform-Value', 'Home Page', 'Downloads');

                if(isset($meta_keys) && !empty($meta_keys)){

                    foreach ($meta_keys as $key => $meta_key) {
                        $meta_value = get_post_meta($post->ID, $meta_key, true);
                        if(!empty( $meta_value)){
                        ?>
                            <div class="post-info"><label><?php echo $meta_key; ?>: </label>
                                <?php if($meta_key == 'Home Page'){ ?>
                                        <a href="<?php echo $meta_value; ?>" rel="nofollow" class="product__link">
                                            <?php echo $meta_value; ?>
                                        </a>
                                    <?php }else{ ?>
                                        <?php echo $meta_value; ?>
                                    <?php } ?>
                            </div>                        
                            <?php 
                            }
                        }
                    }
                ?>
                <?php            
                $file = get_post_meta($post->ID, 'Exact_File_Path', true);            
                if(isset($file) && !empty($file)){ ?>
                 <a href="<?php echo add_query_arg('pid', base64_encode($post->ID), get_permalink(get_page_by_path('download'))); ?>" rel="nofollow" id="download_link" class="btn nn is-with-ico">
                
               

                <span class="btn__left">
                <i class="ico is-arrow-down">
                <span class="line"></span>
                </i>
                </span>

                <span class="btn__right">
                Download
                </span>
                </a>
                <?php } ?>
            </div>
            </div>
            <?php //echo $td_mod_single->get_content();?>
        </div>


       <?php /* <footer>
            <?php echo $td_mod_single->get_post_pagination();?>
            <?php echo $td_mod_single->get_review();?>

            <div class="td-post-source-tags">
                <?php echo $td_mod_single->get_source_and_via();?>
                <?php echo $td_mod_single->get_the_tags();?>
            </div>

            <?php echo $td_mod_single->get_social_sharing_bottom();?>
            <?php echo $td_mod_single->get_next_prev_posts();?>
            <?php echo $td_mod_single->get_author_box();?>
            <?php echo $td_mod_single->get_item_scope_meta();?>
        </footer> */?>

    </article> <!-- /.post -->

    <?php echo $td_mod_single->related_posts();?>

<?php
} else {
    //no posts
    echo td_page_generator::no_posts();
}