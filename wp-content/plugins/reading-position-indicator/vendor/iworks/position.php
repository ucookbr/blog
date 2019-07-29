<?php

class iworks_position
{
    private $base;
    private $capability;
    private $options;
    private $root;
    private $version = '1.0.3';
    private $min = '.min';
    private $check = false;
    private $data = null;

    public function __construct() {
        /**
         * static settings
         */
        $this->base = dirname( dirname( __FILE__ ) );
        $this->root = plugins_url( '', (dirname( dirname( __FILE__ ) )) );
        $this->capability = apply_filters( 'iworks_reading_position_indicator_capability', 'manage_options' );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $this->min = '';
        }
        /**
         * options
         */
        $this->options = get_iworks_reading_position_indicator_options();

        /**
         * generate
         */
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'wp_head', array( $this, 'wp_head' ) );
        add_filter( 'the_content', array( $this, 'the_content' ) );
        add_action( 'iworks_rate_css', array( $this, 'iworks_rate_css' ) );
        add_action( 'wp_head', array( $this, 'set_check_value' ), 0 );
    }

    public function wp_head() {
        if ( ! $this->check ) {
            return;
        }
        $data = $this->get_data();
        if ( ! isset( $data['style'] ) ) {
            return;
        }
        $color1 = $data['color1'];
        if ( isset( $data['color1_opacity'] ) && 100 != $data['color1_opacity'] ) {
            $color1 = $this->options->hex2rgb( $color1 );
            $color1[] = $data['color1_opacity'] / 100;
            $color1 = sprintf( 'rgba(%s)', implode( ',', $color1 ) );
        }
        $color2 = $data['color2'];
        if ( isset( $data['color2_opacity'] ) && 100 != $data['color2_opacity'] ) {
            $color2 = $this->options->hex2rgb( $color2 );
            $color2[] = $data['color2_opacity'] / 100;
            $color2 = sprintf( 'rgba(%s)', implode( ',', $color2 ) );
        }
        $background = $data['background'];
        if ( isset( $data['background_opacity'] ) && 100 != $data['background_opacity']  ) {
            $background = $this->options->hex2rgb( $background );
            $background[] = $data['background_opacity'] / 100;
            $background = sprintf( 'rgba(%s)', implode( ',', $background ) );
        }
        echo '<style type="text/css" media="handheld, projection, screen">';
        if ( isset( $data['radius'] ) && 0 < $data['radius'] ) {
            $style = sprintf( 'border-radius: %dpx;', $data['radius'] );
            printf( '#reading-position-indicator::-moz-progress-bar { %s }', $style );
            printf( '#reading-position-indicator::-webkit-progress-value { %s }', $style );
            printf( '#reading-position-indicator[role] { %s }', $style );
        }
        echo 'body #reading-position-indicator,';
        echo 'body.admin-bar #reading-position-indicator {';
        /**
         * position
         */
        if ( isset( $data['position'] ) ) {
            switch( $data['position'] ) {
                case 'bottom':
                    echo 'bottom: 0;';
                    echo 'top: inherit;';
                    break;
            }
        }
/**
 * height
 */
        $height = 10;
        if ( isset( $data['height'] ) ) {
            $height = $data['height'];
        }
        printf( 'height: %spx;', $height );
        printf( 'background: %s;', $background );
        echo '}';

?>
#reading-position-indicator::-webkit-progress-bar{background-color: <?php echo $background; ?>}
<?php
        switch ( $data['style'] ) {
        case 'solid':
            if ( isset( $data['color1'] ) ) {
?>
#reading-position-indicator {
    color: <?php echo $color1; ?>;
    background: <?php echo $background; ?>;
}
#reading-position-indicator::-webkit-progress-value {
    background-color: <?php echo $color1; ?>;
}
#reading-position-indicator::-moz-progress-bar {
    background-color: <?php echo $color1; ?>;
}
#reading-position-indicator::[aria-valuenow]:before {
    background-color: <?php echo $color1; ?>;
}
.progress-bar  {
    background-color: <?php echo $color1; ?>; ;
}
<?php
            }
            break;
        case 'indeter':
?>
#reading-position-indicator[value]::-webkit-progress-value {
        background-image:
        -webkit-linear-gradient(-45deg, transparent 33%, rgba(0, 0, 0, .1) 33%, rgba(0,0, 0, .1) 66%, transparent 66%),
        -webkit-linear-gradient(top, rgba(255, 255, 255, .25), rgba(0, 0, 0, .25)),
        -webkit-linear-gradient(right, <?php echo $color1; ?>, <?php echo $color2; ?>);
background-size: <?php echo $height * 2; ?>px <?php echo $height; ?>px, 100% 100%, 100% 100%;
    }
<?php

        case 'transparent':
        case 'gradient':
            if ( 'transparent' == $data['style'] ) {
                $color2 = 'transparent';
            }
?>
#reading-position-indicator::-webkit-progress-value {
    background: linear-gradient(to right, <?php echo $color2; ?>, <?php echo $color1; ?>);
}
#reading-position-indicator::-moz-progress-bar {
    background: linear-gradient(to right, <?php echo $color2; ?>, <?php echo $color1; ?>);
}
#reading-position-indicator[role][aria-valuenow] {
    background: linear-gradient(to right, <?php echo $color2; ?>, <?php echo $color1; ?>) !important;
}
<?php
/*
    background: -webkit-linear-gradient(left, <?php echo $color2; ?>, <?php echo $color1; ?>); /* For Safari 5.1 to 6.0 * 
    background: -o-linear-gradient(right, <?php echo $color2; ?>, <?php echo $color1; ?>); /* For Opera 11.1 to 12.0 * 
    background: -moz-linear-gradient(right, <?php echo $color2; ?>, <?php echo $color1; ?>); /* For Firefox 3.6 to 15 * 
 */
            break;
        }
?>
</style>
<?php
    }

    public function admin_init() {
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
        /**
         * options
         */
        $this->options->options_init();
    }

    public function wp_enqueue_scripts() {
        if ( ! $this->check ) {
            return;
        }

        $file = sprintf( '/assets/styles/%s%s.css', __CLASS__, $this->min );
        wp_register_style(
            __CLASS__,
            plugins_url( $file, $this->base ),
            array(),
            $this->get_version(),
            'handheld, projection, screen'
        );
        wp_enqueue_style( __CLASS__ );

        $file = sprintf( '/assets/scripts/%s%s.js', __CLASS__, $this->min );
        wp_register_script(
            __CLASS__,
            plugins_url( $file, $this->base ),
            array( 'jquery' ),
            $this->version,
            true
        );
        wp_localize_script( __CLASS__, __CLASS__, $this->options->get_all_options() );
        wp_enqueue_script( __CLASS__ );
    }

    private function get_version( $file = null ) {
        if ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE ) {
            if ( null != $file ) {
                $file = dirname( $this->base ) . $file;
                if ( is_file( $file ) ) {
                    return md5_file( $file );
                }
            }
            return rand( 0, 99999 );
        }
        return $this->version;
    }

    public function plugin_row_meta( $links, $file ) {
        if ( ! preg_match( '/reading-position-indicator.php$/', $file ) ) {
            return $links;
        }
        if ( ! is_multisite() && current_user_can( $this->capability ) ) {
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                add_query_arg( 'page', 'irpi_index', admin_url( 'themes.php' ) ),
                __( 'Settings' )
            );
        }
        $links[] = sprintf(
            '<a href="http://iworks.pl/donate/reading-position-indicator.php">%s</a>',
            __( 'Donate' )
        );
        return $links;
    }

    /**
     * Add marker to content.
     *
     * @since 1.0.2
     */
    public function the_content( $content ) {
        if ( $this->check ) {
            $content .= '<div class="reading-position-indicator-end"></div>';
        }
        return $content;
    }

    /**
     * Change image for rate message.
     *
     * @since 1.0.2
     */
    public function iworks_rate_css() {
        $logo = plugin_dir_url( dirname( dirname( __FILE__ ) ) ).'assets/images/icon.svg';
        echo '<style type="text/css">';
        printf( '.iworks-notice-reading-position-indicator .iworks-notice-logo{background-image:url(%s);}', esc_url( $logo ) );
        echo '</style>';
    }

    /**
     * Set check value.
     *
     * @since 1.0.2
     */
    public function set_check_value() {
        if ( !is_singular() ) {
            return;
        }
        $data = $this->get_data();
        if ( ! isset( $data['post_type'] ) ) {
            return;
        }
        $post_type = get_post_type();
        $this->check = in_array( $post_type, $data['post_type'] );
    }

    /**
     * Get data from DB.
     *
     * @since 1.0.2
     */
    private function get_data() {
        if ( null === $this->data ) {
            $this->data = $this->options->get_all_options();
        }
        return $this->data;
    }
}
