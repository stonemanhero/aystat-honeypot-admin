<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Aystat_Honeypot_Admin_Login_View {

    public static function render( $options, $show_error, $is_locked ) {
        $title              = ! empty( $options['login_title'] ) ? $options['login_title'] : '';
        $logo_url           = ! empty( $options['logo_url'] ) ? $options['logo_url'] : '';
        $err_msg            = ! empty( $options['error_message'] ) ? $options['error_message'] : 'Username or password is not correct.';
        $lck_msg            = ! empty( $options['limit_message'] ) ? $options['limit_message'] : 'Access blocked.';

        $title_color        = ! empty( $options['title_color'] ) ? $options['title_color'] : '#3c434a';
        $bg_color           = ! empty( $options['bg_color'] ) ? $options['bg_color'] : '#f0f0f1';
        $font_family        = ! empty( $options['font_family'] ) ? $options['font_family'] : 'sans-serif';
        $box_bg_color       = ! empty( $options['box_bg_color'] ) ? $options['box_bg_color'] : '#ffffff';
        $box_text_color     = ! empty( $options['box_text_color'] ) ? $options['box_text_color'] : '#3c434a';
        $box_border_color   = ! empty( $options['box_border_color'] ) ? $options['box_border_color'] : '#c3c4c7';
        $input_bg_color     = ! empty( $options['input_bg_color'] ) ? $options['input_bg_color'] : '#ffffff';
        $input_text_color   = ! empty( $options['input_text_color'] ) ? $options['input_text_color'] : '#3c434a';
        $input_border_color = ! empty( $options['input_border_color'] ) ? $options['input_border_color'] : '#8c8f94';
        $box_border_radius  = isset( $options['box_border_radius'] ) ? (int) $options['box_border_radius'] : 0;
        $box_shadow         = ! empty( $options['box_shadow'] ) ? $options['box_shadow'] : 'none';
        $btn_color          = ! empty( $options['btn_color'] ) ? $options['btn_color'] : '#2271b1';
        $btn_text_color     = ! empty( $options['btn_text_color'] ) ? $options['btn_text_color'] : '#ffffff';
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html( $title ); ?></title>
        </head>
        <body style="font-family: <?php echo esc_attr( $font_family ); ?>; background: <?php echo esc_attr( $bg_color ); ?>; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
        <div style="width: 100%; max-width: 320px;">
            <h1 style="text-align: center; font-size: 24px; font-weight: normal; color: <?php echo esc_attr( $title_color ); ?>; margin-top: 0; margin-bottom: 16px;"><?php echo esc_html( $title ); ?></h1>

            <?php if ( ! empty( $logo_url ) ) : ?>
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" style="max-width: 100%; max-height: 80px; height: auto;">
                </div>
            <?php endif; ?>

            <?php if ( $is_locked ) : ?>
                <div style="padding: 12px; margin-bottom: 16px; border-left: 4px solid #d63638; background: #fff; color: #3c434a;"><?php printf( esc_html__( 'Error: %s', 'aystat-honeypot-admin' ), esc_html( $lck_msg ) ); ?></div>
            <?php elseif ( $show_error ) : ?>
                <div style="padding: 12px; margin-bottom: 16px; border-left: 4px solid #d63638; background: #fff; color: #3c434a;"><?php printf( esc_html__( 'Error: %s', 'aystat-honeypot-admin' ), esc_html( $err_msg ) ); ?></div>
            <?php endif; ?>

            <div style="background: <?php echo esc_attr( $box_bg_color ); ?>; padding: 24px; border: 1px solid <?php echo esc_attr( $box_border_color ); ?>; border-radius: <?php echo esc_attr( $box_border_radius ); ?>px; box-shadow: <?php echo esc_attr( $box_shadow ); ?>; color: <?php echo esc_attr( $box_text_color ); ?>;">
                <form method="post" action="">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px;"><?php esc_html_e( 'Username', 'aystat-honeypot-admin' ); ?></label>
                    <input type="text" name="username" required style="width: 100%; padding: 4px 8px; font-size: 20px; margin-bottom: 16px; box-sizing: border-box; background: <?php echo esc_attr( $input_bg_color ); ?>; color: <?php echo esc_attr( $input_text_color ); ?>; border: 1px solid <?php echo esc_attr( $input_border_color ); ?>; border-radius: 4px;">

                    <label style="display: block; margin-bottom: 8px; font-size: 14px;"><?php esc_html_e( 'Password', 'aystat-honeypot-admin' ); ?></label>
                    <input type="password" name="password" required style="width: 100%; padding: 4px 8px; font-size: 20px; margin-bottom: 16px; box-sizing: border-box; background: <?php echo esc_attr( $input_bg_color ); ?>; color: <?php echo esc_attr( $input_text_color ); ?>; border: 1px solid <?php echo esc_attr( $input_border_color ); ?>; border-radius: 4px;">

                    <button type="submit" onmouseover="this.style.filter='brightness(90%)'" onmouseout="this.style.filter='none'" style="background: <?php echo esc_attr( $btn_color ); ?>; color: <?php echo esc_attr( $btn_text_color ); ?>; border: none; padding: 8px 16px; cursor: pointer; float: right; border-radius: 4px;"><?php esc_html_e( 'Log In', 'aystat-honeypot-admin' ); ?></button>
                    <div style="clear:both;"></div>
                </form>
            </div>
        </div>
        </body>
        </html>
        <?php
    }
}