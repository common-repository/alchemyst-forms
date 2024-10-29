<?php

/**
 * FREE VERSION
 */
class Alchemyst_Forms_License {
    private static $instance;

    public static function get_instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct() {
        $this->license_key = '0';
    }

    public static function license_is_valid() {
        return false;
    }

    public function license_is_free_version() {
        return true;
    }

    public function get_disallowed_message() {
        ?>
        <p class="alchemyst-license-required">
            <em>This feature is limited to paid license holders only.</em><br>
            <a href="#">Purchase a License Now</a>
            <img src="<?=ALCHEMYST_FORMS__PLUGIN_URL?>css/pegasus.png">
        </p>
        <?php
    }

    private function __clone() {}
    private function __wakeup() {}
}
