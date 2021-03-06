<?php

namespace WPStaging\Backend\Pro\Upgrade;

use WPStaging\Core\WPStaging;
use WPStaging\Backend\Optimizer\Optimizer;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\Utils\Helper;
use WPStaging\Core\Utils\IISWebConfig;
use WPStaging\Core\Utils\Htaccess;

/**
 * Upgrade Class
 * This must be loaded on every page init to ensure all settings are 
 * adjusted correctly and to run any upgrade process if necessary.
 */
// No Direct Access
if( !defined( "WPINC" ) ) {
    die;
}

class Upgrade {

    /**
     * Previous Version number
     * @var string 
     */
    private $previousVersion;

    /**
     * Clone data
     * @var obj 
     */
    private $clones;

    /**
     * Global settings
     * @var type obj
     */
    private $settings;

    /**
     * Logger
     * @var obj 
     */
    private $logger;

    /**
     * db object
     * @var obj 
     */
    private $db;

    public function __construct() {

        // Previous version
        $this->previousVersion = preg_replace( '/[^0-9.].*/', '', get_option( 'wpstgpro_version' ) );

        // Options earlier than version 2.0.0
        $this->clones = get_option( "wpstg_existing_clones", [] );

        $this->settings = ( object ) get_option( "wpstg_settings", [] );

        $this->db = WPStaging::getInstance()->get( "wpdb" );

        // Logger
        $this->logger = new Logger;
    }

    public function doUpgrade() {
        $this->upgrade2_0_3();
        $this->upgrade2_0_4();
        $this->upgrade2_1_7();
        $this->upgrade2_3_6();
        $this->upgrade2_6_5();
        $this->upgrade2_8_6();
        $this->setVersion();
    }

    /**
     * Fix array keys of staging sites
     */
    private function upgrade2_8_6() {
        // Previous version lower than 2.8.6
        if( version_compare( $this->previousVersion, '2.8.6', '<' ) ) {

            // Current options
            $sites = get_option( "wpstg_existing_clones_beta", [] );
            
            $new = [];

            // Fix keys. Replace white spaces with dash character
            foreach ( $sites as $oldKey => $site ) {
                $key                = preg_replace( "#\W+#", '-', strtolower( $oldKey ) );
                $new[$key] = $sites[$oldKey];
            }
            if (update_option("wpstg_existing_clones_beta", $new)){
                return true;
            } else {
                return false;
            }
        }
    }

    private function upgrade2_6_5() {
        // Previous version lower than 2.6.5
        if( version_compare( $this->previousVersion, '2.6.5', '<' ) ) {
            // Add htaccess to wp staging uploads folder
            $htaccess = new Htaccess();
            $htaccess->create( trailingslashit( \WPStaging\Core\WPStaging::getContentDir() ) . '.htaccess' );
            $htaccess->create( trailingslashit( \WPStaging\Core\WPStaging::getContentDir() ) . 'logs/.htaccess' );

            // Add litespeed htaccess to wp root folder
            if( extension_loaded( 'litespeed' ) ) {
                $htaccess->createLitespeed( ABSPATH . '.htaccess' );
            }

            // create web.config file for IIS in wp staging uploads folder
            $webconfig = new IISWebConfig();
            $webconfig->create( trailingslashit( \WPStaging\Core\WPStaging::getContentDir() ) . 'web.config' );
            $webconfig->create( trailingslashit( \WPStaging\Core\WPStaging::getContentDir() ) . 'logs/web.config' );
        }
    }

    /**
     * Upgrade method 2.0.3
     */
    public function upgrade2_0_3() {
        // Previous version lower than 2.0.2 or new install
        if( $this->previousVersion === false || version_compare( $this->previousVersion, '2.0.2', '<' ) ) {
            $this->upgradeOptions();
            $this->upgradeClonesBeta();
            $this->upgradeNotices();
        }
    }

    /**
     * Upgrade method 2.0.4
     */
    public function upgrade2_0_4() {
        if( $this->previousVersion === false || version_compare( $this->previousVersion, '2.0.5', '<' ) ) {

            // Register cron job.
            $cron = new \WPStaging\Core\Cron\Cron;
            $cron->scheduleEvent();

            // Install Optimizer 
            $optimizer = new Optimizer();
            $optimizer->installOptimizer();
        }
    }

    /**
     * Upgrade method 2.1.7
     * Sanitize the clone key value.
     */
    private function upgrade2_1_7() {
        if( $this->previousVersion === false || version_compare( $this->previousVersion, '2.1.7', '<' ) ) {
            $sites = get_option( "wpstg_existing_clones_beta", [] );
            foreach ( $sites as $key => $value ) {
                unset( $sites[$key] );
                $sites[preg_replace( "#\W+#", '-', strtolower( $key ) )] = $value;
            }
            update_option( 'wpstg_existing_clones_beta', $sites );
        }
    }

    /**
     * Upgrade method 2.3.6
     */
    private function upgrade2_3_6() {
        // Previous version lower than 2.3.6
        if( version_compare( $this->previousVersion, '2.3.6', '<' ) ) {
            $this->upgradeElements();
        }
    }

    /**
     * Add missing elements
     */
    private function upgradeElements() {
        // Current options
        $sites = get_option( "wpstg_existing_clones_beta", [] );

        if( $sites === false || count( $sites ) === 0 ) {
            return;
        }

        // Check if key prefix is missing and add it
        foreach ( $sites as $key => $value ) {
            if( empty( $sites[$key]['directoryName'] ) ) {
                continue;
            }
            !empty( $sites[$key]['prefix'] ) ?
                            $sites[$key]['prefix'] = $value['prefix'] :
                            $sites[$key]['prefix'] = $this->getStagingPrefix( $sites[$key]['directoryName'] );
        }

        if( !empty( $sites ) ) {
            update_option( 'wpstg_existing_clones_beta', $sites );
        }
    }

    /**
     * Check and return prefix of the staging site
     * @param string $directory
     * @return string
     */
    private function getStagingPrefix( $directory ) {
        // Try to get staging prefix from wp-config.php of staging site
        $path    = ABSPATH . $directory . "/wp-config.php";
        if( ($content = @file_get_contents( $path )) === false ) {
            $prefix = "";
        } else {
            // Get prefix from wp-config.php
            preg_match( "/table_prefix\s*=\s*'(\w*)';/", $content, $matches );

            $prefix = "";
            if( !empty( $matches[1] ) ) {
                $prefix = $matches[1];
            }
        }
        // return result: Check if staging prefix is the same as the live prefix
        if( $this->db->prefix !== $prefix ) {
            return $prefix;
        }

        return "";
    }

    /**
     * Upgrade routine for new install
     */
    private function upgradeOptions() {
        // Write some default vars
        add_option( 'wpstg_installDate', date( 'Y-m-d h:i:s' ) );
        $this->settings->optimizer = 1;
        update_option( 'wpstg_settings', $this->settings );
    }

    /**
     * Write new version number into db
     * return bool
     */
    private function setVersion() {
        // Check if version number in DB is lower than version number in current plugin
        if( version_compare( $this->previousVersion, WPStaging::getVersion(), '<' ) ) {
            // Update Version number
            update_option( 'wpstgpro_version', preg_replace( '/[^0-9.].*/', '', WPStaging::getVersion() ) );
            // Update "upgraded from" version number
            update_option( 'wpstgpro_version_upgraded_from', preg_replace( '/[^0-9.].*/', '', $this->previousVersion ) );

            return true;
        }
        return false;
    }

    /**
     * Create a new db option for beta version 2.0.2
     * @return bool
     */
    private function upgradeClonesBeta() {

        if( empty( $this->clones ) || count( $this->clones ) === 0 ) {
            return false;
        }

        $new = [];
        foreach ( $this->clones as $key => &$value ) {

            // Skip the rest of the loop if data is already compatible to wpstg 2.0.2
            if( isset( $value['directoryName'] ) || !empty( $value['directoryName'] ) ) {
                continue;
            }

            $new[$value]['directoryName'] = $value;
            $new[$value]['path']          = get_home_path() . $value;
            $helper                       = new Helper();
            $new[$value]['url']           = $helper->getHomeUrl() . "/" . $value;
            $new[$value]['number']        = $key + 1;
            $new[$value]['version']       = $this->previousVersion;
        }
        unset( $value );

        if( empty( $new ) || update_option( 'wpstg_existing_clones_beta', $new ) === false ) {
            $this->logger->log( 'Failed to upgrade clone data from ' . $this->previousVersion . ' to ' . WPStaging::getVersion() );
        }
    }

    /**
     * Upgrade Notices db options from wpstg 1.3 -> 2.0.1
     * Fix some logical db options
     */
    private function upgradeNotices() {
        $poll   = get_option( "wpstg_start_poll", false );
        $beta   = get_option( "wpstg_hide_beta", false );
        $rating = get_option( "wpstg_RatingDiv", false );

        if( $poll && $poll === "no" ) {
            update_option( 'wpstg_poll', 'no' );
        }
        if( $beta && $beta === "yes" ) {
            update_option( 'wpstg_beta', 'no' );
        }
        if( $rating && $rating === 'yes' ) {
            update_option( 'wpstg_rating', 'no' );
        }
    }
}
