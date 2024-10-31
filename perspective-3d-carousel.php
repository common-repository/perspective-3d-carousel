<?php
/*
Plugin Name: Perspective 3D Carousel
Plugin URI: https://tishonator.com/plugins/perspective-3d-carousel
Description: Perspective 3D Carousel PRO plugin allows you to insert and configure a Responsive jQuery Slider into your WordPress site as a shortcode. Admin Slide Fields for Title, Text, and Image.
Author: tishonator
Version: 1.0.1
Author URI: http://tishonator.com/
Contributors: tishonator
Text Domain: perspective-3d-carousel
*/

if ( !class_exists('tishonator_Perspective3DCarouselPlugin') ) :

    /**
     * Register the plugin.
     *
     * Display the administration panel, insert JavaScript etc.
     */
    class tishonator_Perspective3DCarouselPlugin {
        
    	/**
    	 * Instance object
    	 *
    	 * @var object
    	 * @see get_instance()
    	 */
    	protected static $instance = NULL;

        /**
         * Constructor
         */
        public function __construct() {}

        /**
         * Setup
         */
        public function setup() {

            register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

            if ( is_admin() ) { // admin actions

                add_action('admin_menu', array(&$this, 'add_admin_page'));

                add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));
            }

            add_action( 'init', array(&$this, 'register_shortcode') );
        }

        public function register_shortcode() {

            add_shortcode( 'perspective-3d-carousel', array(&$this, 'display_shortcode') );
        }

        public function display_shortcode($atts) {

            $result = '';

            $options = get_option( 'perspec3d_carousel_options' );
            
            if ( ! $options )
                return $result;

            // JS
            wp_register_script('perspective-3d-carousel-js',
                plugins_url('js/perspective-3d-carousel.js', __FILE__), array('jquery'));

            wp_enqueue_script('perspective-3d-carousel-js',
                plugins_url('js/perspective-3d-carousel.js', __FILE__), array('jquery') );

            // FontAwesome
            wp_register_style('font-awesome',
                plugins_url('css/font-awesome.css', __FILE__), true);

            wp_enqueue_style( 'font-awesome', plugins_url('css/font-awesome.css', __FILE__), array( ) );

            // CSS
            wp_register_style('perspective-3d-carousel_css',
                plugins_url('css/perspective-3d-carousel.css', __FILE__), true);

            wp_enqueue_style( 'perspective-3d-carousel_css',
                plugins_url('css/perspective-3d-carousel.css', __FILE__), array( ) );

            $result .= '<div class="cloud9-wrapper"><div id="cloud9-carousel">';

            for ( $slideNumber = 1; $slideNumber <= 4; ++$slideNumber ) {

                $slideTitle = array_key_exists('slide_' . $slideNumber . '_title', $options)
                                ? $options[ 'slide_' . $slideNumber . '_title' ] : '';

                $slideText = array_key_exists('slide_' . $slideNumber . '_text', $options)
                                ? $options[ 'slide_' . $slideNumber . '_text' ] : '';

                $slideImage = array_key_exists('slide_' . $slideNumber . '_image', $options)
                                ? $options[ 'slide_' . $slideNumber . '_image' ] : '';

                if ( $slideTitle || $slideText || $slideImage ) {

                    $result .= '<div class="cloud9-item">';
                    if ($slideTitle != '') {

                        $result .= '<h2>' . esc_attr($slideTitle) . '</h2>';
                    }

                    if ($slideImage) {
                        $result .= '<img src="' . esc_attr($slideImage) . '" alt="'
                            . esc_attr($slideTitle) . '" />';
                    }

                    if ($slideText) {
                        $result .= '<p>' . esc_attr($slideText) . '</p>';
                    }

                    $result .= '</div>'; // .cloud9-item

                }
            }

            $result .= '<button class="cloud9-nav noselect left">';
            $result .= '<i class="fa fa-angle-double-left" aria-hidden="true"></i>';
            $result .= '</button>';
            $result .= '<button class="cloud9-nav noselect right">';
            $result .= '<i class="fa fa-angle-double-right" aria-hidden="true"></i>';
            $result .= '</button>';

            $result .= '</div>'; // #cloud9-carousel
            $result .= '</div>'; // .cloud9-wrapper

            return $result;
        }

        public function admin_scripts($hook) {

            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');

            wp_register_script( 'perspective-3d_upload_media',
                plugins_url('js/perspective-3d-carousel-upload-media.js', __FILE__),
                array('jquery') );
            wp_enqueue_script('perspective-3d_upload_media');

            wp_enqueue_style('thickbox');
        }

    	/**
    	 * Used to access the instance
         *
         * @return object - class instance
    	 */
    	public static function get_instance() {

    		if ( NULL === self::$instance ) {
                self::$instance = new self();
            }

    		return self::$instance;
    	}

        /**
         * Unregister plugin settings on deactivating the plugin
         */
        public function deactivate() {

            unregister_setting('perspec3d_carousel', 'perspec3d_carousel_options');
        }

        /** 
         * Print the Section text
         */
        public function print_section_info() {}

        public function admin_init_settings() {
            
            register_setting('perspec3d_carousel', 'perspec3d_carousel_options');

            // add separate sections for each of Sliders
            add_settings_section( 'perspec3d_carousel_section',
                __( 'Slider Settings', 'perspective-3d-carousel' ),
                array(&$this, 'print_section_info'),
                'perspec3d_carousel' );

            for ( $i = 1; $i <= 4; ++$i ) {

                // Slide Title
                add_settings_field(
                    'slide_' . $i . '_title',
                    sprintf( __( 'Slide %s Title', 'perspective-3d-carousel' ), $i ),
                    array(&$this, 'input_callback'),
                    'perspec3d_carousel',
                    'perspec3d_carousel_section',
                    [ 'id' => 'slide_' . $i . '_title',
                      'page' =>  'perspec3d_carousel_options' ]
                );

                // Slide Text
                add_settings_field(
                    'slide_' . $i . '_text',
                    sprintf( __( 'Slide %s Text', 'perspective-3d-carousel' ), $i ),
                    array(&$this, 'textarea_callback'),
                    'perspec3d_carousel',
                    'perspec3d_carousel_section',
                    [ 'id' => 'slide_' . $i . '_text',
                      'page' =>  'perspec3d_carousel_options' ]
                );

                // Slide Image
                add_settings_field(
                    'slide_' . $i . '_image',
                    sprintf( __( 'Slide %s Image', 'perspective-3d-carousel' ), $i ),
                    array(&$this, 'image_callback'),
                    'perspec3d_carousel',
                    'perspec3d_carousel_section',
                    [ 'id' => 'slide_' . $i . '_image',
                      'page' =>  'perspec3d_carousel_options' ]
                );
            }
        }

        public function textarea_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field

            $fieldValue = $options && $args['id'] && array_key_exists(esc_attr( $args['id'] ), $options)
                                ? $options[ esc_attr( $args['id'] ) ] : '';
            ?>

            <textarea id="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                name = "<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                rows="10" cols="39"><?php echo esc_attr($fieldValue); ?></textarea>
            <?php
        }

        public function input_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field
            $fieldValue = ($options && $args['id'] && array_key_exists(esc_attr( $args['id'] ), $options))
                                ? $options[ esc_attr( $args['id'] ) ] : 
                                    (array_key_exists('default_val', $args) ? $args['default_val'] : '');
            ?>

            <input type="text" id="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                name="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                class="regular-text"
                value="<?php echo esc_attr( $fieldValue ); ?>" />
<?php
        }

        public function image_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field

            $fieldValue = $options && $args['id'] && array_key_exists(esc_attr( $args['id'] ), $options)
                                ? $options[ esc_attr( $args['id'] ) ] : '';
            ?>

            <input type="text" id="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                name="<?php echo esc_attr($args['page'] . '[' . $args['id'] . ']' ); ?>"
                class="regular-text"
                value="<?php echo esc_attr( $fieldValue ); ?>" />
            <input class="perspective3d-upload_image_button button button-primary" type="button"
                   value="<?php _e('Change Image', 'perspective-3d-carousel'); ?>" />

            <p><img class="slider-img-preview" <?php if ( $fieldValue ) : ?> src="<?php echo esc_attr($fieldValue); ?>" <?php endif; ?> style="max-width:300px;height:auto;" /><p>
<?php         
        }

        public function add_admin_page() {

            add_menu_page( __('Perspective 3D Carousel Settings', 'perspective-3d-carousel'),
                __('Perspective 3D Carousel', 'perspective-3d-carousel'), 'manage_options',
                'perspective-3d-carousel.php', array(&$this, 'show_settings'),
                'dashicons-format-gallery', 6 );

            //call register settings function
            add_action( 'admin_init', array(&$this, 'admin_init_settings') );
        }

        /**
         * Display the settings page.
         */
        public function show_settings() { ?>

            <div class="wrap">
                <div id="icon-options-general" class="icon32"></div>

                <div class="notice notice-info"> 
                    <p><strong><?php _e('Upgrade to Perspective 3D Carousel PRO Plugin', 'perspective-3d-carousel'); ?>:</strong></p>
                    <ul>
                        <li><?php _e('Configure Up to 10 Different Sliders', 'perspective-3d-carousel'); ?></li>
                        <li><?php _e('Insert Up to 10 Slides per Slider', 'perspective-3d-carousel'); ?></li>
                        <li><?php _e('Slide Buttons', 'perspective-3d-carousel'); ?></li>
                        <li><?php _e('Slider Settings: Height, Sliding Delay, Color Options', 'perspective-3d-carousel'); ?></li>
                    </ul>
                    <a href="https://tishonator.com/plugins/perspective-3d-carousel" class="button-primary">
                        <?php _e('Upgrade to Perspective 3D Carousel PRO Plugin', 'perspective-3d-carousel'); ?>
                    </a>
                    <p></p>
                </div>

                <h2><?php _e('Perspective 3D Carousel Settings', 'perspective-3d-carousel'); ?></h2>

                <form action="options.php" method="post">
                    <?php settings_fields('perspec3d_carousel'); ?>
                    <?php do_settings_sections('perspec3d_carousel'); ?>
                    
                    <h3>
                      Usage
                    </h3>
                    <p>
                        <?php _e('Use the shortcode', 'perspective-3d-carousel'); ?> <code>[perspective-3d-carousel]</code> <?php echo _e( 'to display Slider to any page or post.', 'perspective-3d-carousel' ); ?>
                    </p>
                    <?php submit_button(); ?>
              </form>
            </div>
    <?php
        }
    }

endif; // tishonator_Perspective3DCarouselPlugin

add_action('plugins_loaded', array( tishonator_Perspective3DCarouselPlugin::get_instance(), 'setup' ), 10);
