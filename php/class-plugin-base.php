<?php
/**
 * Class Plugin_Base
 *
 * @package SharethisReactionButtons
 */

namespace SharethisReactionButtons;

/**
 * Class Plugin_Base
 *
 * @package SharethisReactionButtons
 */
abstract class Plugin_Base {

	/**
	 * Plugin config.
	 *
	 * @var array
	 */
	public $config = array();

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	public $dir_path;

	/**
	 * Plugin directory URL.
	 *
	 * @var string
	 */
	public $dir_url;

	/**
	 * Directory in plugin containing autoloaded classes.
	 *
	 * @var string
	 */
	protected $autoload_class_dir = 'php';

	/**
	 * Autoload matches cache.
	 *
	 * @var array
	 */
	protected $autoload_matches_cache = array();

	/**
	 * Required instead of a static variable inside the add_doc_hooks method
	 * for the sake of unit testing.
	 *
	 * @var array
	 */
	protected $called_doc_hooks = array();

	/**
	 * Plugin_Base constructor.
	 */
	public function __construct() {
		$location       = $this->locate_plugin();
		$this->slug     = $location['dir_basename'];
		$this->dir_path = $location['dir_path'];
		$this->dir_url  = $location['dir_url'];

		spl_autoload_register( array( $this, 'autoload' ) );
		$this->add_doc_hooks();
	}

	/**
	 * Plugin_Base destructor.
	 */
	public function __destruct() {
		$this->remove_doc_hooks();
	}

	/**
	 * Get reflection object for this class.
	 *
	 * @return \ReflectionObject
	 */
	public function get_object_reflection() {
		static $reflection;

		if ( empty( $reflection ) ) {
			$reflection = new \ReflectionObject( $this );
		}

		return $reflection;
	}

	/**
	 * Autoload for classes that are in the same namespace as $this.
	 *
	 * @param string $class Class name.
	 * @return void
	 */
	public function autoload( $class ) {
		if ( ! isset( $this->autoload_matches_cache[ $class ] ) ) {
			if ( ! preg_match( '/^(?P<namespace>.+)\\\\(?P<class>[^\\\\]+)$/', $class, $matches ) ) {
				$matches = false;
			}

			$this->autoload_matches_cache[ $class ] = $matches;
		} else {
			$matches = $this->autoload_matches_cache[ $class ];
		}

		if ( empty( $matches ) ) {
			return;
		}

		if ( $this->get_object_reflection()->getNamespaceName() !== $matches['namespace'] ) {
			return;
		}

		$class_name = $matches['class'];
		$class_path = \trailingslashit( $this->dir_path );

		if ( $this->autoload_class_dir ) {
			$class_path .= \trailingslashit( $this->autoload_class_dir );
		}

		$class_path .= sprintf( 'class-%s.php', strtolower( str_replace( '_', '-', $class_name ) ) );

		if ( is_readable( $class_path ) ) {
			require_once $class_path;
		}
	}

	/**
	 * Version of plugin_dir_url() which works for plugins installed in the plugins directory,
	 * and for plugins bundled with themes.
	 *
	 * @return array
	 */
	public function locate_plugin() {
		$base_file    = str_replace( 'php', '', __FILE__ );
		$dir_url      = trailingslashit( plugins_url( '', $base_file ) );
		$dir_path     = substr( str_replace( 'class-plugin-base', '', $base_file ), 0, - 2 );
		$dir_basename = basename( $dir_path );

		return compact( 'dir_url', 'dir_path', 'dir_basename' );
	}

	/**
	 * Hooks a function on to a specific filter.
	 *
	 * @param string $name     The hook name.
	 * @param array  $callback The class object and method.
	 * @param array  $args     An array with priority and arg_count.
	 *
	 * @return mixed
	 */
	public function add_filter( $name, $callback, $args = array() ) {
		// Merge defaults.
		$args = array_merge(
			array(
				'priority'  => 10,
				'arg_count' => PHP_INT_MAX,
			),
			$args
		);

		return $this->add_hook( 'filter', $name, $callback, $args );
	}

	/**
	 * Hooks a function on to a specific action.
	 *
	 * @param string $name     The hook name.
	 * @param array  $callback The class object and method.
	 * @param array  $args     An array with priority and arg_count.
	 *
	 * @return mixed
	 */
	public function add_action( $name, $callback, $args = array() ) {
		// Merge defaults.
		$args = array_merge(
			array(
				'priority'  => 12,
				'arg_count' => PHP_INT_MAX,
			),
			$args
		);

		return $this->add_hook( 'action', $name, $callback, $args );
	}

	/**
	 * Hooks a function on to a specific shortcode.
	 *
	 * @param string $name     The shortcode name.
	 * @param array  $callback The class object and method.
	 *
	 * @return mixed
	 */
	public function add_shortcode( $name, $callback ) {
		return $this->add_hook( 'shortcode', $name, $callback );
	}

	/**
	 * Hooks a function on to a specific action/filter.
	 *
	 * @param string $type     The hook type. Options are action/filter.
	 * @param string $name     The hook name.
	 * @param array  $callback The class object and method.
	 * @param array  $args     An array with priority and arg_count.
	 *
	 * @return mixed
	 */
	protected function add_hook( $type, $name, $callback, $args = array() ) {
		$priority  = isset( $args['priority'] ) ? $args['priority'] : 10;
		$arg_count = isset( $args['arg_count'] ) ? $args['arg_count'] : PHP_INT_MAX;
		$fn        = sprintf( '\add_%s', $type );
		$retval    = \call_user_func( $fn, $name, $callback, $priority, $arg_count );

		return $retval;
	}

	/**
	 * Add actions/filters/shortcodes from the methods of a class based on DocBlocks.
	 *
	 * @param object $object The class object.
	 */
	public function add_doc_hooks( $object = null ) {
		if ( is_null( $object ) ) {
			$object = $this;
		}
		$class_name = get_class( $object );
		if ( isset( $this->called_doc_hooks[ $class_name ] ) ) {
			$notice = sprintf( 'The add_doc_hooks method was already called on %s. Note that the Plugin_Base constructor automatically calls this method.', $class_name );
			// @codingStandardsIgnoreStart
			trigger_error( esc_html( $notice ), \E_USER_NOTICE );
			// @codingStandardsIgnoreEnd
			return;
		}
		$this->called_doc_hooks[ $class_name ] = true;
		$reflector                             = new \ReflectionObject( $object );
		foreach ( $reflector->getMethods() as $method ) {
			$doc       = $method->getDocComment();
			$arg_count = $method->getNumberOfParameters();
			if ( preg_match_all( '#\* @(?P<type>filter|action|shortcode)\s+(?P<name>[a-z0-9\-\._]+)(?:,\s+(?P<priority>\d+))?#', $doc, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$type     = $match['type'];
					$name     = $match['name'];
					$priority = empty( $match['priority'] ) ? 10 : intval( $match['priority'] );
					$callback = array( $object, $method->getName() );
					call_user_func( array( $this, "add_{$type}" ), $name, $callback, compact( 'priority', 'arg_count' ) );
				}
			}
		}
	}

	/**
	 * Removes the added DocBlock hooks.
	 *
	 * @param object $object The class object.
	 */
	public function remove_doc_hooks( $object = null ) {
		if ( is_null( $object ) ) {
			$object = $this;
		}
		$class_name = get_class( $object );
		$reflector  = new \ReflectionObject( $object );
		foreach ( $reflector->getMethods() as $method ) {
			$doc = $method->getDocComment();
			if ( preg_match_all( '#\* @(?P<type>filter|action|shortcode)\s+(?P<name>[a-z0-9\-\._]+)(?:,\s+(?P<priority>\d+))?#', $doc, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$type     = $match['type'];
					$name     = $match['name'];
					$priority = empty( $match['priority'] ) ? 10 : intval( $match['priority'] );
					$callback = array( $object, $method->getName() );
					call_user_func( "remove_{$type}", $name, $callback, $priority );
				}
			}
		}
		unset( $this->called_doc_hooks[ $class_name ] );
	}
}
