<?php
/*
Plugin Name: Widgetize Google Gadgets
Plugin URI: http://webdlabs.com/projects
Description: Google Gadgets are simple HTML and JavaScript applications that can be embedded in webpages and other apps. This plugin provides a widget interface for adding any Google Gadget. Choose from thousands of Google Gadgets for your Webpage from the Google Gadget Directory - http://www.google.com/ig/directory?synd=open
Author: Akshay Raje
Version: 0.2
Author URI: http://webdlabs.com

*/

/*
This wordpress plugin was modified from Milan Petrovic's GD Multi plugin framework. Details about GD Multi plugin:

Plugin Name: GD Multi
Plugin URI: http://wp.gdragon.info/plugins/gd-pages-navigator/
Description: This widget can be used to change the way of navigating through your blog pages.
Version: 2.5.0
Author: Milan Petrovic
Author URI: http://wp.gdragon.info/
*/

class WidgetGoogleGadget {
    function WidgetGoogleGadget() {
    }
    
    function init() {
        if (!$options = get_option('widget_google_gadget'))
            $options = array();
            
        $widget_ops = array('classname' => 'widget_google_gadget', 'description' => 'Add any Google Gadget as a widget');
        $control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'wgg');
        $name = 'Google Gadget';
        
        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;
                
            $id = "wgg-$o";
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('wgg-1', $name, array(&$this, 'widget'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('wgg-1', $name, array(&$this, 'control'), $control_ops, array( 'number' => -1 ) );
        }
    }
    
    function widget($args, $widget_args = 1) {
        extract($args);
        global $post;

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_google_gadget');
        if (!isset($options_all[$number]))
            return;
        $options = $options_all[$number];

        echo $before_widget;
        if ( !empty( $options["title"] ) )  echo $before_title . $options["title"] . $after_title;
		$url = split("<script src=", $options["gadgetcode"]);
		$url = split("&amp;output", $url[1]);		
		$url = str_replace("\"","", $url[0]);
		$url = str_replace("\\http","http", $url);
		preg_match('/&amp;h=(.*?)&amp/s',  $url, $height);
		?>
		<iframe src="<?php echo $url;?>" height="<?php echo $height[1];?>" width="100%" frameborder="0" scrolling="no"></iframe>
		<?php
        echo $after_widget;		
    }
    
    function control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array( 'number' => $widget_args );
        $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_google_gadget');
        if (!is_array($options_all))
            $options_all = array();
            
        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('widget_google_gadget' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("wgg-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['widget_google_gadget'] as $widget_number => $widget_many_instance) {
                if (!isset($widget_many_instance['title']) && isset($options_all[$widget_number]))
                    continue;
                $title = wp_specialchars($widget_many_instance['title']);
                $gadgetcode = $widget_many_instance['gadgetcode'];
                $options_all[$widget_number] = array('title' => $title, 'gadgetcode' => $gadgetcode);
            }
            update_option('widget_google_gadget', $options_all);
            $updated = true;
        }

        if (-1 == $number) {
            $title = '';
            $gadgetcode = '';
            $number = '%i%';
        } else {
            $title = wp_specialchars($options_all[$number]['title'] );
            $gadgetcode = wp_specialchars($options_all[$number]['gadgetcode'] );
        }
        ?>
            <p>
				Title (optional):
                <input class="widefat" id="widget_google_gadget-<?php echo $number; ?>-title" name="widget_google_gadget[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" /><br /><br />
				Google Gadgets are simple HTML and JavaScript applications that can be embedded in webpages and other apps. Choose from thousands of Google Gadgets for your Webpage from the <a href="http://www.google.com/ig/directory?synd=open" target="_blank">Google Gadget Directory</a>.
                On customising the selected gadget of your choice, click 'Get the Code' and paste the code in the input field below.<br /><br />
				Google Gadget Code:
				<textarea class="widefat" id="widget_google_gadget-<?php echo $number; ?>-gadgetcode" name="widget_google_gadget[<?php echo $number; ?>][gadgetcode]"><?php echo $gadgetcode; ?></textarea>
                <input type="hidden" id="widget_google_gadget-<?php echo $number; ?>-submit" name="widget_google_gadget[<?php echo $number; ?>][submit]" value="1" />
            </p>
        <?php
    }
    
    function render_options($pages) {
    }
}

$wgg = new WidgetGoogleGadget();
add_action('widgets_init', array($wgg, 'init'));

?>