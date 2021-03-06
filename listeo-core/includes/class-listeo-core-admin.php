<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Listeo_Core_Admin {

    /**
     * The single instance of WordPress_Plugin_Template_Settings.
     * @var     object
     * @access  private
     * @since   1.0.0
     */
    private static $_instance = null;

    /**
     * The main plugin object.
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $parent = null;


    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * Prefix for plugin settings.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();

    public function __construct ( $parent ) {

        $this->parent = $parent;
        $this->_token = 'listeo';

        
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

        $this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';



        $this->base = 'listeo_';

        // Initialise settings
        add_action( 'init', array( $this, 'init_settings' ), 11 );

        // Register plugin settings
        add_action( 'admin_init' , array( $this, 'register_settings' ) );

        // Add settings page to menu
        add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );

        // Add settings link to plugins page
        //add_filter( 'plugin_action_links_' . plugin_basename( 'listeo_core' ) , array( $this, 'add_settings_link' ) );
        add_action( 'current_screen', array( $this, 'conditional_includes' ) );
    }

    /**
     * Initialise settings
     * @return void
     */
    public function init_settings () {
        $this->settings = $this->settings_fields();

    }


    /**
     * Include admin files conditionally.
     */
    public function conditional_includes() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }
        switch ( $screen->id ) {
            case 'options-permalink':
                include 'class-listeo-core-permalinks.php';
                break;
        }
    }


    /**
     * Add settings page to admin menu
     * @return void
     */
    public function add_menu_item () {
        $page = add_menu_page( __( 'Listeo Core ', 'listeo_core' ) , __( 'Listeo Core', 'listeo_core' ) , 'manage_options' , $this->_token . '_settings' ,  array( $this, 'settings_page' ) );
        add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );

// submit_listing
// browse_listing
// Registration
// Booking
// Pages
// Emails
        add_submenu_page($this->_token . '_settings', 'Map Settings', 'Map Settings', 'manage_options', 'listeo_settings&tab=maps',  array( $this, 'settings_page' ) ); 
        
        add_submenu_page($this->_token . '_settings', 'Submit Listing', 'Submit Listing', 'manage_options', 'listeo_settings&tab=submit_listing',  array( $this, 'settings_page' ) ); 
        
        add_submenu_page($this->_token . '_settings', 'Single Listing', 'Single Listing', 'manage_options', 'listeo_settings&tab=single',  array( $this, 'settings_page' ) );   
         
        add_submenu_page($this->_token . '_settings', 'Booking Settings', 'Booking Settings', 'manage_options', 'listeo_settings&tab=booking',  array( $this, 'settings_page' ) );   
        
        add_submenu_page($this->_token . '_settings', 'Browse Listings', 'Browse Listings', 'manage_options', 'listeo_settings&tab=browse',  array( $this, 'settings_page' ) );   
        
        add_submenu_page($this->_token . '_settings', 'Registration', 'Registration', 'manage_options', 'listeo_settings&tab=registration',  array( $this, 'settings_page' ) );   
        
        add_submenu_page($this->_token . '_settings', 'Pages', 'Pages', 'manage_options', 'listeo_settings&tab=pages',  array( $this, 'settings_page' ) ); 
        
        add_submenu_page($this->_token . '_settings', 'Emails', 'Emails', 'manage_options', 'listeo_settings&tab=emails',  array( $this, 'settings_page' ) ); 
        
        add_submenu_page($this->_token . '_settings', 'Users Conversation', 'Users Conversation', 'manage_options', 'user-conversation',  array( $this, 'user_conversation_fun' ) ); 

        add_submenu_page(NULL, 'Single Users Conversation', 'Single Users Conversation', 'manage_options', 'single-user-conversation',  array( $this, 'single_user_conversation_fun' ) ); 
    }

    /**
    * Include User Conversation Page
    */
    public function user_conversation_fun(){
        require_once REALTEO_PLUGIN_DIR . '/includes/user_conversation.php';
    }

    /**
    * Include Single User Conversation Page
    */
    public function single_user_conversation_fun(){
        require_once REALTEO_PLUGIN_DIR . '/includes/single-user_conversation.php';
    }

    /**
     * Load settings JS & CSS
     * @return void
     */
    public function settings_assets () {

        // We're including the farbtastic script & styles here because they're needed for the colour picker
        // If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
        wp_enqueue_style( 'farbtastic' );
        wp_enqueue_script( 'farbtastic' );

        // We're including the WP media scripts here because they're needed for the image upload field
        // If you're not including an image upload then you can leave this function call out
        wp_enqueue_media();

        wp_register_script( $this->_token . '-settings-js', $this->assets_url . 'js/settings' . $this->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
        wp_enqueue_script( $this->_token . '-settings-js' );
        

    }


    /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields () {

        $settings['general'] = array(
            'title'                 => __( 'General', 'listeo_core' ),
            'description'           => __( 'General Listeo settings.', 'listeo_core' ),
            'fields'                => array(
               
                array(
                    'label'      => __('Clock format', 'listeo_core'),
                    'description'      => __('Set 12/24 clock for timepickers', 'listeo_core'),
                    'id'        => 'clock_format',
                    'type'      => 'radio',
                    'options'   => array( 
                            '12' => '12H', 
                            '24' => '24H' 
                        ),
                    'default'   => '12'
                ),
                array(
                    'label'      => __('Date format separator', 'listeo_core'),
                    'description'      => __('Choose hyphen (-), slash (/), or dot (.)', 'listeo_core'),
                    'id'        => 'date_format_separator',
                    'type'      => 'text',
                    'default'   => '/'
                ),
                array(
                    'label'      => __('Commission rate', 'listeo_core'),
                    'description'      => __('Set commision % for bookings', 'listeo_core'),
                    'id'        => 'commission_rate',
                    'type'      => 'number',
                    'placeholder'      => 'Put just a number',
                    'default'   => '10'
                ),
                array(
                    'label'      => __('Currency', 'listeo_core'),
                    'description'      => __('Choose a currency used.', 'listeo_core'),
                    'id'        => 'currency', //each field id must be unique
                    'type'      => 'select',
                    'options'   => array(
                            'none' => esc_html__( 'Disable Currency Symbol', 'listeo_core' ),
                            'USD' => esc_html__( 'US Dollars', 'listeo_core' ),
                            'AED' => esc_html__( 'United Arab Emirates Dirham', 'listeo_core' ),
                            'ARS' => esc_html__( 'Argentine Peso', 'listeo_core' ),
                            'AUD' => esc_html__( 'Australian Dollars', 'listeo_core' ),
                            'BDT' => esc_html__( 'Bangladeshi Taka', 'listeo_core' ),
                            'BHD' => esc_html__( 'Bahraini Dinar', 'listeo_core' ),
                            'BRL' => esc_html__( 'Brazilian Real', 'listeo_core' ),
                            'BGN' => esc_html__( 'Bulgarian Lev', 'listeo_core' ),
                            'CAD' => esc_html__( 'Canadian Dollars', 'listeo_core' ),
                            'CLP' => esc_html__( 'Chilean Peso', 'listeo_core' ),
                            'CNY' => esc_html__( 'Chinese Yuan', 'listeo_core' ),
                            'COP' => esc_html__( 'Colombian Peso', 'listeo_core' ),
                            'CZK' => esc_html__( 'Czech Koruna', 'listeo_core' ),
                            'DKK' => esc_html__( 'Danish Krone', 'listeo_core' ),
                            'DOP' => esc_html__( 'Dominican Peso', 'listeo_core' ),
                            'MAD' => esc_html__( 'Moroccan Dirham', 'listeo_core' ),
                            'EUR' => esc_html__( 'Euros', 'listeo_core' ),
                            'GHS' => esc_html__( 'Ghanaian Cedi', 'listeo_core' ),
                            'HKD' => esc_html__( 'Hong Kong Dollar', 'listeo_core' ),
                            'HRK' => esc_html__( 'Croatia kuna', 'listeo_core' ),
                            'HUF' => esc_html__( 'Hungarian Forint', 'listeo_core' ),
                            'ISK' => esc_html__( 'Icelandic krona', 'listeo_core' ),
                            'IDR' => esc_html__( 'Indonesia Rupiah', 'listeo_core' ),
                            'INR' => esc_html__( 'Indian Rupee', 'listeo_core' ),
                            'NPR' => esc_html__( 'Nepali Rupee', 'listeo_core' ),
                            'ILS' => esc_html__( 'Israeli Shekel', 'listeo_core' ),
                            'JPY' => esc_html__( 'Japanese Yen', 'listeo_core' ),
                            'JOD' => esc_html__( 'Jordanian Dinar', 'listeo_core' ),
                            'KZT' => esc_html__( 'Kazakhstani tenge', 'listeo_core' ),
                            'KIP' => esc_html__( 'Lao Kip', 'listeo_core' ),
                            'KRW' => esc_html__( 'South Korean Won', 'listeo_core' ),
                            'LKR' => esc_html__( 'Sri Lankan Rupee', 'listeo_core' ),
                            'MYR' => esc_html__( 'Malaysian Ringgits', 'listeo_core' ),
                            'MXN' => esc_html__( 'Mexican Peso', 'listeo_core' ),
                            'NGN' => esc_html__( 'Nigerian Naira', 'listeo_core' ),
                            'NOK' => esc_html__( 'Norwegian Krone', 'listeo_core' ),
                            'NZD' => esc_html__( 'New Zealand Dollar', 'listeo_core' ),
                            'PYG' => esc_html__( 'Paraguayan Guaran??', 'listeo_core' ),
                            'PHP' => esc_html__( 'Philippine Pesos', 'listeo_core' ),
                            'PLN' => esc_html__( 'Polish Zloty', 'listeo_core' ),
                            'GBP' => esc_html__( 'Pounds Sterling', 'listeo_core' ),
                            'RON' => esc_html__( 'Romanian Leu', 'listeo_core' ),
                            'RUB' => esc_html__( 'Russian Ruble', 'listeo_core' ),
                            'SGD' => esc_html__( 'Singapore Dollar', 'listeo_core' ),
                            'ZAR' => esc_html__( 'South African rand', 'listeo_core' ),
                            'SEK' => esc_html__( 'Swedish Krona', 'listeo_core' ),
                            'CHF' => esc_html__( 'Swiss Franc', 'listeo_core' ),
                            'TWD' => esc_html__( 'Taiwan New Dollars', 'listeo_core' ),
                            'THB' => esc_html__( 'Thai Baht', 'listeo_core' ),
                            'TRY' => esc_html__( 'Turkish Lira', 'listeo_core' ),
                            'UAH' => esc_html__( 'Ukrainian Hryvnia', 'listeo_core' ),
                            'USD' => esc_html__( 'US Dollars', 'listeo_core' ),
                            'VND' => esc_html__( 'Vietnamese Dong', 'listeo_core' ),
                            'EGP' => esc_html__( 'Egyptian Pound', 'listeo_core' ),
                            'ZMK' => esc_html__( 'Zambian Kwacha', 'listeo_core' )
                        ),
                    'default'       => 'USD'
                ),      
                array(
                    'label'      => __('Currency position', 'listeo_core'),
                    'description'      => __('Set currency symbol before or after', 'listeo_core'),
                    'id'        => 'currency_postion',
                    'type'      => 'radio',
                    'options'   => array( 
                            'after' => 'After', 
                            'before' => 'Before' 
                        ),
                    'default'   => 'after'
                ),
               
                array(
                    'label'      => __('By default sort listings by:', 'listeo_core'),
                    'description'      => __('sort by', 'listeo_core'),
                    'id'        => 'sort_by',
                    'type'      => 'select',
                    'options'   => array( 
                            'date-asc' => esc_html__( 'Oldest Listings', 'listeo_core' ),
                            'date-desc' => esc_html__( 'Newest Listings', 'listeo_core' ),
                            'featured' => esc_html__( 'Featured', 'listeo_core' ),
							   'highest-rated' => esc_html__( 'Highest Rated', 'listeo_core' ),
                            'reviewed' => esc_html__( 'Most Reviewed Rated', 'listeo_core' ),
                            'price-asc' => esc_html__( 'Price Low to High', 'listeo_core' ),
                            'price-desc' => esc_html__( 'Price High to Low', 'listeo_core' ),
                            'views' => esc_html__( 'Views', 'listeo_core' ),
                            'rand' => esc_html__( 'Random', 'listeo_core' ),
                        ),
                    'default'   => 'date-desc'
                ),
                array(
                    'label'      => __('Region in listing permalinks', 'listeo_core'),
                    'description'      => __('By enabling this option the links to properties will <br> be prepended  with regions (e.g /listing/las-vegas/arlo-apartment/).<br> After enabling this go to Settings-> Permalinks and click \' Save Changes \' ', 'listeo_core'),
                    'id'        => 'region_in_links',
                    'type'      => 'checkbox',
                ), 

                array(
                    'label'      => __('Hide owner contact information from not logged in users', 'listeo_core'),
                    'description'      => __('By enabling this option phone and emails fields will be visible only for logged in users', 'listeo_core'),
                    'id'        => 'user_contact_details_visibility',
                     'type'      => 'select',
                    'options'   => array( 
                            'show_logged' => esc_html__( 'Show owner contact information only for logged in users', 'listeo_core' ),
                           // 'show_booked' => esc_html__( 'Show owner contact information only after booking', 'listeo_core' ),
                            'hide_all' => esc_html__( 'Hide all owner contact information', 'listeo_core' ),
                            'show_all' => esc_html__( 'Always show', 'listeo_core' ),
                          
                        ),
                    'default'   => 'hide_logged'
                ),  
                // array(
                //     'label'      => __('Hide all owner contact information', 'listeo_core'),
                //     'description'      => __('Hide all options to contact user', 'listeo_core'),
                //     'id'        => 'user_contact_details_visibility',
                //     'type'      => 'checkbox',
                // ),  


               
            )
        ); 

        $settings['maps'] = array(
            'title'                 => __( 'Map Settings', 'listeo_core' ),
            'description'           => __( 'Settings for map usage.', 'listeo_core' ),
            'fields'                => array(


                array(
                    'label' => __( 'Restrict search results to one country (works only with Google Maps)', 'listeo_core' ),
                    'description' => __( 'Put symbol of country you want to restrict your results to (eg. uk for United Kingdon). Leave empty to search whole world.', 'listeo_core' ),
                    'id'   => 'maps_limit_country', //field id must be unique
                    'type' => 'text',
                ),
                array(
                    'label' => __( 'Listings map center point', 'listeo_core' ),
                    'description' => __( 'Write latitude and longitude separated by come, for example -34.397,150.644', 'listeo_core' ),
                    'id'   => 'map_center_point', //field id must be unique
                    'type' => 'text',
                    'default' => "52.2296756,21.012228700000037",    
                ), 
                array(
                    'label'         => __( 'Autofit all markers on map', 'listeo_core' ),
                    'description'   => __( 'Disable checkbox to set the zoom of map manually', 'listeo_core' ),
                    'id'            => 'map_autofit', //field id must be unique
                    'type'          => 'checkbox',
                    'default'          => 'on',
                ),
                array(
                    'label'         => __( 'Automatically locate users on page load', 'listeo_core' ),
                    'description'   => __( 'You need to be on HTTPS, this uses html5 geolocation feature https://www.w3schools.com/html/html5_geolocation.asp', 'listeo_core' ),
                    'id'            => 'map_autolocate', //field id must be unique
                    'type'          => 'checkbox',
                    'default'          => 'off',
                ),
                array(
                    'label'         => __( 'Zoom level for Listings Map', 'listeo_core' ),
                    'description'   => __( 'Put number between 0-20, works only with autofit disabled', 'listeo_core' ),
                    'id'            => 'map_zoom_global', //field id must be unique
                    'type'          => 'text',
                    'default'       => 9
                ),
                array(
                    'label'         => __( 'Zoom level for Single Listing Map', 'listeo_core' ),
                    'description'   => __( 'Put number between 0-20', 'listeo_core' ),
                    'id'            => 'map_zoom_single', //field id must be unique
                    'type'          => 'text',
					'default'       => 9
                ),

                array(
                    'label'      => __('Maps Provider', 'listeo_core'),
                    'description'      => __('Choose which service you want to use for maps', 'listeo_core'),
                    'id'        => 'map_provider',
                    'type'      => 'radio',
                    'options'   => array( 
                            'osm' => esc_html__( 'OpenStreetMap', 'listeo_core' ),
                            'google' => __( 'Google Maps <a href="http://www.docs.purethemes.net/listeo/knowledge-base/getting-google-maps-api-key/">(requires API key)</a>', 'listeo_core' ),
                            'mapbox' => __( 'MapBox <a href="https://account.mapbox.com/access-tokens/create">(requires API key)</a>', 'listeo_core' ),
                            'bing' => __( 'Bing <a href="https://www.microsoft.com/en-us/maps/choose-your-bing-maps-api">(requires API key)</a>', 'listeo_core' ),
                            'thunderforest' => __( 'ThunderForest <a href="https://manage.thunderforest.com/">(requires API key)</a>', 'listeo_core' ),
                            'here' => __( 'HERE <a href="https://developer.here.com/lp/mapAPIs?create=Freemium-Basic&keepState=true&step=account">(requires API key)</a>', 'listeo_core' ),
                            // 'esri' => esc_html__( 'ESRI (requires registration)', 'listeo_core' ),
                            // 'stamen' => esc_html__( 'Stamen', 'listeo_core' ),  
                        ),
                    'default'   => 'osm'
                ),

                //geocoding providers
                
                array(
                    'label' => __( 'Google Maps API key', 'listeo_core' ),
                    'description' => __( 'Generate API key for google maps functionality (can be domain restricted).', 'listeo_core' ),
                    'id'   => 'maps_api', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'Google Maps API key', 'listeo_core' )
                ),

                array(
                    'label' => __( 'MapBox Access Token', 'listeo_core' ),
                    'description' => __( 'Generate Access Token for MapBox', 'listeo_core' ),
                    'id'   => 'mapbox_access_token', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'MapBox Access Token key', 'listeo_core' )
                ),
                array(
                    'label' => __( 'MapBox Retina Tiles', 'listeo_core' ),
                    'description' => __( 'Enable to use Retina Tiles. Might affect map loading speed.', 'listeo_core' ),
                    'id'   => 'mapbox_retina', //field id must be unique
                    'type' => 'checkbox',
                    
                ),
                array(
                    'label' => __( 'Bing Maps Key', 'listeo_core' ),
                    'description' => __( 'API key for Bing Maps', 'listeo_core' ),
                    'id'   => 'bing_maps_key', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'Bing Maps API Key', 'listeo_core' )
                ),
                array(
                    'label' => __( 'ThunderForest API Key', 'listeo_core' ),
                    'description' => __( 'API key for ThunderForest', 'listeo_core' ),
                    'id'   => 'thunderforest_api_key', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'ThunderForest API Key', 'listeo_core' )
                ), 
                array(
                    'label' => __( 'HERE App ID', 'listeo_core' ),
                    'description' => __( 'HERE App ID', 'listeo_core' ),
                    'id'   => 'here_app_id', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'HERE Maps API Key', 'listeo_core' )
                ), 
                array(
                    'label' => __( 'HERE App Code', 'listeo_core' ),
                    'description' => __( 'App code key for HERE Maps', 'listeo_core' ),
                    'id'   => 'here_app_code', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'HERE App Code', 'listeo_core' )
                ),

                array(
                    'label' =>  '',
                    'description' =>  __('Radius search settings', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_radius',
                    'description' => 'Radius search settings<br><span style="font-size:13px">To use the Search by Radius feature, you need to create Google Maps API key for geocoding</span>',
                ), 
                array(
                    'label' => __( 'Google Maps API key for server side geocoding', 'listeo_core' ),
                    'description' => __( 'Generate API key for geocoding search functionality (without any domain/key restriction).', 'listeo_core' ),
                    'id'   => 'maps_api_server', //field id must be unique
                    'type' => 'text',
                    'placeholder'   => __( 'Google Maps API key', 'listeo_core' )
                ),

                array(
                    'label'      => __('Radius slider default state', 'listeo_core'),
                    'description'      => __('Choose radius search slider', 'listeo_core'),
                    'id'        => 'radius_state',
                    'type'      => 'select',
                    'options'   => array( 
                            'disabled' => esc_html__( 'Disabled by default', 'listeo_core' ),
                            'enabled' => esc_html__( 'Enabled by default', 'listeo_core' ),
                        ),
                    'default'   => 'km'
                ),  
                array(
                    'label'      => __('Radius search unit', 'listeo_core'),
                    'description'      => __('Choose a unit', 'listeo_core'),
                    'id'        => 'radius_unit',
                    'type'      => 'select',
                    'options'   => array( 
                            'km' => esc_html__( 'km', 'listeo_core' ),
                            'miles' => esc_html__( 'miles', 'listeo_core' ),
                        ),
                    'default'   => 'km'
                ), 
                 array(
                    'label' => __( 'Default radius search value', 'listeo_core' ),
                    'description' => __( 'Set default radius for search, leave empty to disable default radius search.', 'listeo_core' ),
                    'id'   => 'maps_default_radius', //field id must be unique
                    'type' => 'text',
                    'default'   => 50
                ),



            )
        );
        
        $settings['submit_listing'] = array(
            'title'                 => __( 'Submit Listing', 'listeo_core' ),
            'description'           => __( 'Settings for single listing view.', 'listeo_core' ),
            'fields'                => array(
                  array(
                    'id'            => 'listing_types',
                    'label'         => __( 'Supported listing types', 'listeo_core' ),
                    'description'   => __( 'If you select one it will be the default type and Choose Listing Type step in Submit Listing form will be skipped. If you deselect all the default type will always be Service', 'listeo_core' ),
                    'type'          => 'checkbox_multi',
                    'options'       => array( 
                        'service' => esc_html__('Service', 'listeo_core' ), 
                        'rental' => esc_html__('Rental', 'listeo_core' ), 
                        'event' => esc_html__('Event', 'listeo_core' )
                    ), //service

                    'default'       => array( 'service', 'rental', 'event' )
                ),
                array(
                    'label'      => __('Disable Bookings module', 'listeo_core'),
                    'description'      => __('By default bookings are enabled, check this checkbox to disable it and remove booking options from Submit Listing', 'listeo_core'),
                    'id'        => 'bookings_disabled',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Admin approval required', 'listeo_core'),
                    'description'      => __('Require admin approval for any new listings added', 'listeo_core'),
                    'id'        => 'new_listing_requires_approval',
                    'type'      => 'checkbox',
                ),          
                array(
                    'label'      => __('Notify admin by email about new listing waiting for approval', 'listeo_core'),
                    'description'      => __('Send email about any new listings added', 'listeo_core'),
                    'id'        => 'new_listing_admin_notification',
                    'type'      => 'checkbox',
                ),                
                array(
                    'label'      => __('Paid listings', 'listeo_core'),
                    'description'      => __('Adding listings by users will require purchasing a Listing Package', 'listeo_core'),
                    'id'        => 'new_listing_requires_purchase',
                    'type'      => 'checkbox',
                ),     
                array(
                    'label'         => __( 'Allow packages to only be purchased once per client', 'listeo_core' ),
                    'description'   => __( 'Selected packages can be bought only once, useful for demo/free packages', 'listeo_core' ),
                    'id'            => 'buy_only_once',
                    'type'          => 'checkbox_multi',
                    'options'       => listeo_core_get_listing_packages_as_options(),
                    //'options'       => array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
                    'default'       => array( )
                ),           
                // array(
                //     'label'      => __('Remove Preview step from Submit Listing', 'listeo_core'),
                //     'description'      => __('Enable this option to remove Preview step', 'listeo_core'),
                //     'id'        => 'new_listing_preview',
                //     'type'      => 'checkbox',
                // ),
                array(
                    'label' => __( 'Listing duration', 'listeo_core' ),
                    'description' => __( 'Set default listing duration (if not set via listing package). Set to 0 if you don\'t want listings to have an expiration date.', 'listeo_core' ),
                    'id'   => 'default_duration', //field id must be unique
                    'type' => 'text',
                    'default' => '30',    
                ),       
                array(
                    'label' => __( 'Listing images upload limit', 'listeo_core' ),
                    'description' => __( 'Number of images that can be uploaded to one listing', 'listeo_core' ),
                    'id'   => 'max_files', //field id must be unique
                    'type' => 'text',
                    'default' => '10',    
                ),  
                // array(
                //     'label'      => __('Create and assign Region based on Google geocoding', 'listeo_core'),
                //     'description'      => __("Enabling this field will use 'state_long' value from geolocalization request to add new term for Region taxonomy and assign listing to this term.", 'listeo_core'),
                //     'id'        => 'auto_region',
                //     'type'      => 'checkbox',
                // ),   
                array(
                    'label' => __( 'Listing image maximum size (in MB)', 'listeo_core' ),
                    'description' => __( 'Maximum file size to upload ', 'listeo_core' ),
                    'id'   => 'max_filesize', //field id must be unique
                    'type' => 'text',
                    'default' => '2',    
                ),               
                array(
                    'label' => __( 'Submit Listing map center point', 'listeo_core' ),
                    'description' => __( 'Write latitude and longitude separated by come, for example -34.397,150.644', 'listeo_core' ),
                    'id'   => 'submit_center_point', //field id must be unique
                    'type' => 'text',
                    'default' => "52.2296756,21.012228700000037",    
                ),
                
            )
        );
        
        $settings['single'] = array(
            'title'                 => __( 'Single Listing', 'listeo_core' ),
            'description'           => __( 'Settings for single listing view.', 'listeo_core' ),
            'fields'                => array(
                 array(
                    'id'            => 'gallery_type',
                    'label'         => __( 'Default Gallery Type', 'listeo_core' ),
                    'description'   => __( '.', 'listeo_core' ),
                    'type'          => 'select',
                    'options'       => array( 
                            'top'       => __('Gallery on top', 'listeo_core' ),
                            'content'   => __('Gallery in content', 'listeo_core' ),  
                    ),
                    'default'       => 'top'
                ),
                array(
                    'id'            => 'owners_can_review',
                    'label'         => __( 'Allow owners to add reviews', 'listeo_core' ),
                    'type'          => 'checkbox',
                ), 
                array(
                    'id'            => 'reviews_only_booked',
                    'label'         => __( 'Allow reviewing only to users who made a booking', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'review_photos_disable',
                    'label'         => __( 'Disable "Add Photos" option in the review form', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'claim_page_button',
                    'label'         => __( 'Show "Claim it now" button on listing', 'listeo_core' ),
                    'type'          => 'checkbox',
                    'description'   => __( 'Please also set your Claim Listing Page in Pages tab in Listeo Core', 'listeo_core' ),
                ),
                array(
                    'id'            => 'disable_reviews',
                    'label'         => __( 'Disable reviews on listings', 'listeo_core' ),
                    'type'          => 'checkbox',
				 ), 
                array(
                    'id'            => 'disable_address',
                    'label'         => __( 'Hide real address on listings and lists', 'listeo_core' ),
                    'type'          => 'checkbox',  
                ),
             
            )
        ); 

        $settings['booking'] = array(
            'title'                 => __( 'Booking', 'listeo_core' ),
            'description'           => __( 'Settings related to booking.', 'listeo_core' ),
            'fields'                => array(
                
                array(
				    'id'            => 'remove_guests',
                    'label'         => __( 'Remove Guests options from all booking widgets', 'listeo_core' ),
                    'description'   => __( 'Guest picker will be removed from booking widget', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),

                array(
                    'id'            => 'owners_can_book',
                    'label'         => __( 'Allow owners to make bookings', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),  

                array(
                    'id'            => 'add_address_fields_booking_form',
                    'label'         => __( 'Add address field to booking confirmation form', 'listeo_core' ),
                    'type'          => 'checkbox',
                    'description'   => __( 'Used in WooCommerce Orders and required for some payment gateways ', 'listeo_core' ),
                ), 

                

                array(
                    'id'            => 'disable_payments',
                    'label'         => __( 'Disable payments in bookings', 'listeo_core' ),
                    'description'   => __( 'Bookings will have prices but the payments won\'t be handled by the site. Disable Wallet page in Liste Core -> Pages', 'listeo_core' ),
                    'type'          => 'checkbox',
                ), 

            )
        );
        

        $settings['browse'] = array(
            'title'                 => __( 'Browse Listing', 'listeo_core' ),
            'description'           => __( 'Settings for browse/archive listing view.', 'listeo_core' ),
            'fields'                => array(
                array(
                    'id'            => 'ajax_browsing',
                    'label'         => __( 'Ajax based listing browsing', 'listeo_core' ),
                    'description'   => __( '.', 'listeo_core' ),
                    'type'          => 'select',
                    'options'       => array( 
                            'on'    => __('Enabled', 'listeo_core' ),
                            'off'   => __('Disabled', 'listeo_core' ),  
                    ),
                    'default'       => 'on'
                ),
                array(
                    'id'            => 'dynamic_features',
                    'label'         => __( 'Make features taxonomy related to categories', 'listeo_core' ),
                    'description'   => __( 'This option will refresh list of features based on selected category', 'listeo_core' ),
                    'type'          => 'select',
                    'options'       => array( 
                            'on'    => __('Enabled', 'listeo_core' ),
                            'off'   => __('Disabled', 'listeo_core' ),  
                    ),
                    'default'       => 'on'
                ), 
                array(
                    'id'            => 'search_only_address',
                    'label'         => __( 'Restrict location search only to address field', 'listeo_core' ),
                    'description'   => __( 'This option will limit search only to address field if Radius search is not used, otherwise it searches for content and title as well', 'listeo_core' ),
                    'type'          => 'select',
                    'options'       => array( 
                            'on'    => __('Enabled', 'listeo_core' ),
                            'off'   => __('Disabled', 'listeo_core' ),  
                    ),
                    'default'       => 'off'
                ),
                array(
                    'id'            => 'taxonomy_or_and',
                    'label'         => __( 'For taxonomy search use logical relation:', 'listeo_core' ),
                    'description'   => __( 'This option will limit let you choose search results that have one of the features or all of the features you look for.', 'listeo_core' ),
                    'type'          => 'select',
                    'options'       => array( 
                            'OR'    => __('OR', 'listeo_core' ),
                            'AND'   => __('AND', 'listeo_core' ),  
                    ),
                    'default'       => 'OR'
                ),
            )
        );

        $settings['registration'] = array(
            'title'                 => __( 'Registration', 'listeo_core' ),
            'description'           => __( 'Settings for users registration and login.', 'listeo_core' ),
            'fields'                => array(
                array(
                    'id'            => 'front_end_login',
                    'label'         => __( 'Enable Forced Front End Login', 'listeo_core' ),
                    'description'   => __( 'Enabling this option will redirect all wp-login request to frontend form. Be aware that on some servers or some configuration, especially with security plugins, this might cause a redirect loop, so always test this setting on different browser, while being still logged in Dashboard to have option to disable that if things go wrong.', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'popup_login',
                    'label'         => __( 'Login/Registration Form Type', 'listeo_core' ),
                    'description'   => __( '.', 'listeo_core' ),
                    'type'          => 'select',
                    'options'       => array( 
                            'ajax'       => __('Ajax form in a popup', 'listeo_core' ),
                            'page'   => __('Separate page', 'listeo_core' ),  
                    ),
                    'default'       => 'ajax'
                ),
                 array(
                    'id'            => 'autologin',
                    'label'         => __( 'Automatically login user after successful registration', 'listeo_core' ),
                    'description'   => __( '.', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'privacy_policy',
                    'label'         => __( 'Enable Privacy Policy link in registration form', 'listeo_core' ),
                    'description'   => __( '.', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'recaptcha',
                    'label'         => __( 'Enable reCAPTCHA on registration form', 'listeo_core' ),
                    'description'   => __( 'Check this checkbox to add reCAPTCHA to form. You need to provide API keys for that.', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'recaptcha_reviews',
                    'label'         => __( 'Enable reCAPTCHA on reviews form', 'listeo_core' ),
                    'description'   => __( 'Check this checkbox to add reCAPTCHA to Reviews form. You need to provide API keys for that.', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'recaptcha_sitekey',
                    'label'         => __( 'reCAPTCHA Site Key', 'listeo_core' ),
                    'description'   => __( 'Get the sitekey from https://www.google.com/recaptcha/admin#list - use reCaptcha v2', 'listeo_core' ),
                    'type'          => 'text',
                ),
                array(
                    'id'            => 'recaptcha_secretkey',
                    'label'         => __( 'reCAPTCHA Secret Key', 'listeo_core' ),
                    'description'   => __( 'Get the sitekey from https://www.google.com/recaptcha/admin#list - use reCaptcha v2', 'listeo_core' ),
                    'type'          => 'text',
                ),
                array(
                    'id'            => 'registration_hide_role',
                    'label'         => __( 'Hide Role field in Registration Form', 'listeo_core' ),
                    'description'   => __( 'If hidden, set default role in Settings -> General -> New User Default Role', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'registration_hide_username',
                    'label'         => __( 'Hide Username field in Registration Form', 'listeo_core' ),
                    'description'   => __( 'Username will be generated from email address (part before @)', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
               
                array(
                    'id'            => 'display_first_last_name',
                    'label'         => __( 'Display First and Last name fields in registration form', 'listeo_core' ),
                    'description'   => __( 'Adds optional input fields for first and last name', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'display_password_field',
                    'label'         => __('Add Password pickup field to registration form', 'listeo_core'),
                    'description'   => __('Enable to add password field, when disabled it will be randomly generated and sent via email', 'listeo_core'),
                    'type'          => 'checkbox',
                ),
                array(
                    'id'            => 'profile_allow_role_change',
                    'label'         => __('Allow user to change his role in "My Account" page', 'listeo_core'),
                    'description'   => __('Works only for owners and guests', 'listeo_core'),
                    'type'          => 'checkbox',
                )
            )
        );

       $settings['pages'] = array(
            'title'                 => __( 'Pages', 'listeo_core' ),
            'description'           => __( 'Set all pages required in Listeo.', 'listeo_core' ),
            'fields'                => array(
                array(
                    'id'            => 'dashboard_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Dashboard Page' , 'listeo_core' ),
                    'description'   => __( 'Main Dashboard page for user', 'listeo_core' ),
                    'type'          => 'select',
                ),
                array(
                    'id'            => 'messages_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Messages Page' , 'listeo_core' ),
                    'description'   => __( 'Main page for user messages', 'listeo_core' ),
                    'type'          => 'select',
                ),
                array(
                    'id'            => 'bookings_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Bookings Page' , 'listeo_core' ),
                    'description'   => __( 'Page for owners to manage their bookings', 'listeo_core' ),
                    'type'          => 'select',
                ),  
                array(
                    'id'            => 'user_bookings_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'My Bookings Page' , 'listeo_core' ),
                    'description'   => __( 'Page for guest to see their bookings', 'listeo_core' ),
                    'type'          => 'select',
                ), 
                array(
                    'id'            => 'booking_confirmation_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Booking confirmation' , 'listeo_core' ),
                    'description'   => __( 'Displays page for booking confirmation', 'listeo_core' ),
                    'type'          => 'select',
                ), 
                array(
                    'id'            => 'listings_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'My Listings Page' , 'listeo_core' ),
                    'description'   => __( 'Displays or listings added by user', 'listeo_core' ),
                    'type'          => 'select',
                ),    
                array(
                    'id'            => 'wallet_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Wallet Page' , 'listeo_core' ),
                    'description'   => __( 'Displays or owners earnings', 'listeo_core' ),
                    'type'          => 'select',
                ),                
                array(
                    'id'            => 'reviews_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Reviews Page' , 'listeo_core' ),
                    'description'   => __( 'Displays reviews of user properties/o', 'listeo_core' ),
                    'type'          => 'select',
                ),                
                array(
                    'id'            => 'bookmarks_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Bookmarks Page' , 'listeo_core' ),
                    'description'   => __( 'Displays user bookmarks', 'listeo_core' ),
                    'type'          => 'select',
                ),
                array(
                    'id'            => 'submit_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Submit Listing Page' , 'listeo_core' ),
                    'description'   => __( 'Displays submit listing page', 'listeo_core' ),
                    'type'          => 'select',
                ),                
                array(
                    'id'            => 'profile_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'My Profile Page' , 'listeo_core' ),
                    'description'   => __( 'Displays user profile page', 'listeo_core' ),
                    'type'          => 'select',
                ),
                    
                array(
                    'label'          => __('Lost Password Page', 'listeo_core'),
                    'description'          => __('Select page that holds [listeo_lost_password] shortcode', 'listeo_core'),
                    'id'            =>  'lost_password_page',
                    'type'          => 'select',
                    'options'       => listeo_core_get_pages_options(),
                ),                
                array(
                    'label'          => __('Reset Password Page', 'listeo_core'),
                    'description'          => __('Select page that holds [listeo_reset_password] shortcode', 'listeo_core'),
                    'id'            =>  'reset_password_page',
                    'type'          => 'select',
                    'options'       => listeo_core_get_pages_options(),
                ),
                array(
                    'id'            => 'claim_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'Claim Listing Page' , 'listeo_core' ),
                    'description'   => __( 'Displays claim listing form', 'listeo_core' ),
                    'type'          => 'select',
                ),
                array(
                    'id'            => 'orders_page',
                    'label'         => __( 'WooCommerce Orders Page' , 'listeo_core' ),
                    'description'   => __( 'Displays orders page in dashboard menu', 'listeo_core' ),
                    'type'          => 'checkbox',
                ), 
                array(
                    'id'            => 'subscription_page',                    
                    'label'         => __( 'WooCommerce Subscription Page' , 'listeo_core' ),
                    'description'   => __( 'Displays subscription page in dashboard menu (requires WooCommerce Subscription plugin)', 'listeo_core' ),
                    'type'          => 'checkbox',
                ),
				 array(	   
                    'id'            => 'ical_page',
                    'options'       => listeo_core_get_pages_options(),
                    'label'         => __( 'iCal generator' , 'listeo_core' ),
                    'description'   => __( 'Used to generate iCal output', 'listeo_core' ),
                    'type'          => 'select',
                ),

        //         array(
        //             'id'            => 'colour_picker',
        //             'label'         => __( 'Pick a colour', 'listeo_core' ),
        //             'description'   => __( 'This uses WordPress\' built-in colour picker - the option is stored as the colour\'s hex code.', 'listeo_core' ),
        //             'type'          => 'color',
        //             'default'       => '#21759B'
        //         ),
                // array(
                //     'id'            => 'an_image',
                //     'label'         => __( 'An Image' , 'listeo_core' ),
                //     'description'   => __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'listeo_core' ),
                //     'type'          => 'image',
                //     'default'       => '',
                //     'placeholder'   => ''
                // ),
        //         array(
        //             'id'            => 'multi_select_box',
        //             'label'         => __( 'A Multi-Select Box', 'listeo_core' ),
        //             'description'   => __( 'A standard multi-select box - the saved data is stored as an array.', 'listeo_core' ),
        //             'type'          => 'select_multi',
        //             'options'       => array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
        //             'default'       => array( 'linux' )
        //         )
		)	 
    );

       $settings['emails'] = array(
            'title'                 => __( 'Emails', 'listeo_core' ),
            'description'           => __( 'Email settings.', 'listeo_core' ),
            'fields'                => array(
        
                array(
                    'label'  => __('"From name" in email', 'listeo_core'),
                    'description'  => __('The name from who the email is received, by default it is your site name.', 'listeo_core'),
                    'id'    => 'emails_name',
                    'default' =>  get_bloginfo( 'name' ),                
                    'type'  => 'text',
                ),

                array(
                    'label'  => __('"From" email ', 'listeo_core'),
                    'description'  => __('This will act as the "from" and "reply-to" address. This emails should match your domain address', 'listeo_core'),
                    'id'    => 'emails_from_email',
                    'default' =>  get_bloginfo( 'admin_email' ),               
                    'type'  => 'text',
                ),
                
                array(
                    'label' =>  '',
                    'description' =>  __('Registration/Welcome email for new users', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_welcome',
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{site_name},{password},{login}',
                ), 
                array(
                    'label'      => __('Welcome Email Subject', 'listeo_core'),
                    'default'      => __('Welcome to {site_name}', 'listeo_core'),
                    'id'        => 'listing_welcome_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Welcome Email Content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
Welcome to our website.<br>
<ul>
<li>Username: {login}</li>
<li>Password: {password}</li>
</ul>
<br>
Thank you.
<br>")),
                    'id'        => 'listing_welcome_email_content',
                    'type'      => 'editor',
                ),   


                /*----------------*/

                array(
                    'label' =>  '',
                    'description' =>  __('Listing Published notification email', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_published'
                ), 
                array(
                    'label'      => __('Enable listing published notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to listing authors', 'listeo_core'),
                    'id'        => 'listing_published_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Published notification Email Subject', 'listeo_core'),
                    'default'      => __('Your listing was published - {listing_name}', 'listeo_core'),
                    'id'        => 'listing_published_email_subject',
                    'type'      => 'text',

                ),
                 array(
                    'label'      => __('Published notification Email Content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
We are pleased to inform you that your submission '{listing_name}' was just published on our website.<br>
<br>
Thank you.
<br>")),
                    'id'        => 'listing_published_email_content',
                    'type'      => 'editor',
                ),   

                /*----------------New listing notification email' */
                array(
                    'label'      =>  '',
                    'description'      =>  __('New listing notification email', 'listeo_core'),
                    'type'      => 'title',
                    'id'        => 'header_new'
                ), 
                array(
                    'label'      => __('Enable new listing notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to listing authors', 'listeo_core'),
                    'id'        => 'listing_new_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('New listing notification email subject', 'listeo_core'),
                    'default'      => __('Thank you for adding a listing', 'listeo_core'),
                    'id'        => 'listing_new_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('New listing notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    Thank you for submitting your listing '{listing_name}'.<br>
                    <br>")),
                    'id'        => 'listing_new_email_content',
                    'type'      => 'editor',
                ),  

                /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Expired listing notification email', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_expired'
                ), 
                array(
                    'label'      => __('Enable expired listing notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to listing authors', 'listeo_core'),
                    'id'        => 'listing_expired_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Expired listing notification email subject', 'listeo_core'),
                    'default'      => __('Your listing has expired - {listing_name}', 'listeo_core'),
                    'id'        => 'listing_expired_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Expired listing notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    We'd like you to inform you that your listing '{listing_name}' has expired and is no longer visible on our website. You can renew it in your account.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'listing_expired_email_content',
                    'type'      => 'editor',
                ),

                /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Expiring listing in next 5 days notification email ', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_expiring_soon'
                ), 
                array(
                    'label'      => __('Enable Expiring soon listing notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to listing authors', 'listeo_core'),
                    'id'        => 'listing_expiring_soon_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Expiring soon listing notification email subject', 'listeo_core'),
                    'default'      => __('Your listing is expiring in 5 days - {listing_name}', 'listeo_core'),
                    'id'        => 'listing_expiring_soon_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Expiring soon listing notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    We'd like you to inform you that your listing '{listing_name}' is expiring in 5 days.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'listing_expiring_soon_email_content',
                    'type'      => 'editor',
                ),  
           
           /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Booking confirmation to user (paid - not instant booking) ', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_booking_confirmation'
                ), 
                array(
                    'label'      => __('Enable Booking confirmation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to users after they request booking', 'listeo_core'),
                    'id'        => 'booking_user_waiting_approval_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Booking confirmation notification email subject', 'listeo_core'),
                    'default'      => __('Thank you for your booking - {listing_name}', 'listeo_core'),
                     'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details},
                        ,{dates},{user_message},{service},{details},{client_first_name},{client_last_name},{client_email},{client_phone},{billing_address},{billing_postcode},{billing_city},{billing_country},{price}',
                    'id'        => 'booking_user_waiting_approval_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Booking confirmation notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    Thank you for your booking request on {listing_name} for {dates}. Please wait for confirmation and further instructions.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'booking_user_waiting_approval_email_content',
                    'type'      => 'editor',
                ),   
                /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Instant Booking confirmation to user', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_instant_booking_confirmation'
                ), 
                array(
                    'label'      => __('Enable Instant Booking confirmation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to users after they request booking', 'listeo_core'),
                    'id'        => 'instant_booking_user_waiting_approval_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Instant Booking confirmation notification email subject', 'listeo_core'),
                    'default'      => __('Thank you for your booking - {listing_name}', 'listeo_core'),
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details}, 
					{payment_url},{expiration},{dates},{children},{adults},{user_message},{tickets},{service},{details},{client_first_name},{client_last_name},{client_email},{client_phone},{billing_address},{billing_postcode},{billing_city},{billing_country},{price}',	
                    'id'        => 'instant_booking_user_waiting_approval_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Instant Booking confirmation notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    Thank you for your booking request on {listing_name} for {dates}. Please wait for confirmation and further instructions.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'instant_booking_user_waiting_approval_email_content',
                    'type'      => 'editor',
                ),  

   /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('New booking request notification to owner ', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_booking_notification_owner'
                ), 
                array(
                    'label'      => __('Enable Booking request notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to owners when new booking was requested', 'listeo_core'),
                    'id'        => 'booking_owner_new_booking_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Booking request notification email subject', 'listeo_core'),
                    'default'      => __('There is a new booking request for {listing_name}', 'listeo_core'),
                 'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details},
                       {dates},{children},{adults},{user_message},{tickets},{service},{details},{client_first_name},{client_last_name},{client_email},{client_phone},{billing_address},{billing_postcode},{billing_city},{billing_country},{price}',
                    'id'        => 'booking_owner_new_booking_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Booking request notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    There's a new booking request on '{listing_name}' for {dates}. Go to your Bookings Dashboard to accept or reject it.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'booking_owner_new_booking_email_content',
                    'type'      => 'editor',
                ),   


                array(
                    'label' =>  '',
                    'description' =>  __('New Instant booking notification to owner ', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_instant_booking_notification_owner'
                ), 
                array(
                    'label'      => __('Enable Instant Booking notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to owners when new instant booking was made', 'listeo_core'),
                    'id'        => 'booking_instant_owner_new_booking_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Instant Booking notification email subject', 'listeo_core'),
                    'default'      => __('There is a new instant booking for {listing_name}', 'listeo_core'),
                   'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details},
                        {payment_url},{expiration},{dates},{children},{adults},{user_message},{tickets},{service},{details},{client_first_name},{client_last_name},{client_email},{client_phone},{billing_address},{billing_postcode},{billing_city},{billing_country},{price}',
                    'id'        => 'booking_instant_owner_new_booking_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Instant Booking notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    There's a new booking  on '{listing_name}' for {dates}.
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'booking_instant_owner_new_booking_email_content',
                    'type'      => 'editor',
                ),   

                 /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Free booking confirmation to user', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_free_booking_notification_user'
                ), 
                array(
                    'label'      => __('Enable Booking confirmation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to users when booking was accepted by owner', 'listeo_core'),
                    'id'        => 'free_booking_confirmation',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Booking request notification email subject', 'listeo_core'),
                    'default'      => __('Your booking request was approved {listing_name}', 'listeo_core'),
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details},
                        {payment_url},{expiration},{dates},{children},{adults},{user_message},{tickets},{service},{details},{client_first_name},{client_last_name},{client_email},{client_phone},{billing_address},{billing_postcode},{billing_city},{billing_country},{price}',
                    'id'        => 'free_booking_confirmation_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Booking request notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    Your booking request on '{listing_name}' for {dates} was approved. See you soon!.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'free_booking_confirmation_email_content',
                    'type'      => 'editor',
                ),     


                 /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Booking approved - payment needed email to user', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_pay_booking_notification_owner'
                ), 
                array(
                    'label'      => __('Enable Booking confirmation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to users when booking was accepted by owner and they need to pay', 'listeo_core'),
                    'id'        => 'pay_booking_confirmation_user',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Booking request notification email subject', 'listeo_core'),
                    'default'      => __('Your booking request was approved {listing_name}, please pay', 'listeo_core'),
                     'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{site_name},{site_url},{dates},{details},{payment_url},{expiration}',
                    'id'        => 'pay_booking_confirmation_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Booking request notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    Your booking request on '{listing_name}' for {dates} was approved. Here's the payment link {payment_url}, the booking will expire after {expiration} if not paid!.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'pay_booking_confirmation_email_content',
                    'type'      => 'editor',
                ),  

                   /*----------------*/
                array(
                    'label' =>  '',
                    'description' =>  __('Booking paid by user email to owner', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_pay_booking_confirmation_owner'
                ), 
                array(
                    'label'      => __('Enable Booking paid confirmation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to owner when booking was paid by use', 'listeo_core'),
                    'id'        => 'paid_booking_confirmation',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Booking paid notification email subject', 'listeo_core'),
                    'default'      => __('Your booking was paid by user - {listing_name}', 'listeo_core'),
                    'id'        => 'paid_booking_confirmation_email_subject',
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details},{payment_url},{expiration}',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Booking paid notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    The booking for '{listing_name}' on {dates} was paid by user.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'paid_booking_confirmation_email_content',
                    'type'      => 'editor',
                ),  

                // booking cancelled
                array(
                    'label' =>  '',
                    'description' =>  __('Booking cancelled notification to user ', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_booking_cancellation_user'
                ), 
                array(
                    'label'      => __('Enable Booking cancellation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to user when booking is cancelled', 'listeo_core'),
                    'id'        => 'booking_user_cancallation_email',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('Booking cancelled notification email subject', 'listeo_core'),
                    'default'      => __('Your booking request for {listing_name} was cancelled', 'listeo_core'),
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{booking_date},{listing_name},{listing_url},{listing_address},{site_name},{site_url},{dates},{details}',
                    'id'        => 'booking_user_cancellation_email_subject',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('Booking cancelled notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    Your booking '{listing_name}' for {dates} was cancelled.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'booking_user_cancellation_email_content',
                    'type'      => 'editor',
                ),   
               
                /*New message in conversation*/
                array(
                    'label' =>  '',
                    'description' =>  __('Email notification about new conversation', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_new_converstation'
                ), 
                array(
                    'label'      => __('Enable new conversation notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to user when there was new conversation started', 'listeo_core'),
                    'id'        => 'new_conversation_notification',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('New conversation notification email subject', 'listeo_core'),
                    'default'      => __('You got new conversation', 'listeo_core'),
                    'id'        => 'new_conversation_notification_email_subject',
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{sender},{conversation_url},{site_name},{site_url}',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('New conversation notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    There's a new conversation waiting for your on {site_name}.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'new_conversation_notification_email_content',
                    'type'      => 'editor',
                ),  

                /*New message in conversation*/
                array(
                    'label' =>  '',
                    'description' =>  __('Email notification about new message', 'listeo_core'),
                    'type' => 'title',
                    'id'   => 'header_new_message'
                ), 
                array(
                    'label'      => __('Enable new message notification email', 'listeo_core'),
                    'description'      => __('Check this checkbox to enable sending emails to user when there was new message send', 'listeo_core'),
                    'id'        => 'new_message_notification',
                    'type'      => 'checkbox',
                ), 
                array(
                    'label'      => __('New message notification email subject', 'listeo_core'),
                    'default'      => __('You got new message', 'listeo_core'),
                    'id'        => 'new_message_notification_email_subject',
                    'description' => '<br>'.__('Available tags are:').'{user_mail},{user_name},{listing_name},{listing_url},{listing_address},{sender},{conversation_url},{site_name},{site_url}',
                    'type'      => 'text',
                ),
                 array(
                    'label'      => __('New message notification email content', 'listeo_core'),
                    'default'      => trim(preg_replace('/\t+/', '', "Hi {user_name},<br>
                    There's a new message waiting for your on {site_name}.<br>
                    <br>
                    Thank you
                    <br>")),
                    'id'        => 'new_message_notification_email_content',
                    'type'      => 'editor',
                ),  

               
              


            ),
        );

        $settings = apply_filters( $this->_token . '_settings_fields', $settings );

        return $settings;
    }

    /**
     * Register plugin settings
     * @return void
     */
    public function register_settings () {
        if ( is_array( $this->settings ) ) {

            // Check posted/selected tab
            $current_section = '';
            if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
                $current_section = $_POST['tab'];
            } else {
                if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
                    $current_section = $_GET['tab'];
                }
            }

            foreach ( $this->settings as $section => $data ) {

                if ( $current_section && $current_section != $section ) continue;

                // Add section to page
                add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->_token . '_settings' );

                foreach ( $data['fields'] as $field ) {

                    // Validation callback for field
                    $validation = '';
                    if ( isset( $field['callback'] ) ) {
                        $validation = $field['callback'];
                    }

                    // Register field
                    $option_name = $this->base . $field['id'];

                    register_setting( $this->_token . '_settings', $option_name, $validation );

                    // Add field to page

                    add_settings_field( $field['id'], $field['label'], array($this, 'display_field'), $this->_token . '_settings', $section, array( 'field' => $field, 'class' => 'listeo_map_settings '.$field['id'],  'prefix' => $this->base ) );
                }

                if ( ! $current_section ) break;
            }
        }
    }

    public function settings_section ( $section ) {
        $html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
        echo $html;
    }

    /**
     * Load settings page content
     * @return void
     */
    public function settings_page () {

        // Build page HTML
        $html = '<div class="wrap" id="' . $this->_token . '_settings">' . "\n";
            $html .= '<h2>' . __( 'Plugin Settings' , 'listeo_core' ) . '</h2>' . "\n";

            $tab = '';
            if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
                $tab .= $_GET['tab'];
            }

            // Show page tabs
            if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

                $html .= '<h2 class="nav-tab-wrapper">' . "\n";

                $c = 0;
                foreach ( $this->settings as $section => $data ) {

                    // Set tab class
                    $class = 'nav-tab';
                    if ( ! isset( $_GET['tab'] ) ) {
                        if ( 0 == $c ) {
                            $class .= ' nav-tab-active';
                        }
                    } else {
                        if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
                            $class .= ' nav-tab-active';
                        }
                    }

                    // Set tab link
                    $tab_link = add_query_arg( array( 'tab' => $section ) );
                    if ( isset( $_GET['settings-updated'] ) ) {
                        $tab_link = remove_query_arg( 'settings-updated', $tab_link );
                    }

                    // Output tab
                    $html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

                    ++$c;
                }

                $html .= '</h2>' . "\n";
            }

            $html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

                // Get settings fields
                ob_start();
                settings_fields( $this->_token . '_settings' );
                do_settings_sections( $this->_token . '_settings' );
                $html .= ob_get_clean();

                $html .= '<p class="submit">' . "\n";
                    $html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
                    $html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'listeo_core' ) ) . '" />' . "\n";
                $html .= '</p>' . "\n";
            $html .= '</form>' . "\n";
        $html .= '</div>' . "\n";

        echo $html;
    }

    /**
     * Generate HTML for displaying fields
     * @param  array   $field Field data
     * @param  boolean $echo  Whether to echo the field HTML or return it
     * @return void
     */
    public function display_field ( $data = array(), $post = false, $echo = true ) {

        // Get field info
        if ( isset( $data['field'] ) ) {
            $field = $data['field'];
        } else {
            $field = $data;
        }

        // Check for prefix on option name
        $option_name = '';
        if ( isset( $data['prefix'] ) ) {
            $option_name = $data['prefix'];
        }

        // Get saved data
        $data = '';
        if ( $post ) {

            // Get saved field data
            $option_name .= $field['id'];
            $option = get_post_meta( $post->ID, $field['id'], true );

            // Get data to display in field
            if ( isset( $option ) ) {
                $data = $option;
            }

        } else {

            // Get saved option
            $option_name .= $field['id'];
            $option = get_option( $option_name );

            // Get data to display in field
            if ( isset( $option ) ) {
                $data = $option;
            }

        }

        // Show default data if no option saved and default is supplied
        if ( $data === false && isset( $field['default'] ) ) {
            $data = $field['default'];
        } elseif ( $data === false ) {
            $data = '';
        }

        $html = '';

        switch( $field['type'] ) {

            case 'text':
            case 'url':
            case 'email':
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" class="regular-text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( (isset($field['placeholder'])) ? $field['placeholder'] : '' ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
            break;

            case 'password':
            case 'number':
            case 'hidden':
                $min = '';
                if ( isset( $field['min'] ) ) {
                    $min = ' min="' . esc_attr( $field['min'] ) . '"';
                }

                $max = '';
                if ( isset( $field['max'] ) ) {
                    $max = ' max="' . esc_attr( $field['max'] ) . '"';
                }
                $html .= '<input step="0.1" id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
            break;

            case 'text_secret':
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
            break;

            case 'textarea':
                $html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '">' . $data . '</textarea><br/>'. "\n";
            break;

            case 'checkbox':
                $checked = '';
                if ( $data && 'on' == $data ) {
                    $checked = 'checked="checked"';
                }
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
            break;

            case 'checkbox_multi':
                foreach ( $field['options'] as $k => $v ) {
                    $checked = false;
                    if ( in_array( $k, (array) $data ) ) {
                        $checked = true;
                    }
                    $html .= '<p><label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label></p> ';
                }
            break;

            case 'radio':
                foreach ( $field['options'] as $k => $v ) {
                    $checked = false;
                    if ( $k == $data ) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label><br> ';
                }
            break;

            case 'select':
                $html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
                foreach ( $field['options'] as $k => $v ) {
                    $selected = false;
                    if ( $k == $data ) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
            break;

            case 'select_multi':
                $html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
                foreach ( $field['options'] as $k => $v ) {
                    $selected = false;
                    if ( in_array( $k, (array) $data ) ) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
            break;

            case 'image':
                $image_thumb = '';
                if ( $data ) {
                    $image_thumb = wp_get_attachment_thumb_url( $data );
                }
                $html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
                $html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'listeo_core' ) . '" data-uploader_button_text="' . __( 'Use image' , 'listeo_core' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'listeo_core' ) . '" />' . "\n";
                $html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'listeo_core' ) . '" />' . "\n";
                $html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
            break;

            case 'color':
                ?><div class="color-picker" style="position:relative;">
                    <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
                    <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
                </div>
                <?php
            break;
            
            case 'editor':
                wp_editor($data, $option_name, array(
                    'textarea_name' => $option_name,
                    'editor_height' => 150
                ) );
            break;

        }

        switch( $field['type'] ) {

            case 'checkbox_multi':
            case 'radio':
            case 'select_multi':
                $html .= '<br/><span class="description">' . $field['description'] . '</span>';
            break;
            case 'title':
                $html .= '<br/><h3 class="description '.$field['id'].' ">' . $field['description'] . '</h3>';
            break;

            default:
                if ( ! $post ) {
                    $html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
                }
                if(isset($field['description']) && !empty($field['description'] )) {
                    $html .= '<span class="description">' . $field['description'] . '</span>' . "\n";    
                }
                

                if ( ! $post ) {
                    $html .= '</label>' . "\n";
                }
            break;
        }

        if ( ! $echo ) {
            return $html;
        }

        echo $html;

    }

    /**
     * Validate form field
     * @param  string $data Submitted value
     * @param  string $type Type of field to validate
     * @return string       Validated value
     */
    public function validate_field ( $data = '', $type = 'text' ) {

        switch( $type ) {
            case 'text': $data = esc_attr( $data ); break;
            case 'url': $data = esc_url( $data ); break;
            case 'email': $data = is_email( $data ); break;
        }

        return $data;
    }

    /**
     * Add meta box to the dashboard
     * @param string $id            Unique ID for metabox
     * @param string $title         Display title of metabox
     * @param array  $post_types    Post types to which this metabox applies
     * @param string $context       Context in which to display this metabox ('advanced' or 'side')
     * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
     * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
     * @return void
     */
    public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

        // Get post type(s)
        if ( ! is_array( $post_types ) ) {
            $post_types = array( $post_types );
        }

        // Generate each metabox
        foreach ( $post_types as $post_type ) {
            add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
        }
    }

    /**
     * Display metabox content
     * @param  object $post Post object
     * @param  array  $args Arguments unique to this metabox
     * @return void
     */
    public function meta_box_content ( $post, $args ) {

        $fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

        if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

        echo '<div class="custom-field-panel">' . "\n";

        foreach ( $fields as $field ) {

            if ( ! isset( $field['metabox'] ) ) continue;

            if ( ! is_array( $field['metabox'] ) ) {
                $field['metabox'] = array( $field['metabox'] );
            }

            if ( in_array( $args['id'], $field['metabox'] ) ) {
                $this->display_meta_box_field( $field, $post );
            }

        }

        echo '</div>' . "\n";

    }

    /**
     * Dispay field in metabox
     * @param  array  $field Field data
     * @param  object $post  Post object
     * @return void
     */
    public function display_meta_box_field ( $field = array(), $post ) {

        if ( ! is_array( $field ) || 0 == count( $field ) ) return;

        $field = '<p class="form-field"><label for="' . $field['id'] . '">' . $field['label'] . '</label>' . $this->display_field( $field, $post, false ) . '</p>' . "\n";

        echo $field;
    }

    /**
     * Save metabox fields
     * @param  integer $post_id Post ID
     * @return void
     */
    public function save_meta_boxes ( $post_id = 0 ) {

        if ( ! $post_id ) return;

        $post_type = get_post_type( $post_id );

        $fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

        if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

        foreach ( $fields as $field ) {
            if ( isset( $_REQUEST[ $field['id'] ] ) ) {
                update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
            } else {
                update_post_meta( $post_id, $field['id'], '' );
            }
        }
    }

    /**
     * Main WordPress_Plugin_Template_Settings Instance
     *
     * Ensures only one instance of WordPress_Plugin_Template_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main WordPress_Plugin_Template_Settings instance
     */
    public static function instance ( $parent ) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $parent );
        }
        return self::$_instance;
    } // End instance()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone () {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
    } // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup () {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
    } // End __wakeup()

}

$settings = new Listeo_Core_Admin( __FILE__ );