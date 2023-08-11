<?php
!defined( 'ABSPATH' ) && exit; // Exit if accessed directly

/**
 * Class YITH_POS_Integrations
 *
 * handle plugin integrations
 *
 * @author Leanza Francesco <leanzafrancesco@gmail.com>
 * @since   1.0.6
 */
class YITH_POS_Integrations {

    /** @var YITH_POS_Integrations */
    private static $_instance;

    protected $_plugins = array();

    /**
     * Singleton implementation
     *
     * @return YITH_POS_Integrations
     */
    public static function get_instance() {
        return !is_null( self::$_instance ) ? self::$_instance : self::$_instance = new self();
    }

    /**
     * YITH_POS_Integrations constructor.
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_plugins' ), 15 );
    }

    /**
     * Load plugins
     */
    public function load_plugins() {
        $this->_plugins = require_once( 'plugins-list.php' );
        $this->_load();
    }

    /**
     * Load Integration classes
     */
    private function _load() {
        require_once( YITH_POS_INCLUDES_PATH . '/integrations/abstract.yith-pos-integration.php' );

        foreach ( $this->_plugins as $slug => $plugin_info ) {
            $filename  = YITH_POS_INCLUDES_PATH . '/integrations/class.yith-pos-' . $slug . '-integration.php';
            $classname = $this->get_class_name_from_slug( $slug );

            $var = str_replace( '-', '_', $slug );
            if ( file_exists( $filename ) && !class_exists( $classname ) ) {
                require_once( $filename );
            }

            if ( method_exists( $classname, 'get_instance' ) ) {
                $has_plugin         = $this->has_plugin( $slug );

                $this->$var = $classname::get_instance( $has_plugin );
            }
        }
    }

    /**
     * get the class name from slug
     *
     * @param $slug
     *
     * @return string
     */
    public function get_class_name_from_slug( $slug ) {
        $class_slug = str_replace( '-', ' ', $slug );
        $class_slug = ucwords( $class_slug );
        $class_slug = str_replace( ' ', '_', $class_slug );

        return 'YITH_POS_' . $class_slug . '_Integration';
    }

    /**
     * Check if user has a plugin
     *
     * @param string $slug
     *
     * @return bool
     */
    public function has_plugin( $slug ) {
        if ( !empty( $this->_plugins[ $slug ] ) ) {
            $plugin = $this->_plugins[ $slug ];

            if ( isset( $plugin[ 'premium' ] ) && defined( $plugin[ 'premium' ] ) && constant( $plugin[ 'premium' ] ) ) {
                if ( !isset ( $plugin[ 'version' ] ) || !isset( $plugin[ 'min_version' ] ) )
                    return true;

                $compare = isset( $plugin[ 'compare' ] ) ? $plugin[ 'compare' ] : '>=';

                if ( defined( $plugin[ 'version' ] ) && constant( $plugin[ 'version' ] ) &&
                     version_compare( constant( $plugin[ 'version' ] ), $plugin[ 'min_version' ], $compare )
                ) {
                    return true;
                }

            }
        }

        return false;
    }
}