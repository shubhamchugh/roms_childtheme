<?php
add_action( 'wp_enqueue_scripts', 'newsletter_child_theme_enqueue_styles' );
function newsletter_child_theme_enqueue_styles() {
	$parent_style = 'news-letter-parent-style';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );


    wp_enqueue_style( 'newsletter-child-style',
        get_stylesheet_directory_uri() . '/style.css?time='.time(),
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );

    wp_enqueue_style( 'fontawesome-child-style',
        'https://use.fontawesome.com/releases/v5.0.13/css/all.css?time='.time(),
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );

   wp_enqueue_script('jquery.countdown',  get_stylesheet_directory_uri(). '/js/jquery.countdown.js', array('jquery'), '1.0.0',true  );



}

//remove_filter('the_content', array('gdrts_addon_posts', 'content'), 11);

function gd_rating_function ($post){
	global $post, $_gdrts_addon_posts;

	$content = '';
	if(isset($_gdrts_addon_posts)){

		$item = gdrts_get_rating_item_by_post($post);

	    if ($item !== false) {
	        $post_type = $post->post_type;
	        $location = $item->get('posts-integration_location', 'default');
	        $method = $item->get('posts-integration_method', 'default');

	        if ($location == 'default') {
	            $location = $_gdrts_addon_posts->get($post_type.'_auto_embed_location');
	        }

	        if ($method == 'default') {
	            $method = $_gdrts_addon_posts->get($post_type.'_auto_embed_method');
	        }

	        $location = apply_filters('gdrts_posts_auto_embed_location', $location);
	        $_method = apply_filters('gdrts_posts_auto_embed_method', $method);
	        $_parts = explode('::', $_method, 2);
	        $method = $_parts[0];
	        $series = null;

	        if (isset($_parts[1])) {
	            $series = $_parts[1];
	        }

	        if (gdrts_is_method_loaded($method)) {
	            if (!empty($location) && is_string($location) && in_array($location, array('top', 'bottom', 'both'))) {
	                $rating = gdrts_posts_render_rating(array(
	                    'name' => $post_type,
	                    'id' => $post->ID,
	                    'method' => $method,
	                    'series' => $series
	                ));

	                if ($location == 'top' || $location == 'both') {
	                    $content = $rating.$content;
	                }

	                if ($location == 'bottom' || $location == 'both') {
	                    $content.= $rating;
	                }
	            }
	        }
	    }
	}

    return $content;
}


function roms_get_post_rating($pid){
	global $wpdb, $post;


	$check_rating_sql = "SHOW TABLES LIKE '{$wpdb->prefix}gdrts_items'";

	$check_rating_table = $wpdb->get_var($check_rating_sql);

	$total_rating = 0;

	if(isset($check_rating_table) && !empty($check_rating_table)){

		$sql =  "SELECT itemmeta.meta_value as total_rating FROM {$wpdb->prefix}gdrts_items as item INNER JOIN {$wpdb->prefix}gdrts_itemmeta as itemmeta On item.item_id=itemmeta.item_id WHERE itemmeta.meta_key='stars-rating_rating' AND item.id = {$pid}";

		$total_rating = $wpdb->get_var($sql);
		if(empty($total_rating))
		{
			$total_rating =  '0';
		}
	}

	return $total_rating.'/5';

}


function roms_search_category( $query ) {
    if ( $query->is_category() && $query->is_main_query() ) {
    	$query->set( 'posts_per_archive_page', 20);
    	//echo $_GET['search'];
    	if(isset($_GET['search']) && !empty($_GET['search'])){
    		$query->set( 's', $_GET['search']);
    	}

    	if(isset($_GET['genre']) && !empty($_GET['genre'])){
    		$query->set( 'meta_query', array(
		        array(
		              'key' => 'GENRE',
		              'value' => $_GET['genre'],
		              'compare' => 'LIKE'
		        )
		  ));
    	}

    	if(isset($_GET['region']) && !empty($_GET['region'])){
    		$query->set( 'meta_query', array(
		        array(
		              'key' => 'REGION',
		              'value' => $_GET['region'],
		              'compare' => 'LIKE'
		        )
		  ));
    	}

    	if(isset($_GET['genre']) && !empty($_GET['genre']) && isset($_GET['region']) && !empty($_GET['region'])){
    		$query->set( 'meta_query', array(
		         array(
		              'key' => 'Region',
		              'value' => $_GET['region'],
		              'compare' => 'LIKE'
		        ),
		        array(
		              'key' => 'Genre',
		              'value' => $_GET['genre'],
		              'compare' => 'LIKE'
		        )
		  ));
    	}

    	if(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'dowloads'){
    		//echo $_GET['orderby'];
    		$query->set( 'meta_key', 'Downloads');
    		$query->set( 'orderby', trim('meta_value_num'));
    	}

    	if(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'console'){
    		//echo $_GET['orderby'];
    		$query->set( 'meta_key', 'Console');
    		$query->set( 'orderby', trim('meta_value'));
    	}

    	if(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'platform'){
    		//echo $_GET['orderby'];
    		$query->set( 'meta_key', 'Platform-Value');
    		$query->set( 'orderby', trim('meta_value'));
    	}

    	if(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'name'){
    		$query->set( 'orderby', 'post_title');
    	}



    	if(isset($_GET['orderby']) && !empty($_GET['orderby']) && $_GET['orderby'] == 'rating'){
    		add_filter( 'posts_fields', 'roams_add_rating_field', 10);
    		add_filter( 'posts_orderby', 'roams_orderby_rating_field', 10);
    		//$query->set( 'orderby', 'rating');

    	}



    }
}
add_action( 'pre_get_posts', 'roms_search_category' );

function roams_add_rating_field($fields){

	global $wpdb;

	$fields.= ", (SELECT itemmeta.meta_value as total_rating FROM {$wpdb->prefix}gdrts_items as item INNER JOIN {$wpdb->prefix}gdrts_itemmeta as itemmeta On item.item_id=itemmeta.item_id WHERE itemmeta.meta_key='stars-rating_rating' AND {$wpdb->prefix}posts.ID = item.id) AS rating ";
	//print_r($fields);
	return $fields;
}

function roams_orderby_rating_field($orderby){

	global $wpdb;
	$order = $_GET['order'];
	return $orderby = " rating $order";
}

function uploadRemoteImageAndAttach($image_url, $post_id){

    $image = $image_url;

    $get = wp_remote_get( $image );

    $type = wp_remote_retrieve_header( $get, 'content-type' );

    if (!$type)
        return false;

    $mirror = wp_upload_bits( basename( $image ), '', wp_remote_retrieve_body( $get ) );
    print_r($mirror);
    if(isset($mirror) && !empty($mirror['url'])){
    	update_post_meta($post_id, '_roms_download_post_file', $mirror);
    	return 1;
    }
}

function download_roms_file($fileUrl, $post_id){

	$upload_dir = wp_upload_dir();

	//$fileUrl = urldecode($fileUrl);
	//$fileUrl = urldecode($fileUrl);
	//$fileUrl = str_replace(' ','',$fileUrl);
	$fileUrl = trim($fileUrl);

	$saveTo = $upload_dir['path'].'/'.basename($fileUrl);
	$file_url = $upload_dir['url'].'/'.basename($fileUrl);

	$fp = fopen($saveTo, 'w+');

	//If $fp is FALSE, something went wrong.
	if($fp === false){
	    throw new Exception('Could not open: ' . $saveTo);
	}

	//Create a cURL handle.
	$ch = curl_init($fileUrl);

	//Pass our file handle to cURL.
	curl_setopt($ch, CURLOPT_FILE, $fp);

	//Timeout if the file doesn't download after 20 seconds.
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);

	//Execute the request.
	curl_exec($ch);

	//If there was an error, throw an Exception
	if(curl_errno($ch)){
	    throw new Exception(curl_error($ch));
	}

	//Get the HTTP status code.
	$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//Close the cURL handler.
	curl_close($ch);

	if($statusCode == 200){
		$file = array('url' => $file_url, 'file' => $saveTo);
		update_post_meta($post_id, '_roms_download_post_file', $file);

		//$url='https://romsemulator.com/?testdonload=dd';
		//header("Refresh: 0; URL=$url");
		return true;
	} else{

		update_post_meta($post_id, '_roms_download_post_file_error', 'error');
		//$url='https://romsemulator.com/?testdonload=dd';
		//header("Refresh: 10; URL=$url");

		return false;

	}

}

//DOWNLOAD FILE FROM URL
add_action( 'admin_init', 'ny_roms_download_file_callback',99 );
function ny_roms_download_file_callback()
{
	global  $wpdb;

	$data_array = array();

	if(isset($_REQUEST['start_download']) && !empty($_REQUEST['start_download']) && $_REQUEST['start_download'] == 'start_download_file')
	{
		global $post, $wpdb;
		$post_meta = $wpdb->prefix.'postmeta';
		$post = $wpdb->prefix.'posts';

		$sql =  "SELECT posts.ID FROM {$post} as posts WHERE posts.post_type = 'post' AND posts.post_status = 'publish' AND NOT EXISTS (SELECT * FROM {$post_meta} as pm WHERE pm.meta_key = '_roms_download_post_file' AND pm.post_id=posts.ID) group by posts.ID LIMIT 0, 1";

		$posts = $wpdb->get_results($sql);
		//echo count($posts);

		if(!empty($posts))
		{
			foreach ($posts as $key => $dpost)
			{
				//echo $post->ID.'<br>';

			 	$file = get_post_meta($dpost->ID, 'Download-Links', true);

				if(isset($file) && !empty($file)){
					$data_array['file'] = $file;
					$data_array['dpost'] = $dpost->ID;

					$uploaded = download_roms_file($file,$dpost->ID);
					$data_array['uploaded'] = $uploaded;
					if($uploaded == 1){

						$data_array['dataprocess'] = 'done';
						$totaldownload = $_REQUEST['totaldownload'];
						$totaldownload = $totaldownload + 1;
						$data_array['totaldownload'] = $totaldownload;
					}
				}
			}
		}
		echo json_encode($data_array);
		die();
	}
}



//ADD MENU IN THE POST
//add_action('admin_menu', 'romas_add_pages');
// action function for above hook
function romas_add_pages() {
// Add a new submenu under Settings:
	add_submenu_page('edit.php', __('Import File'), __('Download File'), 'manage_options', 'roms-download-files', 'roms_import_download_file_page');
}

function roms_import_download_file_page()
	{
		global $post, $wpdb;
		$post_meta = $wpdb->prefix.'postmeta';
		$post = $wpdb->prefix.'posts';
		$sql = "SELECT COUNT(p.ID) FROM {$post} as p INNER JOIN {$post_meta} as pm ON p.ID = pm.post_id  WHERE pm.meta_key = '_roms_download_post_file' AND pm.meta_value != ''  AND p.post_status = 'publish' AND p.post_type='post' group by p.ID ";
		$totalimport = $wpdb->get_results($sql);


		 $sql2 = "SELECT p.ID FROM {$post} as p INNER JOIN {$post_meta} as pm ON p.ID = pm.post_id  WHERE pm.meta_key = 'Download-Links' AND pm.meta_value != ''  AND p.post_status = 'publish' AND p.post_type='post' group by p.ID ";
		 $total_files = $wpdb->get_results($sql2);




	?>
	<div class="wrap">
		<h1> Download File</h1>
		<?php if(isset($_SESSION['success'])){ ?>
			<p style="color:green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
		<?php } ?>
		<form method="post" action="" name="downloadfile" id="downloadfile">
			<input type="hidden" name="start_download" value="start_download_file">
			<table class="form-table">
				<tbody>

					<tr>
						<th scope="row"><label for="apikey">Download files</label></th>
					</tr>

					<tr>
						<th scope="row"><label for="apikey">Total Files (<?php echo count($total_files); ?>) and Import Files (<span id="totaldownload"><?php echo count( $totalimport ); ?></span>) </label></th>
					</tr>

					<tr>
						<th scope="row"><label for="apikey">Click button to download post file.</label></th>
					</tr>
					<tr>

						<td>
							<input type="hidden" id="totaldownloadfield" value="<?php echo count( $totalimport ); ?>">
							<input type="button"  data-import="" onclick="submit_download_filedd();" name="submit" id="submitbtn" class="button button-primary" value="Start Download">

							<image style="display:none;" id="loading" src="<?php echo get_stylesheet_directory_uri(); ?>/img/progress_bar.gif">
							<br>
							<span id="large_amount_data_uploaded_status"></span>

						</td>
					</tr>
				</tbody>
			</table>
		</form>

		<script type="text/javascript">
		function submit_download_filedd(){
			jQuery( "#loading" ).show();

			var totaldownload = jQuery('#totaldownloadfield').val();

			var data = {'action': 'ny_roms_download_file', 'totaldownload' : totaldownload, 'start_download' : 'start_download_file'};

			jQuery.post('<?php echo admin_url(); ?>',data,function(){}, 'json').done(function(response) {
				//jQuery( "#loading" ).hide();

				jQuery( '#totaldownload' ).text(response.totaldownload );
				jQuery('#totaldownloadfield').val(response.totaldownload )
				submit_download_filedd();
			}).fail(function() {
				jQuery('#large_amount_data_uploaded_status').text('Please Try again by Click on Start Download button.');
				return false;
			});
		}

	</script>

	</div>
	<?php
}

//DOWNLOAD FILE BY DOWNLOAD PAGE
add_action( 'wp', 'roms_download_file_from_post', 99 );
function roms_download_file_from_post()
{
	if(isset($_POST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] == 'roms_download_file')
	{
		global $post, $wpdb;
		if (isset( $_POST['roms_download_file_nonce_field'] ) && wp_verify_nonce( $_POST['roms_download_file_nonce_field'], 'roms_download_file_action' )
		) {

			$pid = $_POST['pid'];
			$pid =  base64_decode($pid);
			$file = get_post_meta($pid, 'Exact_File_Path', true);
			//print_r($file);
			if(isset($file) && !empty($file)){

				$file_url = $file;
				$file_name = end(explode('/', $file_url));
				$upload_dir = wp_upload_dir();

				$upload_file = WP_CONTENT_DIR.'/uploads/mntroms/Download_Files/'.urldecode($file_name);
				$downloads =  get_post_meta($pid, 'Downloads', true);
				$downloads = str_replace(",","",$downloads);
				$downloads++;

				if (file_exists($upload_file)) {

					update_post_meta($pid, 'Downloads', $downloads);

					header('Content-Type: application/octet-stream');
					header("Content-Transfer-Encoding: Binary");
					header("Content-disposition: attachment; filename=\"".$file_name."\"");
					readfile($upload_file);
			    	exit;
				}
			}
		}
	}
}


function roms_change_wp_title_callback( $title, $sep ) {
    global $paged, $page, $wp_query, $wpdb, $post;

    $id = $post->ID;

	if (!is_admin() && get_post_type( $id ) == 'post' && is_single() && $wp_query->is_main_query()) {

		$title = $post->post_title;
		$cats = array();
		foreach (get_the_category($id) as $c) {
			$cat = get_category($c);
			array_push($cats, $cat->name);
		}

		if(in_array('ROMs', $cats)){
			$Console = get_post_meta($id, 'Console', true);
			$changed_title = $title.' ROM | Free Download for '.$Console;
		}
		if(in_array('Emulators', $cats)){
			$console = get_post_meta($post->ID, 'Console', true);
			$platform = get_post_meta($post->ID, 'Platform-Value', true);
			$changed_title = 'Download '.$title.' '.$console.' Emulators | for '.$platform;
		}
		$site_title = get_bloginfo( 'name' );
		return $changed_title;
	}

	if (!is_admin() && is_category() && $wp_query->is_main_query())
	{

		$category_slug = $wp_query->query['category_name'];
		$category_slug = explode('/', $category_slug);
		$main_cat = $category_slug[0];

		$name = $wp_query->queried_object->name;

		if(ucfirst($main_cat) != $name){
			$changed_title = $name.' | Free Download | Romesemulator.com ';
		}else{
			$changed_title = $name.' | Free Download | Romesemulator.com ';
		}


		$site_title = get_bloginfo( 'name' );
		return $changed_title;
	}
    return $title;
}
add_filter( 'wp_title', 'roms_change_wp_title_callback', 99, 2 );
