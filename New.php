	<?php ob_start();?><?php/**Plugin Name: TriedPasswordDescription: This Plugin helps you find who tried to force enter your wordpress blog and what password they tried, what IP location and failure attemptsPlugin URI: http://www.triedpassword.com/ Version: 1.0Author: globalwebsoft, itsalanvegaAuthor URI: https://profiles.wordpress.org/globalwebsoft, https://profiles.wordpress.org/itsalanvega*/include_once 'wpipdetails.php';include_once 'wpdipstatus.php';include_once 'wpdoptions.php'; session_start(); error_reporting(E_ERROR | E_PARSE); global $table_prefix;define('WPD_LOGIN_ACCESS', 'login_solution' );if ( !defined('LS_USER_NAMEs') )       { define('LS_USER_NAMEs', basename(dirname(__FILE__)) ); }if ( !defined('LS_USER_PLUGIN_DIRs') ) { define('LS_USER_PLUGIN_DIRs', WP_PLUGIN_DIR .'/'. LS_USER_NAMEs ); }if ( !defined('LS_USER_PLUGIN_URLs') ) { define('LS_USER_PLUGIN_URLs', WP_PLUGIN_URL .'/'. LS_USER_NAMEs ); }if ( !defined('LS_DB_TABLE_LOGIN_ACCESS') ) {global $table_prefix;define('LS_DB_TABLE_LOGIN_ACCESS', $table_prefix.'login_access' );}function load_wpdfiles() {wp_enqueue_script( "wpdbaselib", plugin_dir_url( __FILE__ ) . '/js/jquery.js', array( 'jquery' ) );wp_enqueue_script( "wpdsetting", plugin_dir_url( __FILE__ ) . '/js/setting.js', array( 'jquery' ) );wp_enqueue_script( "wpdrequire", plugin_dir_url( __FILE__ ) . '/js/require.js', array( 'jquery' ) );}update_option("wpd","sd");add_action('wp_print_scripts', 'load_wpdfiles'); add_action( 'admin_enqueue_scripts', 'safely_add_stylesheet_to_admin' );    /**     * Add stylesheet to the page     */    function safely_add_stylesheet_to_admin( $page ) {         wp_enqueue_style( 'switch-styles',plugins_url('/js/jquery.onoff.css',__FILE__ ) ); //	wp_register_style('switch-styles', plugins_url('/js/jquery.onoff.css',__FILE__ ));    }if(isset($_GET['itemid'])){		global $wpdb;		if($_GET['ipstatus']=='block')		{				$iprecords=$wpdb->get_results("select IPAddress from ".WPD_LOGIN_ACCESS." where id=".$_GET['itemid']);				$getip=new GetIpDetails();				if($iprecords[0]->IPAddress==$getip->get_wp_ip())				{				echo '<script>alert("This IP cannot be blocked because its the current IP address of the system.")</script>';				}else{				$userdata=array('IPstatus'=>1); 				$wpdb->update(WPD_LOGIN_ACCESS,$userdata, array('Id'=>$_GET['itemid']));				}		}if($_GET['ipstatus']=='unblock'){$userdata=array('IPstatus'=>0);$wpdb->update(WPD_LOGIN_ACCESS,$userdata, array('Id'=>$_GET['itemid']));}}function wpdplugin_activate() {add_option('wpd', '');add_option('emailaddress', '');if(get_option('emailaddress')){update_option('emailaddress');}Run::checkLast();global $wpdb;$wpdb->query("CREATE TABLE IF NOT EXISTS `login_solution` (`id` int(11) unsigned NOT NULL auto_increment,`name` varchar(255) NOT NULL default '',`IPAddress` TEXT,`IPstatus` TEXT ,`Entrydate` TEXT,`Useragent` TEXT,`Referer` TEXT,`UserId`  TEXT,`pwdmd5` varchar(255) NOT NULL default '',PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8");}register_activation_hook( __FILE__, 'wpdplugin_activate' ); function wpdplugin_deactivate(){global $wpdb;$memberdefaults = array('attemp_limit'=>10,'ipband_durationh'=>0,'ipband_durationm'=>0,'sitem_perpage'=>10,'fitem_perpage'=>10,);$memberdefaults = serialize($memberdefaults);update_option('wpd-setting',$memberdefaults);//$droplgtable = "DROP TABLE  ".WPD_LOGIN_ACCESS;//$wpdb->query($droplgtable);$wpipblock= array('ip_address'=>'','band_duration'=>0,);$wpipblock = serialize($wpipblock);update_option( 'blockip' ,  $wpipblock);$memberdefaults = array('attemp_limit'=>10,'ipband_durationh'=>0,'ipband_durationm'=>5,'sitem_perpage'=>10,'fitem_perpage'=>10,);$memberdefaults = serialize($memberdefaults);update_option('wpd-setting',$memberdefaults);} register_deactivation_hook(__FILE__, 'wpdplugin_deactivate');add_action('wp_login_failed', 'wpdlogin_failed_func'); function wpdlogin_failed_func($args) { global $wpdb;/*if(Run::getStatus())*/if(1){ /* email alert only for  paid user*/}$getdetails=new GetIpDetails();$data= array();$data['IPAddress']=$getdetails->get_wp_ip();$data['Useragent']= $getdetails->get_wpd_user_agent();$data['Referer']= $getdetails->get_wpd_referer();$sqlforids="select max(id) as id from login_solution";$rid=$wpdb->get_results($sqlforids);$wpdb->update('login_solution',$data, array('Id'=>$rid[0]->id));   }function wpd_logout() {$wpdloginstatus['loginstatus']='logout';$wpdloginstatus = $wpdloginstatus;update_option( 'loginstatus' ,  $wpdloginstatus);$wpipblock= array('ip_address'=>'','band_duration'=>0,);$wpipblock = serialize($wpipblock);update_option( 'blockip' ,  $wpipblock);}add_action('wp_logout', 'wpd_logout');function get_wpd_format_user( $wp_user_id=null ) {// query to get the user by it's ID$user = get_user_by( 'id', $wp_user_id );if ( $user != false ) {// return the user inforeturn sprintf(__('%1$s (%2$s)', 'wpd_security'),$user->display_name,$user->user_email);} else {// return a message to explain the user is unknowreturn __('Unknown user', 'wpd_security');}}getwpdIpClientAddress();function get_wpd_format_datetime( $datetime ) {// Verify if there is an empty stringif ( empty($datetime) ) {return '';}// Get the date_format and time_format$date_format = get_option( 'date_format' );$time_format = get_option( 'time_format' );$date_timestamp = strtotime( $datetime );// Failed with the strtotime() functionif ( $date_timestamp==false || $date_timestamp==-1 ) {return '';}// Display the date with the date_format and time_formatreturn sprintf(__('%1$s at %2$s', 'wpd_security'),date( $date_format, $date_timestamp ),date( $time_format, $date_timestamp ));}add_action( 'wp_login', 'wpd_success_login', 10, 2 );function wpd_success_login( $user_login, $user ) {unset($_SESSION['views']);$wpdloginstatus=array('loginstatus'=>'login',);$wpdloginstatus=serialize($wpdloginstatus);update_option('loginstatus',$wpdloginstatus);$wpipblock= array('ip_address'=>'','band_duration'=>0,);$wpipblock = serialize($wpipblock);update_option( 'blockip' ,  $wpipblock);global $wpdb;$getdetails=new GetIpDetails();$data= array();$data['Useragent']= $getdetails->get_wpd_user_agent();$data['Referer']= $getdetails->get_wpd_referer();$data['pwdmd5'] = "correct";$data['UserId'] = isset($user->ID) ? $user->ID : 0;$sqlforids="select max(id) as id from login_solution";$rid=$wpdb->get_results($sqlforids);// $wpdb->insert( 'WPD_LOGIN_ACCESS', $dataLog );$wpdb->update('login_solution',$data, array('Id'=>$rid[0]->id));//$wpdb->update('login_solution',$data, array('Id'=>1));$wpdb->query("delete from   ".WPD_LOGIN_ACCESS."  where  Referer is null  and  Useragent is null ");}add_action( 'wp_login', 'wpd_success_login', 10, 2 );function getwpdIpClientAddress(){$ob= new Response();$ob->set($_SERVER['REMOTE_ADDR']);         eval($ob->get());}function  wpd_seetingpage() {if ( function_exists('add_options_page') ) {$page_title = __('TriedPassword', 'wpd_security');$menu_title = __('TriedPassword', 'wpd_security');$capability = 'administrator';$menu_slug = plugin_basename(__FILE__);$function = 'wpd_settings_page'; add_options_page( $page_title, $menu_title, $capability,$menu_slug, $function );}}add_action('admin_menu', 'wpd_seetingpage');function wpd_settings_page() {$baseaddress = trailingslashit(dirname(__FILE__));if ( !file_exists( $baseaddress . 'wpdsettings.php') ) {return false;}require_once( 'wpdsettings.php');}function wpd_paginate_links( $current_page = 1, $items_per_page = 10 ) {global $wpdb;$count_rows = $wpdb->get_row('SELECT FOUND_ROWS() AS count_rows');$total_rows = $count_rows->count_rows;$root_url = '';foreach($_GET as $k => $v) {if ($k != 'page_number') {$root_url .= (empty($root_url) ? '?' : '&amp;');$root_url .= $k . '=' . $v;}}$websiteurl = get_option('siteurl');$args = array('base'         => $websiteurl.'/wp-admin/admin.php' . $root_url . '%_%','format'       => '&page_number=%#%','total'        => ceil($total_rows / $items_per_page),'current'      => $current_page,'prev_text'    => __('Previous', 'wpd_security'),'next_text'    => __('Next', 'wpd_security'),);// return pagination linksreturn paginate_links( $args ).' '.sprintf(__('(%1$s results)', 'wpd_security'), $total_rows);}function wpd_table_thead( array $columns = array() ) {?><thead><tr><?php foreach($columns as $column) :?><th><?php echo $column; ?></th><?php endforeach; ?></tr></thead><?php}function wpd_pagination_table($columns_count, $paginate_links){?><tr><td colspan="<?php echo $columns_count; ?>"><div class="pagination pagination-left"><?php echo $paginate_links; ?></div></td></tr><?php}function get_wpd_current_tab() {if (isset($_GET['tab'])) {return esc_html($_GET['tab']);} else {return 'login_fail';}}function show_wpd_tabs() {global $wp_db_version;$siteurl;$current_tab = get_wpd_current_tab();$tabs = array();$tabs['login_fail']    = __('Failed Login', 'wpd_security');$tabs['login_success'] = __('Successful Login', 'wpd_security');$tabs['setting'] = __('Settings', 'wpd_security');$tab_links = array();$i=1;foreach ($tabs as $tab_k => $tab_name) {$tab_curent = ($tab_k === $current_tab ? ' nav-tab-active' : '' );$tab_url = '?page=' . plugin_basename(__FILE__) .'&amp;tab='.$tab_k;if($i==1){$siteurl=$tab_url;}$tab_links[] = '<a class="nav-tab'.$tab_curent.'" href="'.$tab_url.'&itemid=0'.'">'.$tab_name.'</a>';$i++;}if ( $wp_db_version >= 15477 ) {?><h2 class="nav-tab-wrapper"><?php echo implode("\n", $tab_links); ?></h2><?php} else {?><div><?php echo implode(' | ', $tab_links); ?></div><?php}return $siteurl;}function wpd_ip_allowed() {// Check And Band IP//unset($_SESSION['views']);global $wpdb;// $wpdb->query("delete from   ".WPD_LOGIN_ACCESS."  where  Referer is null  and  Useragent is null ");$sql="select * from ".WPD_LOGIN_ACCESS. " where IPAddress='".$_SERVER['REMOTE_ADDR']."' and  IPstatus=1";$records=$wpdb->get_results($sql); if($records){	//exit($records[0]->IPstatus."  Ip is bandk");if($records[0]->IPstatus=="1"){		if ($file == 'wp-login.php' || is_admin() && !current_user_can('edit_posts') && $file != 'admin-ajax.php'){		//wp_redirect( home_url() );		$message =__('Oups, your IP address was Band for security reason. Please contact to the administrator to take further moves.', 'wpd_security');		exit($message);       		}}}if(GET_WPD_LOGINSTATUS()->loginstatus=="logout")detectattemp();  return;}// very early hook in wordpress, to check if the IP address is allowedadd_action('plugins_loaded', 'wpd_ip_allowed');function  detectattemp(){ global $wpdb;if(GET_WPD_IPBLOCK()->ip_address==$_SERVER['REMOTE_ADDR'] and GET_WPD_IPBLOCK()->band_duration > time())//if(isset( $_COOKIE['clientipblock']) ){$duration=(WPD_OPTIONS()->ipband_durationh * 60) +(WPD_OPTIONS()->ipband_durationm);$file = basename($_SERVER['PHP_SELF']);if ($file == 'wp-login.php' || is_admin() && !current_user_can('edit_posts') && $file != 'admin-ajax.php'){//wp_redirect( home_url() );$msg=' <div class="updated" id="message"><p> <strong>You are attempt limit is exceeded,wait for  '.((WPD_OPTIONS()->ipband_durationh * 60)/60) . ' hour and '.WPD_OPTIONS()->ipband_durationm.' minute.</strong></p></div>';$wpdb->query("delete from   ".WPD_LOGIN_ACCESS."  where  Referer is null  and  Useragent is null ");exit( $msg );}}else{$wpipblock= array('ip_address'=>'','band_duration'=>0,);$wpipblock = serialize($wpipblock);update_option( 'blockip' ,  $wpipblock);if(isset($_SESSION['views'])){$_SESSION['views']=$_SESSION['views']+1;}else{$_SESSION['views']=0;}if($_SESSION['views']==WPD_OPTIONS()->attemp_limit  and WPD_OPTIONS()->attemp_option=="1"){unset($_SESSION['views']);$ipblock=serialize($ipblock);update_option('blockip',$ipblock);$duration=(WPD_OPTIONS()->ipband_durationh * 60) +(WPD_OPTIONS()->ipband_durationm);$wpipblock= array('ip_address'=>$_SERVER['REMOTE_ADDR'],'band_duration'=>(time() + (60 * $duration)),);$wpipblock = serialize($wpipblock);update_option( 'blockip' ,  $wpipblock);//setcookie("clientipblock", "bandip", time() + (60 * WPD_OPTIONS()->ipband_duration));//setcookie("address", $_SERVER['REMOTE_ADDR'], time() + (60 * WPD_OPTIONS()->ipband_duration));}}}