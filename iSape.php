<?php
/*
Plugin Name: iSape
Version: 0.72 (02-05-2010)
Plugin URI: https://github.com/xezzus/sape-wp
Description: Плагин для Wordpress, обеспечивает вывод ссылок от биржи ссылок Sape
Author: xezzus
Author URI: https://github.com/xezzus
*/

/*
Copyright 2012  xezzus (web : https://github.com/xezzus)

This free.
*/

class itex_sape
{
	var $version = '0.72';
	var $full = 0;
	var $error = '';
	//var $force_show_code = true;
	var $sape;
	var $sapecontext;
	var $links = array();
	var $tnx;
	//var $enable = false;
	var $sidebar = array();
	var $sidebar_links = '';
	var $footer = '';
	var $beforecontent = '';
	var $aftercontent = '';
	var $safeurl = '';
	var $document_root = '';
	//var $debug = 1;
	var $debuglog = '';
	var $memory_get_usage = 0; //start memory_get_usage
	var $get_num_queries = 0; //start get_num_queries
	//var $replacecontent = 0;
	
	/**
   	* constructor, function __construct()  in php4 not working
   	*
   	*/
	function itex_sape()
	{
		if (substr(phpversion(),0,1) == 4) $this->php4(); //fix php4 bugs
		add_action('widgets_init', array(&$this, 'itex_s_init'));
		//add_action("widgets_init", array(&$this, 'itex_s_widget_init'));
		add_action('admin_menu', array(&$this, 'itex_s_menu'));
		add_action('wp_footer', array(&$this, 'itex_s_footer'));
		$this->document_root = ($_SERVER['DOCUMENT_ROOT'] != str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]))?(str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"])):($_SERVER['DOCUMENT_ROOT']);
		
	}
	
	/**
   	* php4 support
   	*
   	*/
	function php4()
	{
		if (!function_exists('file_put_contents')) 
		{
   			function file_put_contents($filename, $data) 
   			{
        		$f = @fopen($filename, 'w');
        		if (!$f) return false;
        		else 
        		{
            		$bytes = fwrite($f, $data);
            		fclose($f);
            		return $bytes;
        		}
    		}
		}
		$this->itex_debug('Used php4');
	}
	
	/**
   	*  Russian lang support
   	*
   	*/
	function lang_ru()
	{
		global $l10n;
		$locale = get_locale();
		if ($locale == 'ru_RU')
		{
			$domain = 'iSape';
			if (isset($l10n[$domain])) return;
			///ru_RU lang .mo file
			$input = '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';
			$input = gzuncompress(base64_decode($input));
			if (file_exists(ABSPATH . WPINC . '/streams.php') && file_exists(ABSPATH . WPINC . '/gettext.php'))
			{
				include_once(ABSPATH . WPINC . '/streams.php');
				include_once(ABSPATH . WPINC . '/gettext.php');
				$inputReader = new StringReader($input);
				$l10n[$domain] = new gettext_reader($inputReader);
				$this->itex_debug('Used Ru language');
			}
		}
	}
	
	/**
   	* Debug collector
   	*
   	*/
	function itex_debug($text='')
	{
		$this->debuglog .= "\r\n".$text."\r\n";
	}
	
	/**
   	* plugin init function 
   	*
   	* @return  bool	
   	*/
	function itex_s_init()
	{
		if ( function_exists('memory_get_usage') ) $this->memory_get_usage = memory_get_usage();
		if ( function_exists('get_num_queries') ) $this->get_num_queries = get_num_queries();
		
		//echo $this->get_num_queries;//die();
		
		
		if (get_option('itex_s_global_masking')){
			$this->itex_s_safe_url();
			$last_REQUEST_URI = $_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI'] = $this->safeurl;
			
		}
		$this->itex_debug('REQUEST_URI = '.$_SERVER['REQUEST_URI']);
		
		
		$this->itex_init_sape();
		
		$this->itex_s_widget_init();
		if (strlen($this->footer)) add_action('wp_footer', array(&$this, 'itex_s_footer'));

		//echo get_num_queries();die();
		
		if ((strlen($this->beforecontent)) || (strlen($this->aftercontent)) )
		{
			$this->itex_debug('strlenbeforecontent = '.strlen($this->beforecontent));
			$this->itex_debug('strlenaftercontent = '.strlen($this->aftercontent));
			add_filter('the_content', array(&$this, 'itex_s_replace'));
			add_filter('the_excerpt', array(&$this, 'itex_s_replace'));
		}
		
		if (isset($last_REQUEST_URI)) //privodim REQUEST_URI v poryadok
		{
			$_SERVER['REQUEST_URI'] = $last_REQUEST_URI;
			unset($last_REQUEST_URI);
		}
		
		if ( function_exists('memory_get_usage') ) $this->itex_debug("memory start/end/dif ".$this->memory_get_usage.'/'.memory_get_usage().'/'.(memory_get_usage()-$this->memory_get_usage));
		if ( function_exists('get_num_queries') ) $this->itex_debug("get_num_queries start/end/dif ".intval($this->get_num_queries).'/'.intval(get_num_queries()).'/'.(intval(get_num_queries())-intval($this->get_num_queries)));
		return 1;
	}

	/**
   	* sape init
   	*
   	* @return  bool
   	*/
	function itex_init_sape()
	{
		if (!get_option('itex_s_sape_enable')) return 0;
		if (!defined('_SAPE_USER')) define('_SAPE_USER', get_option('itex_s_sape_sapeuser'));
		else $this->error .= '_SAPE_USER '.__('already defined<br/>', 'iSape');
		$this->itex_debug('SAPE_USER = '.get_option('itex_s_sape_sapeuser'));
		//FOR MASS INSTALL ONLY, REPLACE if (0) ON if (1)
		//		if (0)
		//		{
		//			update_option('itex_sape_sapeuser', 'abcdarfkwpkgfkhagklhskdgfhqakshgakhdgflhadh'); //sape uid
		//			update_option('itex_sapecontext_enable', 1);
		//			update_option('itex_sape_enable', 1);
		//			update_option('itex_sape_links_footer', 'max');
		//		}

		$file = $this->document_root . '/' . _SAPE_USER . '/sape.php'; //<< Not working in multihosting.
		if (file_exists($file)) require_once($file);
		else return 0;
		
		$o['charset'] = get_option('blog_charset')?get_option('blog_charset'):'UTF-8';
		//$o['force_show_code'] = $this->force_show_code;
		if (get_option('itex_s_global_debugenable'))
		{
			$o['force_show_code'] = 1;
		}
		$o['multi_site'] = true;
//		if (get_option('itex_s_sape_masking'))
//		{
//			$this->itex_s_safe_url();
//			$o['request_uri'] = $this->safeurl;
//		}
		if (get_option('itex_s_sape_enable'))
		{
			$this->sape = new SAPE_client($o);
			
			
			$this->itex_init_sape_links();
			
			
			///check it
			if (is_object($GLOBALS['wp_rewrite'])) $url = url_to_postid($_SERVER['REQUEST_URI']);
			else $url = 1;
			if (($url) || !get_option('itex_sape_pages_enable')) 
			{
				if (get_option('itex_s_sape_links_beforecontent') == '0')
				{
					//$this->beforecontent = '';
				}
				else
				{
					$this->beforecontent .= '<div>'.$this->itex_init_sape_get_links(intval(get_option('itex_s_sape_links_beforecontent'))).'</div>';
				}
					
				if (get_option('itex_s_sape_links_aftercontent') == '0')
				{
					//$this->aftercontent = '';
				}
				else
				{
					$this->aftercontent .= '<div>'.$css.$this->itex_init_sape_get_links(intval(get_option('itex_s_sape_links_aftercontent'))).'</div>';
				}
			}
			$countsidebar = get_option('itex_s_sape_links_sidebar');
			$check = get_option('itex_s_global_debugenable')?'<!---check sidebar '.$countsidebar.'-->':'';
			if ($countsidebar == 'max')
			{
				//$this->sidebar = '<div>'.$this->sape->return_links().'</div>';
			}
			elseif ($countsidebar == '0')
			{
				//$this->sidebar = '';
			}
			else
			{
				$this->sidebar_links .= '<div>'.$this->itex_init_sape_get_links(intval($countsidebar)).'</div>';
			}
			$this->sidebar_links = $check.$this->sidebar_links;
			
			$countfooter = get_option('itex_s_sape_links_footer');
			$check = get_option('itex_s_global_debugenable')?'<!---check footer '.$countfooter.'-->':'';
			$this->footer .= $check;
			if ($countfooter == 'max')
			{
				//$this->footer = '<div>'.$this->sape->return_links().'</div>';
			}
			elseif ($countfooter == '0')
			{
				//$this->footer = '';
			}
			else
			{
				$this->footer .= '<div>'.$this->itex_init_sape_get_links($countfooter).'</div>';
			}
			$this->footer = $check.$this->footer;
			
			if (($countsidebar == 'max') && ($countfooter == 'max')) $this->footer .= $this->itex_init_sape_get_links();
			else
			{
				if  ($countsidebar == 'max') {
          $this->sidebar_links = $this->itex_init_sape_get_links(false,false,'array');
        }
				else $this->footer .= $this->itex_init_sape_get_links();
			}
			
		}

		if (get_option('itex_s_sape_sapecontext_enable'))
		{
			$this->sapecontext = new SAPE_context($o);
			add_filter('the_content', array(&$this, 'itex_s_replace'));
			add_filter('the_excerpt', array(&$this, 'itex_s_replace'));
		}
		return 1;
	}
	
	/**
   	* get sape links
   	*
   	* @return  bool
   	*/
	function itex_init_sape_links()
	{
		$i = 1;
		
		while ($i++)
		{
			$q = $this->sape->return_links(1);
			if (empty($q) || !strlen($q))
			{
				break;
			}
			$q .= $this->sape->_links_delimiter;
			
			if (strlen($q)) $this->links['a_only'][] = $q;
			
			//!!!!!!!!!!check it, tk ne vozvrashaet pustuu stroku
			if ($i > 30) break;
		}
		$this->itex_debug('sape links:'.var_export($this->links, true));
		return 1;
	}

	/**
   	* get links
   	*
   	* @param   int   $c		count
   	* @param   int   $c		a only if 1
    * @return  string $ret  
   	*/
	function itex_init_sape_get_links($c = false, $q=false, $return = 'string') //$q = a only
	{
    $c = $c === false ? 30 : (int) $c ;
    $q = $q === false ? 1 : (int) $q ;
    $ret = array();
		for ($i=1;$i<=$c;$i++)
		{
			if ($q)
			{
				if (count($this->links['a_only'])) 
					foreach ($this->links['a_only'] as $k=>$v)
					{
						if(strlen($v) > 4) $ret[] = $v;
						unset($this->links['a_only'][$k]);
						break;
					}
			}
			else 
			{
				if (count($this->links['a_text'])) 
					foreach ($this->links['a_text'] as $k=>$v)
					{
						if(strlen($v) > 4) $ret[] = $v;
						unset($this->links['a_text'][$k]);
						break;
					}
			}
		}
    switch($return){
      case "string":
        return implode($ret);
      break;
      case "array":
        return $ret;
      break;
    }
	}
	
	
		
	/**
   	* Footer output
   	*
   	*/
	function itex_s_footer()
	{
		echo $this->footer;
		if (get_option('itex_s_global_debugenable'))
		{
			//echo 'is_user_logged_in'.intval(is_user_logged_in()).'_'.intval(get_option('itex_s_global_debugenable_forall'));//die();
		
			if ((intval(is_user_logged_in())) || intval(get_option('itex_s_global_debugenable_forall')))
			{
				echo '<!--- iSapeDebugLogStart'.$this->debuglog.' iSapeDebugLogEnd --->';
				echo '<!--- iSapeDebugErrorsStart'.$this->error.' iSapeDebugErrorsEnd --->';
			}
		}
	}
	
	/**
   	* Content links and before-after content links
   	*
   	* @param   string   $content   input text
   	* @return  string	$content   outpu text
   	*/
	function itex_s_replace($content)
	{
		//sape context
		if (get_option('itex_s_sape_sapecontext_enable'))
		{
			if (url_to_postid($_SERVER['REQUEST_URI']) || !get_option('itex_sape_pages_enable')) 
			{
				//if (defined('_SAPE_USER') || is_object($this->sapecontext)) 
				if (is_object($this->sapecontext)) 
				{
					$content = $this->sapecontext->replace_in_text_segment($content);
					if (get_option('itex_s_global_debugenable'))
					{
						$content = '<!---checkcontext_start-->'.$content.'<!---checkcontext_stop-->';
					}
					$this->itex_debug('sapecontext worked');
				}
				else $this->itex_debug('$this->sapecontext not object');
			}
			else $this->itex_debug('url_to_postid='.url_to_postid($_SERVER['REQUEST_URI']).' itex_sape_pages_enable='.get_option('itex_sape_pages_enable'));
		}
		else $this->itex_debug('sapecontext disabled');
		
		
		if ((strlen($this->beforecontent)) || (strlen($this->aftercontent)))
		{
			if (get_option('itex_s_global_debugenable'))
			{
				
				$content = '<!---check_beforecontent-->'.$this->beforecontent.$content.'<!---check_aftercontent-->'.$this->aftercontent;
			}
			else $content = $this->beforecontent.$content.$this->aftercontent;
			$this->beforecontent=$this->aftercontent='';
			$this->itex_debug('links in content worked');
		}
		else $this->itex_debug('beforecontent and aftercontent is empty');
		return $content;
	}

	/**
   	* 
   	*
   	* @param   string   $domnod   $text
   	* @return  string	$text
   	*/
	function itex_s_widget_init()
	{
		if (function_exists('register_sidebar_widget')) register_sidebar_widget('iSape Links', array(&$this, 'itex_s_widget_links'));
		if (function_exists('register_widget_control')) register_widget_control('iSape Links', array(&$this, 'itex_s_widget_links_control'), 300, 200 );
			
	}

	
	/**
   	* Links widget
   	*
   	* @param   array   $args   arguments for widget
    */
	function itex_s_widget_links($args)
	{
		extract($args, EXTR_SKIP);
		$title = get_option("itex_s_widget_links_title");
    
		if (count($this->sidebar_links) > 0) {
      if(is_array($this->sidebar_links)) $li = '<li>'.implode('<li></li>',$this->sidebar_links).'</li>';
      echo $before_widget.$before_title . $title . $after_title.  '<ul>'.$li.'</ul>'.$after_widget;
    }
	}

	/**
   	*  Links widget control
   	*
   	* @param   string   $domnod   $text
   	*/
	function itex_s_widget_links_control()
	{
		$title = get_option("itex_s_widget_links_title");
		if ($_POST['itex_s_widget_links_Submit'])
		{
			//$title = htmlspecialchars($_POST['itex_s_widget_title']);
			$title = stripslashes($_POST['itex_s_widget_links_title']);
			update_option("itex_s_widget_links_title", $title);
		}
		echo '
  			<p>
    			<label for="itex_s_widget_links">'.__('Widget Title: ', 'iSape').'</label>
    			<textarea name="itex_s_widget_links_title" id="itex_s_widget_links" rows="1" cols="20">'.$title.'</textarea>
    			<input type="hidden" id="" name="itex_s_widget_links_Submit" value="1" />
  			</p>';
		//print_r($this->debuglog);//die();
	}

	/**
   	* Add admin menu to options
   	*
   	* @param   string   $domnod   $text
   	* @return  string	$text
   	*/
	function itex_s_menu()
	{
		if (is_admin()) add_options_page('iSape', 'iSape', 10, basename(__FILE__), array(&$this, 'itex_s_admin'));
	}

	/**
   	* Admin menu
   	*
   	*/
	function itex_s_admin()
	{
		if (!is_admin()) return 0;
		$this->lang_ru();
		$this->itex_s_admin_css();
		// Output the options page
		?>
		<div class="wrap">
		
			<form method="post">
			<?php
			if (strlen($this->error))
			{
				echo '
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
					'.$this->error.'
				</div>
			';
			}
			?>		
			
			<h2><?php echo __('iSape Options', 'iSape');?></h2>
			<?php if ( '09_May' == date('d_F')) $this->itex_m_admin_9_may(); ?>
			                       
       			<!-- Main -->
        		
        			<?php 
        			?>
        		
        		<p class="submit">
				<input type='submit' name='info_update' value='<?php echo __('Save Changes', 'iSape'); ?>' />
				</p>
        		
       	 		<div id="itex_sape"><?php $this->itex_s_admin_sape(); ?></div>
       	 		
			</div>
			
			<p class="submit">
				<input type='submit' name='info_update' value='<?php echo __('Save Changes', 'iSape'); ?>' />
			</p>
			        		
			</form>
		
		</div>
		<?php
	}

	/**
   	* Css fo admin menu
   	*
   	*/
	function itex_s_admin_css()
	{
		?>
		<style type='text/css'>
			#edit_tabs li {            
				list-style-type: none;
				float: left;       
				margin: 2px 5px 0 0;           
				padding-left: 15px;  
				text-align: center;
			}                        

			#edit_tabs li a {           
				display: block;                            
				font-size: 85%;                               
				font-family: "Lucida Grande", "Verdana";
				font-weight: bold;                          
				float: left;                                       
				color: #999;
				border-bottom: none;
				padding: 2px 15px 2px 0;	
				width: auto !important;
				width: 50px;        
				min-width: 50px;                                                     
				text-shadow: white 0 1px 0;  
			}               

			#edit_sections .section {
				background: url('images/bg_tab_section.gif') no-repeat top left;
				padding-left: 10px;
				padding-top: 15px;
				height: auto !important;
				height: 200px;       
				min-height: 200px;
				display: none;
			}              

			#edit_sections .section ul {
				padding-left: 10px;
				width: 500px;
			}

			#edit_sections .current {
				display: block;
			}                   

			#edit_sections .section .section_warn {
				background: #FFFFE0;
				border: 1px solid #EBEBA9;
				padding: 8px;
				float: right;
				width: 300px;
				font-size: 11px;
			}       
		</style>
		<?php
	}

	/**
   	* Sape section admin menu
   	*
   	*/
	function itex_s_admin_sape()
	{
		if (isset($_POST['info_update']))
		{
			//phpinfo();die();
			if (isset($_POST['sape_sapeuser']))
			{
				update_option('itex_s_sape_sapeuser', trim($_POST['sape_sapeuser']));
			}
			if (isset($_POST['sape_enable']))
			{
				update_option('itex_s_sape_enable', intval($_POST['sape_enable']));
			}

			if (isset($_POST['sape_links_beforecontent']))
			{
				update_option('itex_s_sape_links_beforecontent', $_POST['sape_links_beforecontent']);
			}

			if (isset($_POST['sape_links_aftercontent']))
			{
				update_option('itex_s_sape_links_aftercontent', $_POST['sape_links_aftercontent']);
			}

			if (isset($_POST['sape_links_sidebar']))
			{
				update_option('itex_s_sape_links_sidebar', $_POST['sape_links_sidebar']);
			}

			if (isset($_POST['sape_links_footer']))
			{
				update_option('itex_s_sape_links_footer', $_POST['sape_links_footer']);
			}

			
			
			if (isset($_POST['sape_sapecontext_enable']) )
			{
				update_option('itex_s_sape_sapecontext_enable', intval($_POST['sape_sapecontext_enable']));
			}

			if (isset($_POST['sape_sapecontext_pages_enable']) )
			{
				update_option('itex_s_sape_sapecontext_pages_enable', intval($_POST['sape_sapecontext_pages_enable']));
			}
			
			if (isset($_POST['sape_pages_enable']) )
			{
				update_option('itex_s_sape_pages_enable', intval($_POST['sape_pages_enable']));
			}
			if (isset($_POST['global_debugenable']))
			{
				update_option('itex_s_global_debugenable', intval($_POST['global_debugenable']));
			}
			
			if (isset($_POST['global_debugenable_forall']))
			{
				update_option('itex_s_global_debugenable_forall', intval($_POST['global_debugenable_forall']));
			}
			
			if (isset($_POST['global_masking']))
			{
				update_option('itex_s_global_masking', intval($_POST['global_masking']));
			}
			
			if (isset($_POST['global_widget_links']))
			{
				$s_w = wp_get_sidebars_widgets();
				$ex = 0;
				if (count($s_w['sidebar-1'])) foreach ($s_w['sidebar-1'] as $k => $v)
				{
					if ($v == 'isape-links')
					{
						$ex = 1;
						if (!$_POST['global_widget_links']) unset($s_w['sidebar-1'][$k]);
					}
				}
				if (!$ex && $_POST['global_widget_links']) $s_w['sidebar-1'][] = 'isape-links';
				wp_set_sidebars_widgets( $s_w );
			}
			
			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}
		if (isset($_POST['sape_sapedir_create']))
		{
			if (get_option('itex_s_sape_sapeuser'))  $this->itex_s_sape_install_file();
		}
		if (get_option('itex_s_sape_sapeuser'))  
		{
		$file = $this->document_root . '/' . _SAPE_USER . '/sape.php'; //<< Not working in multihosting.
		if (file_exists($file)) {}
		else
		{
			$file = str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]).'/'.get_option('itex_s_sape_sapeuser').'/sape.php';
			if (file_exists($file)) {}
			else {?>
		<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				Sape dir not exist!
		</div>
		<div style="margin:10px auto; border:3px #f00 solid; padding:10px; text-align:center;">
				Create new sapedir and sape.php? (<?php echo $file;?>)
				<p class="submit">
				<input type='submit' name='sape_sapedir_create' value='<?php echo __('Create', 'iSape'); ?>' />
				</p>
				<?php
				if (!get_option('itex_s_sape_sapeuser')) echo __('Enter your SAPE UID in this box!', 'iSape');
				?>
		</div>
		
		<?php }
		}
		}
		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Your SAPE UID:', 'iSape');?></label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='sape_sapeuser'";
						echo "id='sapeuser' ";
						echo "value='".get_option('itex_s_sape_sapeuser')."' />\n";
						?>
						<p style="margin: 5px 10px;"><?php echo __('Enter your SAPE UID in this box.', 'iSape');?></p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Sape links:', 'iSape');?></label>
					</th>
					<td>
						<?php
						echo "<select name='sape_enable' id='sape_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_sape_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iSape')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iSape').'</label>';
						echo "<br/>\n";

						echo "<select name='sape_links_beforecontent' id='sape_links_beforecontent'>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_links_beforecontent')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";

						echo "<option value='1'";
						if(get_option('itex_s_sape_links_beforecontent') == 1) echo " selected='selected'";
						echo ">1</option>\n";

						echo "<option value='2'";
						if(get_option('itex_s_sape_links_beforecontent') == 2) echo " selected='selected'";
						echo ">2</option>\n";

						echo "<option value='3'";
						if(get_option('itex_s_sape_links_beforecontent') == 3) echo " selected='selected'";
						echo ">3</option>\n";

						echo "<option value='4'";
						if(get_option('itex_s_sape_links_beforecontent') == 4) echo " selected='selected'";
						echo ">4</option>\n";

						echo "<option value='5'";
						if(get_option('itex_s_sape_links_beforecontent') == 5) echo " selected='selected'";
						echo ">5</option>\n";

						echo "</select>\n";

						echo '<label for="">'.__('Before content links', 'iSape').'</label>';

						echo "<br/>\n";



						echo "<select name='sape_links_aftercontent' id='sape_links_aftercontent'>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_links_aftercontent')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";

						echo "<option value='1'";
						if(get_option('itex_s_sape_links_aftercontent') == 1) echo " selected='selected'";
						echo ">1</option>\n";

						echo "<option value='2'";
						if(get_option('itex_s_sape_links_aftercontent') == 2) echo " selected='selected'";
						echo ">2</option>\n";

						echo "<option value='3'";
						if(get_option('itex_s_sape_links_aftercontent') == 3) echo " selected='selected'";
						echo ">3</option>\n";

						echo "<option value='4'";
						if(get_option('itex_s_sape_links_aftercontent') == 4) echo " selected='selected'";
						echo ">4</option>\n";

						echo "<option value='5'";
						if(get_option('itex_s_sape_links_aftercontent') == 5) echo " selected='selected'";
						echo ">5</option>\n";

						echo "</select>\n";

						echo '<label for="">'.__('After content links', 'iSape').'</label>';

						echo "<br/>\n";

						echo "<select name='sape_links_sidebar' id='sape_links_sidebar'>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_links_sidebar')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";

						echo "<option value='1'";
						if(get_option('itex_s_sape_links_sidebar') == 1) echo " selected='selected'";
						echo ">1</option>\n";

						echo "<option value='2'";
						if(get_option('itex_s_sape_links_sidebar') == 2) echo " selected='selected'";
						echo ">2</option>\n";

						echo "<option value='3'";
						if(get_option('itex_s_sape_links_sidebar') == 3) echo " selected='selected'";
						echo ">3</option>\n";

						echo "<option value='4'";
						if(get_option('itex_s_sape_links_sidebar') == 4) echo " selected='selected'";
						echo ">4</option>\n";

						echo "<option value='5'";
						if(get_option('itex_s_sape_links_sidebar') == 5) echo " selected='selected'";
						echo ">5</option>\n";

						echo "<option value='max'";
						if(get_option('itex_s_sape_links_sidebar') == 'max') echo " selected='selected'";
						echo ">".__('Max', 'iSape')."</option>\n";

						echo "</select>\n";

						echo '<label for="">'.__('Sidebar links', 'iSape').'</label>';

						echo "<br/>\n";


						echo "<select name='sape_links_footer' id='sape_links_footer'>\n";
						echo "<option value='0'";
						if(!get_option('itex_s_sape_links_footer')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";

						echo "<option value='1'";
						if(get_option('itex_s_sape_links_footer') == 1) echo " selected='selected'";
						echo ">1</option>\n";

						echo "<option value='2'";
						if(get_option('itex_s_sape_links_footer') == 2) echo " selected='selected'";
						echo ">2</option>\n";

						echo "<option value='3'";
						if(get_option('itex_s_sape_links_footer') == 3) echo " selected='selected'";
						echo ">3</option>\n";

						echo "<option value='4'";
						if(get_option('itex_s_sape_links_footer') == 4) echo " selected='selected'";
						echo ">4</option>\n";

						echo "<option value='5'";
						if(get_option('itex_s_sape_links_footer') == 5) echo " selected='selected'";
						echo ">5</option>\n";

						echo "<option value='max'";
						if(get_option('itex_s_sape_links_footer') == 'max') echo " selected='selected'";
						echo ">".__('Max', 'iSape')."</option>\n";

						echo "</select>\n";

						echo '<label for="">'.__('Footer links', 'iSape').'</label>';

						echo "<br/>\n";
						echo "<select name='sape_pages_enable' id='sape_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_sape_pages_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iSape')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_pages_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Show content links only on Pages and Posts.', 'iSape').'</label>';

						echo "<br/>\n";
						?>
					</td>
					
					
				</tr>
				<?php
				?>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Sape context:', 'iSape'); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='sape_sapecontext_enable' id='sape_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_sape_sapecontext_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iSape')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_sapecontext_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Context', 'iSape').'</label>';

						echo "<br/>\n";

						echo "<select name='sape_sapecontext_pages_enable' id='sape_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_sape_sapecontext_pages_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iSape')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_sape_sapecontext_pages_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Show context only on Pages and Posts.', 'iSape').'</label>';

						echo "<br/>\n";
						?>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Masking of links', 'iSape'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='global_masking' id='global_masking'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_global_masking')) echo " selected='selected'";
						echo __(">Enabled</option>\n", 'iSape');

						echo "<option value='0'";
						if(!get_option('itex_s_global_masking')) echo" selected='selected'";
						echo __(">Disabled</option>\n", 'iSape');
						echo "</select>\n";

						echo '<label for="">'.__('Masking of links', 'iSape').'.</label>';

						?>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Global debug:', 'iSape'); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='global_debugenable' id='global_debugenable'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_global_debugenable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iSape')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_global_debugenable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Debug log in footer. For see debug user must register', 'iSape').'.</label>';
						
						echo "<br/>";
						
						echo "<select name='global_debugenable_forall' id='global_debugenable_forall'>\n";
						echo "<option value='1'";

						if(get_option('itex_s_global_debugenable_forall')) echo " selected='selected'";
						echo ">".__("Enabled", 'iSape')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_s_global_debugenable_forall')) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Debug log in footer for all, who open the site. Dont leave this parameter switched Enabled for a long time, because in this case it will disclose your private data like SAPE UID', 'iSape').'.</label>';
						
						?>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Widgets settings:', 'iSape'); ?></label>
					</th>
					<td>
						<?php
						$ws = wp_get_sidebars_widgets();
						
						
						echo "<select name='global_widget_links' id='global_widget_links'>\n";
						echo "<option value='0'";
						if (count($ws['sidebar-1'])) if(!in_array('isape-links',$ws['sidebar-1'])) echo" selected='selected'";
						echo ">".__("Disabled", 'iSape')."</option>\n";

						echo "<option value='1'";
						if (count($ws['sidebar-1'])) if (in_array('isape-links',$ws['sidebar-1'])) echo " selected='selected'";
						echo ">".__('Active','iSape')."</option>\n";

						echo "</select>\n";
						
						echo '<label for="">'.__('Widget Links Active', 'iSape').'</label>';

						echo "<br/>\n";
						
						?>
					</td>
				</tr>
			</table>
			<?php
	}

	/**
   	* Sape file installation
   	*
   	* @return  bool
   	*/
	function itex_s_sape_install_file()
	{
		//file sape.php from sape.ru v1.0.4 21.07.2008
		$sape_php_content = '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';
		$sape_php_content = gzuncompress(base64_decode($sape_php_content));
		$file = str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]).'/'.get_option('itex_s_sape_sapeuser').'/sape.php';

		$dir = dirname($file);
		if (!@mkdir($dir, 0777))
		{
			echo '

		<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				'.__('Can`t create Sape dir!', 'iSape').'
		</div>';
			return 0;
		}
		chmod($dir, 0777);  //byli gluki s mkdir($dir, 0777)
		if (!file_put_contents($file,$sape_php_content))
		{
			echo '
		<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				'.__('Can`t create sape.php!', 'iSape').'
		</div>';
			return 0;
		}
		//chmod($file, 0777);
		file_put_contents($dir.'/.htaccess',"deny from all\r\n");
		echo '
		<div style="margin:10px auto; border:3px  #55ff00 solid; background-color:#afa; padding:10px; text-align:center;">
				'.__('Sapedir and sape.php created!', 'iSape').'
		</div>';
		//die();
		return 1;
	}

	/**
   	* 9 may section admin menu
   	*
   	*/
	function itex_m_admin_9_may()
	{
		if ( '09_May' == date('d_F'))
		echo '<center><h1><a href="http://itex.name/plugins/s-dnem-pobedy.html">С Праздником Победы!</a></h1><p><object width="640" height="505"><param name="movie" value="http://www.youtube-nocookie.com/v/TQrINrPzgmw&hl=ru_RU&fs=1&rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube-nocookie.com/v/TQrINrPzgmw&hl=ru_RU&fs=1&rel=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="505"></embed></object></p></center>';
		
	}
	/**
   	* Url masking
   	*
   	* @return  bool
   	*/
	function itex_s_safe_url()
	{
		$vars=array('p','p2','pg','page_id', 'm', 'cat', 'tag', 'paged');
		
		$url=explode("?",strtolower($_SERVER['REQUEST_URI']));
		if(isset($url[1]))
		{
			$count = preg_match_all("/(.*)=(.*)\&/Uis",$url[1]."&",$get);
			for($i=0; $i < $count; $i++)
				if (in_array($get[1][$i],$vars) && !empty($get[2][$i])) 
					$ret[] = $get[1][$i]."=".$get[2][$i];
			if (count($ret))
			{
				$ret = '?'.implode("&",$ret);
		//print_r($ret);die();
			}
			else $ret = '';
		}
		else $ret = '';
		$this->safeurl = $url[0].$ret;
		return 1;
	}
	
	
}

if (function_exists(add_action)) $itex_sape = & new itex_sape();

?>
