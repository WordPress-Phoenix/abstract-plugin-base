<?php
// TODO: move to packagist and build in composer
if ( ! class_exists( 'FanSided_Powertools' ) ) {
	/**
	 * Class Plugin_Base
	 */
	abstract class Plugin_Base {
		
		public $debug;
		public $installed_dir;
		public $installed_url;
		public $admin;
		public $modules;
		public $network;
		public $current_blog_globals;
		public $detect;
		public $autoload_dir = '/includes/';
		protected $current_file = __FILE__;
		
		/**
		 * Construct the plugin object
		 *
		 */
		public function __construct() {
			
			// hook can be used by mu plugins to modify plugin behavior after plugin is setup
			do_action( get_called_class() . '_preface', $this );
			
			// configure and setup the plugin class variables
			$this->configure_defaults();
			
			// define globals used by the plugin including bloginfo
			$this->defines_and_globals();
			
			// Register autoloading to include any files in the $autoload_dir
			spl_autoload_register( array( $this, 'autoload' ) );
			
			// Enable any composer libraries if they exist
			if ( file_exists( $this->installed_dir . '/vendor/autoload.php' ) ) {
				include_once $this->installed_dir . '/vendor/autoload.php';
			}
			
			// Onload to do things during plugin construction
			
			// initialize
			add_action( 'init', array( $this, 'init' ) );
			
			// init for use with logged in users, see this::authenticated_init for more details
			add_action( 'init', array( $this, 'authenticated_init' ) );
			
			// hook can be used by mu plugins to modify plugin behavior after plugin is setup
			do_action( get_called_class() . '_setup', $this );
			
		} // END public function __construct
		
		/**
		 * Initialize the plugin - for public (front end)
		 *
		 * @since   0.1
		 * @return  void
		 */
		abstract public function onload();
		
		/**
		 * Initialize the plugin - for public (front end)
		 *
		 * @since   0.1
		 * @return  void
		 */
		abstract public function init();
		
		/**
		 * Initialize the plugin - for admin (back end)
		 * You would expected this to be handled on action admin_init, but it does not properly handle
		 * the use case for all logged in user actions. Always keep is_user_logged_in() wrapper within
		 * this function for proper usage.
		 *
		 * @since   0.1
		 * @return  void
		 */
		abstract public function authenticated_init();
		
		/**
		 * Activated the plugin actions
		 *
		 * @return  void
		 */
		public static function activate(){}
		
		/**
		 * Deactivated the plugin actions
		 *
		 * @return  void
		 */
		public static function deactivate(){}
		
		abstract protected function defines_and_globals();
		
		protected function configure_defaults() {
			// Setup plugins global params
			$this->modules        = new stdClass();
			$this->modules->count = 0;
			$this->installed_dir  = dirname( $this->current_file );
			$this->installed_url  = plugins_url( '/', $this->current_file );
		}
		
		/**
		 * Take a class name and turn it into a file name.
		 *
		 * @param  string $class
		 *
		 * @return string
		 */
		private function get_file_name_from_class( $class ) {
			
			return 'class-' . str_replace( '_', '-', $class ) . '.php';
		}
		
		/**
		 * Include a class file.
		 *
		 * @param  string $path
		 *
		 * @return bool successful or not
		 */
		private function load_file( $path ) {
			if ( $path && is_readable( $path ) ) {
				include_once( $path );
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * Auto-load classes on demand to reduce memory consumption.
		 *
		 * @param string $class
		 *
		 * @extra Special thanks to @mlteal for introducing concept for inception
		 */
		public function autoload( $class ) {
			$class = strtolower( $class );
			$file  = $this->get_file_name_from_class( $class );
			$this->load_file( $this->installed_dir . $this->autoload_dir . $file );
		}
		
		public static function run() {
			/**
			 * Build and initialize the plugin
			 */
			// Installation and un-installation hooks
			register_activation_hook( __FILE__, array( get_called_class(), 'activate' ) );
			register_deactivation_hook( __FILE__, array( get_called_class(), 'deactivate' ) );
			self::set();
		}
		
		/**
		 * Used to setup the instance of the class and place in wp_plugins collection
		 *
		 * @param $instance
		 */
		private static function set( $instance = false ) {
			// instantiate the plugin class, which should never be instantiated more then once
			global $wp_plugins;
			if ( ! isset( $wp_plugins ) ) {
				$wp_plugins = new stdClass();
			}
			$plugin_name = strtolower( get_called_class() );
			if ( empty( $instance ) || ! is_a( $instance, get_called_class() ) ) {
				$wp_plugins->$plugin_name = new FanSided_Powertools();
			} else {
				$wp_plugins->$plugin_name = $instance;
			}
		}
		
		/**
		 * @return bool / $instance
		 */
		public function get() {
			global $wp_plugins;
			$plugin_name = strtolower( get_called_class() );
			if ( isset( $wp_plugins ) && isset( $wp_plugins->$plugin_name ) ) {
				return $wp_plugins->$plugin_name;
			} else {
				return false;
			}
		}
		
	} // END class
} // END if(!class_exists())
