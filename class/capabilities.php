<?php

class Alchemyst_Forms_Capabilities {

    // The big array of capabilities used to set up plugin permissions.
    // No filters are needed here because this is only used on plugin activation/deactivation.
    // Also role editor plugins exist.
    public static function caps() {
        return array(
            'administrator' => array(
                'af-view-entries',
                'af-edit-notifications',
                'af-edit-forms',
                'af-edit-form-additional-settings',
                'af-manage-settings',
                'af-use-tools',

                'edit_af-form',
                'read_af-form',
                'delete_af-form',
                'delete_af-forms',
                'delete_others_af-forms',
                'delete_published_af-forms',
                'delete_private_af-forms',
                'edit_af-forms',
                'edit_others_af-forms',
                'publish_af-forms',
                'read_private_af-forms',
                'edit_af-forms',

                'edit_af-entry',
                'read_af-entry',
                'delete_af-entry',
                'delete_af-entries',
                'edit_af-entries',
                'edit_others_af-entries',
                'publish_af-entries',
                'read_private_af-entries',
                'edit_af-entries',

                'edit_af-notification',
                'read_af-notification',
                'delete_af-notification',
                'delete_af-notifications',
                'edit_af-notifications',
                'edit_others_af-notifications',
                'publish_af-notifications',
                'read_private_af-notifications',
                'edit_af-notifications',
            ),
            'editor' => array(
                'af-view-entries',
                'af-edit-notifications',
                'af-edit-forms',
                'af-edit-form-additional-settings',

                'edit_af-form',
                'read_af-form',
                'delete_af-form',
                'delete_af-forms',
                'edit_af-forms',
                'edit_others_af-forms',
                'publish_af-forms',
                'read_private_af-forms',
                'edit_af-forms',

                'edit_af-entry',
                'read_af-entry',
                'delete_af-entry',
                'delete_af-entries',
                'edit_af-entries',
                'edit_others_af-entries',
                'publish_af-entries',
                'read_private_af-entries',
                'edit_af-entries',

                'edit_af-notification',
                'read_af-notification',
                'delete_af-notification',
                'delete_af-notifications',
                'edit_af-notifications',
                'edit_others_af-notifications',
                'publish_af-notifications',
                'read_private_af-notifications',
                'edit_af-notifications',
            ),
            'author' => array(

            ),
            'contributor' => array(

            ),
            'subscriber' => array(

            )
        );
    }

    public static function add_caps() {
        $capabilities = self::caps();

        foreach ($capabilities as $role => $caps) {
            if (empty($caps)) continue;
            $r = get_role($role);

            foreach ($caps as $cap) {
                $r->add_cap($cap);
            }
        }
    }

    public static function remove_caps() {
        $capabilities = self::caps();

        foreach ($capabilities as $role => $caps) {
            if (empty($caps)) continue;
            $r = get_role($role);

            foreach ($caps as $cap) {
                $r->remove_cap($cap);
            }
        }
    }
}
