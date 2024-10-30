<?php

const PLUGIN_ID         = 'custom-post-list-order';
const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';

$cplo_settings = get_option( self::PLUGIN_ID );
$cplo_targets = isset( $cplo_settings['targets'] ) ? $cplo_settings['targets'] : array();
$cplo_order_by = isset( $cplo_settings['order_by'] ) ? $cplo_settings['order_by'] : array();
$cplo_order = isset( $cplo_settings['order'] ) ? $cplo_settings['order'] : array();

?>

<div class="wrapp">

<h2>Custom Post List Order - Settings</h2>

<div class="postbox">
<div class="inside">

<form method="post">

<?php wp_nonce_field( self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME ) ?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">Target</th>
            <td>
                <?php
                $target_list = array( 'Top Page', 'Category Pages', 'Tag Pages' );

                foreach( $target_list as $target ) {
                    if ( $cplo_targets && in_array($target, $cplo_targets) ) {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '';
                    }

                    echo '<label>';
                    echo '<input type="checkbox" name="target[]" value="' . esc_html( $target ) . '"' . $checked . '"> ' . esc_html( $target ) ;
                    echo '</label><br>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th scope="row">Order by</th>
            <td>
                <?php
                $orderby_list = array('Published Date', 'Modified Date', 'Page Views', 'Random');

                foreach( $orderby_list as $orderby ) {
                    if ( $orderby == $cplo_order_by ) {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '';
                    }

                    echo '<label>';
                    echo '<input type="radio" name="order_by" value="' . esc_html( $orderby ) . '"' . $checked . '"> ' . esc_html( $orderby ) ;
                    echo '</label><br>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th scope="row">Order</th>
            <td>
                <?php
                $order_list = array('DESC', 'ASC');

                foreach( $order_list as $order ) {
                    if ( $order == $cplo_order ) {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '';
                    }

                    echo '<label>';
                    echo '<input type="radio" name="order" value="' . esc_html( $order ) . '"' . $checked . '"> ' . esc_html( $order ) ;
                    echo '</label><br>';
                }
                ?>
            </td>
        </tr>
    </tbody>
</table>

<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save" /></p>

</form>

</div>
</div>
</div>