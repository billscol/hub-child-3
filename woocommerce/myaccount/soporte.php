<?php
/**
 * Sistema de Soporte - Plataforma de Cursos
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Procesar nuevo ticket
if (isset($_POST['submit_ticket']) && wp_verify_nonce($_POST['ticket_nonce'], 'create_ticket')) {
  $ticket_title = sanitize_text_field($_POST['ticket_title']);
  $ticket_content = sanitize_textarea_field($_POST['ticket_content']);
  $course_id = intval($_POST['course_id']);
  
  $ticket_id = wp_insert_post(array(
    'post_title' => $ticket_title,
    'post_content' => $ticket_content,
    'post_type' => 'support_ticket',
    'post_status' => 'publish',
    'post_author' => $user_id,
  ));
  
  if ($ticket_id) {
    update_post_meta($ticket_id, '_course_id', $course_id);
    update_post_meta($ticket_id, '_ticket_status', 'open');
    update_post_meta($ticket_id, '_ticket_created', current_time('mysql'));
    echo '<div class="woocommerce-message" style="text-align:center">✅ Ticket creado exitosamente. Te responderemos en las próximas 24 horas.</div>';
  }
}

// Obtener cursos comprados (productos)
$customer_orders = wc_get_orders(array(
  'customer_id' => $user_id,
  'status' => array('wc-completed', 'wc-processing'),
  'limit' => -1,
));

$purchased_courses = array();
foreach ($customer_orders as $order) {
  foreach ($order->get_items() as $item) {
    $product = $item->get_product();
    if ($product) {
      $purchased_courses[$product->get_id()] = $product->get_name();
    }
  }
}

// Obtener tickets del usuario
$user_tickets = get_posts(array(
  'post_type' => 'support_ticket',
  'author' => $user_id,
  'posts_per_page' => -1,
  'orderby' => 'date',
  'order' => 'DESC',
));
?>

<div class="sp-support-section">
  <div style="margin-bottom:32px">
    <h2 style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
      <svg style="width:32px;height:32px;fill:#da0480" viewBox="0 0 24 24">
        <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/>
      </svg>
      Centro de Soporte
    </h2>
    <p style="color:#9ca3af;margin:0;font-size:15px">¿Tienes alguna duda sobre tus cursos? Estamos aquí para ayudarte.</p>
  </div>
  
  <div class="sp-support-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:40px">
    
    <!-- Crear Ticket -->
    <div class="sp-create-ticket" style="background:rgba(0,0,0,.3);padding:28px;border-radius:16px;border:1.5px solid rgba(218,4,128,.2)">
      <h3 style="font-size:20px;margin-bottom:20px;display:flex;align-items:center;gap:12px">
        <svg style="width:24px;height:24px;fill:#da0480" viewBox="0 0 24 24">
          <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        Crear Nuevo Ticket
      </h3>
      
      <?php if (empty($purchased_courses)): ?>
        <div style="text-align:center;padding:40px 20px;background:rgba(245,158,11,.1);border-radius:12px;border:1.5px dashed rgba(245,158,11,.3)">
          <svg style="width:48px;height:48px;fill:#f59e0b;margin:0 auto 16px" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
          </svg>
          <div style="color:#f59e0b;font-weight:600;margin-bottom:8px">No tienes cursos aún</div>
          <div style="color:#9ca3af;font-size:14px">Compra un curso para poder crear tickets de soporte</div>
        </div>
      <?php else: ?>
        <form method="post" action="">
          <?php wp_nonce_field('create_ticket', 'ticket_nonce'); ?>
          
          <label style="display:block;margin-bottom:18px">
            <span style="display:block;font-size:14px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Curso relacionado *</span>
            <select name="course_id" required style="width:100%;padding:14px 16px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);border-radius:12px;color:#fff;font-size:15px">
              <option value="">Selecciona un curso</option>
              <?php foreach ($purchased_courses as $id => $name): ?>
                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          
          <label style="display:block;margin-bottom:18px">
            <span style="display:block;font-size:14px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Asunto del ticket *</span>
            <input type="text" name="ticket_title" required placeholder="Ej: No puedo acceder al módulo 3" style="width:100%;padding:14px 16px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);border-radius:12px;color:#fff;font-size:15px" />
          </label>
          
          <label style="display:block;margin-bottom:24px">
            <span style="display:block;font-size:14px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Descripción detallada *</span>
            <textarea name="ticket_content" required rows="6" placeholder="Explica tu problema con el mayor detalle posible. Incluye capturas de pantalla si es necesario..." style="width:100%;padding:14px 16px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);border-radius:12px;color:#fff;font-size:15px;resize:vertical"></textarea>
          </label>
          
          <button type="submit" name="submit_ticket" style="width:100%;padding:14px;background:linear-gradient(135deg,#da0480,#b00368);color:#fff;border:none;border-radius:12px;font-weight:800;cursor:pointer;font-size:16px;transition:.3s;box-shadow:0 4px 16px rgba(218,4,128,.3)">
            📨 Enviar Ticket
          </button>
        </form>
      <?php endif; ?>
    </div>
    
    <!-- Estadísticas -->
    <div class="sp-support-stats" style="display:grid;gap:16px">
      <div style="background:linear-gradient(135deg,rgba(218,4,128,.15),rgba(218,4,128,.08));padding:24px;border-radius:16px;border:1.5px solid rgba(218,4,128,.3)">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:56px;height:56px;background:rgba(218,4,128,.2);border-radius:14px;display:flex;align-items:center;justify-content:center">
            <svg style="width:28px;height:28px;fill:#da0480" viewBox="0 0 24 24">
              <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:32px;font-weight:800;color:#fff"><?php echo count($user_tickets); ?></div>
            <div style="font-size:13px;color:#9ca3af;font-weight:600">Total de Tickets</div>
          </div>
        </div>
      </div>
      
      <div style="background:rgba(0,0,0,.3);padding:24px;border-radius:16px;border:1.5px solid rgba(255,255,255,.08)">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:56px;height:56px;background:rgba(34,197,94,.15);border-radius:14px;display:flex;align-items:center;justify-content:center">
            <svg style="width:28px;height:28px;fill:#22c55e" viewBox="0 0 24 24">
              <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:32px;font-weight:800;color:#fff">
              <?php 
                $resolved = array_filter($user_tickets, function($t) {
                  return get_post_meta($t->ID, '_ticket_status', true) === 'resolved';
                });
                echo count($resolved);
              ?>
            </div>
            <div style="font-size:13px;color:#9ca3af;font-weight:600">Resueltos</div>
          </div>
        </div>
      </div>
      
      <div style="background:rgba(0,0,0,.3);padding:20px;border-radius:16px;border:1.5px solid rgba(255,255,255,.08)">
        <div style="font-size:14px;color:#cbd5e1;margin-bottom:12px;font-weight:700">⚡ Tiempo de respuesta</div>
        <div style="font-size:13px;color:#9ca3af;line-height:1.6;margin-bottom:8px">
          • Tickets normales: <strong style="color:#22c55e">24-48 horas</strong>
        </div>
        <div style="font-size:13px;color:#9ca3af;line-height:1.6">
          • Tickets urgentes: <strong style="color:#f59e0b">8-12 horas</strong>
        </div>
      </div>
      
      <div style="background:linear-gradient(135deg,rgba(59,130,246,.1),rgba(59,130,246,.05));padding:20px;border-radius:16px;border:1.5px solid rgba(59,130,246,.2)">
        <div style="font-size:14px;color:#3b82f6;margin-bottom:8px;font-weight:700">💡 Tip para respuestas rápidas:</div>
        <div style="font-size:13px;color:#9ca3af;line-height:1.6">
          Incluye capturas de pantalla, describe los pasos que seguiste y menciona el navegador que usas.
        </div>
      </div>
    </div>
  </div>
  
  <!-- Lista de Tickets -->
  <div class="sp-tickets-list">
    <h3 style="font-size:22px;margin-bottom:20px;display:flex;align-items:center;gap:12px">
      <svg style="width:24px;height:24px;fill:#da0480" viewBox="0 0 24 24">
        <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
      </svg>
      Historial de Tickets
    </h3>
    
    <?php if (empty($user_tickets)): ?>
      <div style="text-align:center;padding:60px 20px;background:rgba(0,0,0,.2);border-radius:16px;border:1.5px dashed rgba(255,255,255,.1)">
        <svg style="width:64px;height:64px;fill:#4b5563;margin:0 auto 16px" viewBox="0 0 24 24">
          <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
        </svg>
        <div style="font-size:18px;color:#9ca3af;font-weight:600;margin-bottom:8px">No tienes tickets aún</div>
        <div style="font-size:14px;color:#6b7280">Cuando tengas alguna duda, crea tu primer ticket arriba</div>
      </div>
    <?php else: ?>
      <div style="display:grid;gap:16px">
        <?php foreach ($user_tickets as $ticket): 
          $status = get_post_meta($ticket->ID, '_ticket_status', true);
          $course_id = get_post_meta($ticket->ID, '_course_id', true);
          $course = wc_get_product($course_id);
          
          $status_data = array(
            'open' => array('bg' => 'rgba(59,130,246,.15)', 'text' => '#3b82f6', 'label' => '🔵 Abierto', 'icon' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z'),
            'in_progress' => array('bg' => 'rgba(245,158,11,.15)', 'text' => '#f59e0b', 'label' => '🟡 En Proceso', 'icon' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'),
            'resolved' => array('bg' => 'rgba(34,197,94,.15)', 'text' => '#22c55e', 'label' => '✅ Resuelto', 'icon' => 'M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'),
          );
          $current = $status_data[$status] ?? $status_data['open'];
          
          $time_ago = human_time_diff(strtotime($ticket->post_date), current_time('timestamp'));
        ?>
          <div style="background:rgba(0,0,0,.3);padding:24px;border-radius:16px;border:1.5px solid rgba(255,255,255,.08);transition:.3s" onmouseover="this.style.borderColor='rgba(218,4,128,.3)';this.style.transform='translateX(4px)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.transform='translateX(0)'">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;gap:16px">
              <div style="flex:1">
                <h4 style="color:#fff;font-size:18px;font-weight:700;margin-bottom:8px"><?php echo esc_html($ticket->post_title); ?></h4>
                <div style="font-size:13px;color:#9ca3af;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                  <?php if ($course): ?>
                    <span style="display:flex;align-items:center;gap:6px">
                      <svg style="width:16px;height:16px;fill:currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
                      <?php echo esc_html($course->get_name()); ?>
                    </span>
                  <?php endif; ?>
                  <span>•</span>
                  <span>Hace <?php echo $time_ago; ?></span>
                </div>
              </div>
              <span style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;background:<?php echo $current['bg']; ?>;color:<?php echo $current['text']; ?>;white-space:nowrap"><?php echo $current['label']; ?></span>
            </div>
            <div style="color:#cbd5e1;font-size:14px;line-height:1.7;margin-bottom:16px;padding:16px;background:rgba(0,0,0,.2);border-radius:12px;border-left:3px solid rgba(218,4,128,.4)">
              <?php echo wp_trim_words($ticket->post_content, 25); ?>
            </div>
            <div style="display:flex;gap:12px;align-items:center">
              <span style="padding:8px 16px;background:rgba(218,4,128,.1);color:#da0480;border-radius:10px;font-size:13px;font-weight:600">ID: #<?php echo $ticket->ID; ?></span>
              <span style="color:#6b7280;font-size:13px"><?php echo get_comments_number($ticket->ID); ?> respuestas</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  @media (max-width:968px){
    .sp-support-grid{
      grid-template-columns:1fr!important;
    }
  }
</style>
