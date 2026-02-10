<?php
/**
 * Notificación por email cuando se resuelve un reporte
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function send_report_resolved_email($post_id) {
    $user_id = get_post_meta($post_id, '_user_id', true);
    $product_id = get_post_meta($post_id, '_product_id', true);
    
    $user = get_userdata($user_id);
    $product = wc_get_product($product_id);
    
    if (!$user || !$product) {
        return;
    }
    
    $to = $user->user_email;
    $subject = 'Tu reporte ha sido resuelto - ' . get_bloginfo('name');
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #da0480, #b00368); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #da0480; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="margin: 0;">✅ Reporte Resuelto</h1>
            </div>
            <div class="content">
                <p>Hola <strong>' . esc_html($user->display_name) . '</strong>,</p>
                
                <p>Te informamos que tu reporte sobre el curso <strong>' . esc_html($product->get_name()) . '</strong> ha sido revisado y resuelto.</p>
                
                <p>Nuestro equipo ha trabajado en solucionar el problema que reportaste. Si persiste algún inconveniente, no dudes en contactarnos nuevamente.</p>
                
                <p>Gracias por ayudarnos a mejorar la calidad de nuestros cursos.</p>
                
                <a href="' . get_permalink($product_id) . '" class="button">Ver Curso</a>
                
                <p style="margin-top: 30px; color: #666; font-size: 13px;">
                    Saludos,<br>
                    El equipo de ' . get_bloginfo('name') . '
                </p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
    );
    
    wp_mail($to, $subject, $message, $headers);
}
?>