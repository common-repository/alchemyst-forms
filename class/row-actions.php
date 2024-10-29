<?php
/**
 * Adds some row actions to our custom post type
 */

class Alchemyst_Forms_Row_Actions {

    /**
     * Just registers a filter on post_row_actions.
     */
    public static function init() {
        add_filter('post_row_actions', array('Alchemyst_Forms_Row_Actions', 'setup_actions'), 10, 2);
    }

    /**
     * Setup row acctions for the AF_POSTTYPE post type.
     *
     * @param $actions - Current array of action (key => link) pairs
     * @param $post - Current post object for row
     */
    public static function setup_actions($actions, $post) {
        if ($post->post_type != AF_POSTTYPE) return $actions;

        unset($actions['inline hide-if-no-js']); // Ditch quickedit.

        $trash = $actions['trash']; // Array slice seemed more complicated than just doing this...
        unset($actions['trash']);

        // Set the actions for real now.
        // Uses the same filters that potentially hide these sections in the form builder to prevent the row action link from being shown.
        // Also verifies capabilities.
        if (apply_filters('alchemyst_forms:form-builder-view-section:form-builder', true, $post->ID) && current_user_can('af-edit-forms')) {
            $actions['alchemyst_forms_notifications'] = '<a href="'.get_edit_post_link($post->ID).'#notifications">Notifications</a>';
        }

        if (apply_filters('alchemyst_forms:form-builder-view-section:notifications', true, $post->ID) && current_user_can('af-edit-notifications')) {
            $actions['alchemyst_forms_entries'] = '<a href="'.get_edit_post_link($post->ID).'#entries">Entries</a>';
        }

        if (apply_filters('alchemyst_forms:form-builder-view-section:entries', true, $post->ID) && current_user_can('af-view-entries')) {
            $actions['trash'] = $trash;
        }

        return $actions;
    }
}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Row_Actions', 'init'));
