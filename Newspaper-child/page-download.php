<?php
/* Template Name: Page Download */


get_header();

td_global::$current_template = 'page-overlay';
//set the template id, used to get the template specific settings
$template_id = 'page';


$loop_sidebar_position = td_util::get_option('tds_' . $template_id . '_sidebar_pos'); //sidebar right is default (empty)

//get theme panel variable for page comments side wide
$td_enable_or_disable_page_comments = td_util::get_option('tds_disable_comments_pages');


//read the custom single post settings - this setting overids all of them
$td_page = td_util::get_post_meta_array($post->ID, 'td_page');
if (!empty($td_page['td_sidebar_position'])) {
    $loop_sidebar_position = $td_page['td_sidebar_position'];
}

// sidebar position used to align the breadcrumb on sidebar left + sidebar first on mobile issue
$td_sidebar_position = '';
if($loop_sidebar_position == 'sidebar_left') {
    $td_sidebar_position = 'td-sidebar-left';
}



/**
 * detect the page builder
 */
$td_use_page_builder = td_global::is_page_builder_content();


if ($td_use_page_builder) {

    // the page is rendered using the page builder template (no sidebars)
    if (have_posts()) { ?>
        <?php while ( have_posts() ) : the_post(); ?>

            <div class="td-main-content-wrap td-main-page-wrap td-container-wrap">
                <div class="tdc-content-wrap">
                    <div <?php if (!td_util::tdc_is_installed()) { echo 'class="td-container"'; } ?>>
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php
                if($td_enable_or_disable_page_comments == 'show_comments') {
                    ?>
                    <div class="td-container">
                        <div class="td-pb-row">
                            <div class="td-pb-span12">
                                <?php comments_template('', true); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div> <!-- /.td-main-content-wrap -->


        <?php endwhile; ?>
    <?php }
} else {

    //no page builder detected, we load a default page template with sidebar / no sidebar
    ?>

    <div class="td-main-content-wrap td-container-wrap">
        <div class="td-container tdc-content-wrap <?php echo $td_sidebar_position; ?>">
            <div class="td-crumb-container">
                <?php echo td_page_generator::get_page_breadcrumbs(get_the_title()); ?>
            </div>
            <div class="td-pb-row">
                <?php
                switch ($loop_sidebar_position) {
                    default:
                            $pid = $_GET['pid'];
                            $pid =  base64_decode($pid);
                            $catname = '';
                            foreach (get_the_category($pid) as $c) {
                                $cat = get_category($c);
                                if($cat->slug == 'roms'){
                                     $catname = 'Rom';
                                }elseif($cat->slug == 'emulators'){
                                     $catname = 'Emulator';
                                }elseif($cat->slug == 'bioses'){
                                     $catname = 'Emulator';
                                }
                            }
                        ?>
                        <div class="td-pb-span8 td-main-content" role="main">
                            <div class="td-ss-main-content">
                               <div class="wait">
                                    <p>
                                        <i class="fa fa-download fa-2x"></i>
                                    </p>
                                    <h2>YOUR <?php echo $catname; ?> IS DOWNLOADING... </h2>
                                    <p>Your download will start in <strong><span class="countdown-callback ended">0 second(s)</span></strong>. Please Run/Keep the file when it's finished downloading.</p>
                                    <p>Problems with the download? Please, click the button.</p>
                                    <p class="wait__text">
                                        <a href="javascript:void(0);" rel="nofollow" id="download_link" class="btn is-with-ico">
                                            <span class="btn__left"> 
                                                <i class="ico is-arrow-down">
                                                <span class="line"></span>
                                                </i>
                                            </span>
                                            <span class="btn__right">
                                                Download
                                            </span>
                                        </a>
                                        <form style="display: none;" id="frm_download" method="POST" action="">
                                            <input type="text" name="action" value="roms_download_file">
                                            <input type="text" name="pid" value="<?php echo  $_GET['pid']; ?>">
                                            <?php wp_nonce_field( 'roms_download_file_action', 'roms_download_file_nonce_field' ); ?>
                                        </form>
                                    </p>
                                </div>
                                </div>
                                <?php
                                if($td_enable_or_disable_page_comments == 'show_comments') {
                                    comments_template('', true);
                                }?>
                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar" role="complementary">
                            <div class="td-ss-main-sidebar">
                                <?php //get_sidebar(); ?>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'sidebar_left':
                        ?>
                        <div class="td-pb-span8 td-main-content <?php echo $td_sidebar_position; ?>-content" role="main">
                            <div class="td-ss-main-content">
                                <?php

                                if (have_posts()) {
                                while ( have_posts() ) : the_post();
                                ?>
                                <div class="td-page-header">
                                    <h1 class="entry-title td-page-title">
                                        <span><?php the_title() ?></span>
                                    </h1>
                                </div>
                                <div class="td-page-content">
                                    <?php
                                    the_content();
                                    endwhile; //end loop
                                    }
                                    ?>
                                </div>
                                <?php
                                if($td_enable_or_disable_page_comments == 'show_comments') {
                                    comments_template('', true);
                                }?>
                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar" role="complementary">
                            <div class="td-ss-main-sidebar">
                                <?php get_sidebar(); ?>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'no_sidebar':
                        ?>
                        <div class="td-pb-span12 td-main-content" role="main">

                            <?php
                            if (have_posts()) {
                            while ( have_posts() ) : the_post();
                            ?>
                            <div class="td-page-header">
                                <h1 class="entry-title td-page-title">
                                    <span><?php the_title() ?></span>
                                </h1>
                            </div>
                            <div class="td-page-content">
                                <?php
                                the_content();
                                endwhile; //end loop
                                }
                                ?>
                            </div>
                            <?php
                            if($td_enable_or_disable_page_comments == 'show_comments') {
                                comments_template('', true);
                            }?>
                        </div>
                        <?php
                        break;
                }
                ?>
            </div> <!-- /.td-pb-row -->
        </div> <!-- /.td-container -->
    </div> <!-- /.td-main-content-wrap -->

    <?php
}
get_footer();
?>
<script type="text/javascript">
    jQuery(window).load(function(){
        start_timer();
    });
    function start_timer() { 
        var timer_time = 25000; 
        jQuery('.countdown-callback').countdown({ 
            date: +(new Date) + timer_time, 
            render: function(data) { 
                jQuery(this.el).text(data.sec + " second(s)"); 
            }, 
            onEnd: function() { 
                jQuery(this.el).addClass('ended'); 
            } 
        }); 
        setTimeout(function(){
            jQuery('.wait__text').fadeIn(300);
            <?php            
            $file = get_post_meta($pid, 'Exact_File_Path', true);            
            if(isset($file) && !empty($file)){ ?>
            jQuery('#frm_download').submit();
            <?php } ?>
        }, timer_time);
    }

    jQuery(document).ready(function(){
        jQuery('#download_link').click(function(){
            jQuery('#frm_download').submit();
        });
    });
</script>