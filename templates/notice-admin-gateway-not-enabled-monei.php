<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<div class="inline error">
    <p>
        <strong><?php esc_html_e( 'Gateway Disabled', 'monei' ); ?></strong>: <?php esc_html_e( 'The selected payment method is not active in the MONEI dashboard.', 'monei' ); ?>
        <a href="https://dashboard.monei.com/?action=signIn"><?php esc_html_e( 'Go to your MONEI Dashboard to activate it', 'monei' ); ?></a>
    </p>
</div>