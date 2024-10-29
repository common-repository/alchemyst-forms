<?php

class Alchemyst_Forms_Form_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'alchemyst_form',
			'description' => 'Display one of your forms in a widget area.',
		);
		parent::__construct('alchemyst_form', 'Alchemyst Form', $widget_ops);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
        if (!empty($instance['form'])) {
            // No need to reinvent the weel here.
            echo do_shortcode('[alchemyst-form id="' . $instance['form'] . '"]');
        }
        else {
            // Shouldnt be possible really..
            ?>
            <div class="af-widget af-widget-form-error">
                <p>
                    No form was chosen.
                </p>
            </div>
            <?php
        }
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form($instance) {
		// outputs the options form on admin
        $forms = Alchemyst_Form::get_all_forms();
        $form_id = ! empty($instance['form']) ? $instance['form'] : null;

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>"><?php _e( esc_attr( 'Select a Form To Display' ) ); ?></label>
            <select
                class="widefat"
                id="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'form' ) ); ?>"
            >
                <?php foreach ($forms as $form) : ?>
                    <option
                        value="<?=esc_attr($form->ID)?>"
                        <?=($form->ID == $form_id ? 'selected="selected"' : null)?>
                    >
                        <?=esc_attr($form->post_title)?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update($new_instance, $old_instance) {
		// processes widget options to be saved
        $instance = array();
        $instance['form'] = ( ! empty( $new_instance['form'] ) ) ? strip_tags( $new_instance['form'] ) : '';

		return $instance;
	}

}

add_action('widgets_init', function() {
    register_widget('Alchemyst_Forms_Form_Widget');
});
