<?php
/**
 * Activador del Sistema de Cursos
 * Se ejecuta al cargar el tema por primera vez
 * 
 * @package CoursesSystem
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Activador del Sistema
 */
class Courses_System_Activator {
    
    /**
     * Ejecutar activación
     */
    public static function activate() {
        // Crear tablas de base de datos
        self::create_tables();
        
        // Crear roles y capacidades
        self::create_roles();
        
        // Configuración inicial
        self::initial_setup();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Crear tablas de base de datos
     */
    private static function create_tables() {
        require_once COURSES_SYSTEM_PATH . 'database/tables.php';
        
        if (!courses_tables_exist()) {
            courses_create_tables();
        }
    }
    
    /**
     * Crear roles personalizados
     */
    private static function create_roles() {
        // Rol de instructor (opcional para futuro)
        if (!get_role('instructor')) {
            add_role(
                'instructor',
                'Instructor',
                array(
                    'read' => true,
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'publish_posts' => true,
                    'upload_files' => true,
                )
            );
        }
        
        // Agregar capacidades a administrador
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_courses');
            $admin->add_cap('edit_courses');
            $admin->add_cap('edit_lessons');
        }
    }
    
    /**
     * Configuración inicial
     */
    private static function initial_setup() {
        // Marcar sistema como instalado
        update_option('courses_system_installed', true);
        update_option('courses_system_version', '1.0.0');
        update_option('courses_system_installed_date', current_time('mysql'));
        
        // Opciones por defecto
        $defaults = array(
            'courses_enable_certificates' => 'no',
            'courses_enable_reviews' => 'yes',
            'courses_enable_prerequisites' => 'no',
            'courses_auto_enroll_free' => 'yes'
        );
        
        foreach ($defaults as $key => $value) {
            if (!get_option($key)) {
                update_option($key, $value);
            }
        }
    }
    
    /**
     * Verificar si el sistema está instalado
     */
    public static function is_installed() {
        return get_option('courses_system_installed', false);
    }
    
    /**
     * Obtener versión instalada
     */
    public static function get_version() {
        return get_option('courses_system_version', '0.0.0');
    }
}

/**
 * Ejecutar al cargar el tema
 */
function courses_system_init() {
    // Verificar si ya está instalado
    if (!Courses_System_Activator::is_installed()) {
        Courses_System_Activator::activate();
    }
    
    // Verificar actualizaciones
    $current_version = Courses_System_Activator::get_version();
    $new_version = '1.0.0';
    
    if (version_compare($current_version, $new_version, '<')) {
        // Actualizar
        update_option('courses_system_version', $new_version);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
add_action('after_setup_theme', 'courses_system_init');

/**
 * Desactivador (para futuro uso)
 */
function courses_system_deactivate() {
    // NO eliminar datos, solo limpiar cache
    flush_rewrite_rules();
}

/**
 * Mostrar mensaje de bienvenida al instalar
 */
function courses_system_welcome_notice() {
    if (!Courses_System_Activator::is_installed()) {
        return;
    }
    
    $installed_date = get_option('courses_system_installed_date');
    
    // Mostrar solo las primeras 24 horas
    if ($installed_date && (strtotime($installed_date) > strtotime('-24 hours'))) {
        $dismissed = get_option('courses_welcome_dismissed', false);
        
        if (!$dismissed && current_user_can('manage_options')) {
            ?>
            <div class="notice notice-success is-dismissible" id="courses-welcome-notice">
                <h2>🎉 ¡Sistema de Cursos Activado!</h2>
                <p>
                    El sistema de cursos está listo para usar. Puedes comenzar a:
                </p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li>Crear cursos desde <a href="<?php echo admin_url('post-new.php?post_type=course'); ?>">aquí</a></li>
                    <li>Añadir lecciones a tus cursos</li>
                    <li>Vincular productos de WooCommerce con cursos</li>
                    <li>Ver estadísticas de progreso de estudiantes</li>
                </ul>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=course'); ?>" class="button button-primary">Ver Cursos</a>
                    <a href="#" class="button" onclick="coursesDismissWelcome(event)">Entendido</a>
                </p>
            </div>
            
            <script>
                function coursesDismissWelcome(e) {
                    e.preventDefault();
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=courses_dismiss_welcome'
                    }).then(() => {
                        document.getElementById('courses-welcome-notice').remove();
                    });
                }
            </script>
            <?php
        }
    }
}
add_action('admin_notices', 'courses_system_welcome_notice');

/**
 * Dismiss welcome notice
 */
function courses_ajax_dismiss_welcome() {
    update_option('courses_welcome_dismissed', true);
    wp_send_json_success();
}
add_action('wp_ajax_courses_dismiss_welcome', 'courses_ajax_dismiss_welcome');

/**
 * Agregar link de configuración en menú de admin
 */
function courses_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=course',
        'Configuración',
        '⚙️ Configuración',
        'manage_options',
        'courses-settings',
        'courses_render_settings_page'
    );
    
    add_submenu_page(
        'edit.php?post_type=course',
        'Estadísticas',
        '📊 Estadísticas',
        'manage_options',
        'courses-stats',
        'courses_render_stats_page'
    );
}
add_action('admin_menu', 'courses_add_settings_page');

/**
 * Renderizar página de configuración
 */
function courses_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>⚙️ Configuración del Sistema de Cursos</h1>
        
        <div style="background: #fff; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2>Información del Sistema</h2>
            <table class="widefat">
                <tr>
                    <td><strong>Versión:</strong></td>
                    <td><?php echo Courses_System_Activator::get_version(); ?></td>
                </tr>
                <tr>
                    <td><strong>Instalado:</strong></td>
                    <td><?php echo get_option('courses_system_installed_date'); ?></td>
                </tr>
                <tr>
                    <td><strong>Tablas de BD:</strong></td>
                    <td><?php echo courses_tables_exist() ? '✅ Creadas' : '❌ No creadas'; ?></td>
                </tr>
                <tr>
                    <td><strong>WooCommerce:</strong></td>
                    <td><?php echo class_exists('WooCommerce') ? '✅ Activo' : '❌ No instalado'; ?></td>
                </tr>
            </table>
            
            <?php 
            $stats = courses_get_db_stats();
            ?>
            
            <h2 style="margin-top: 30px;">Estadísticas de Base de Datos</h2>
            <table class="widefat">
                <tr>
                    <td><strong>Total de Progresos:</strong></td>
                    <td><?php echo $stats['total_progress']; ?></td>
                </tr>
                <tr>
                    <td><strong>Lecciones Completadas:</strong></td>
                    <td><?php echo $stats['total_lessons_completed']; ?></td>
                </tr>
                <tr>
                    <td><strong>Accesos Otorgados:</strong></td>
                    <td><?php echo $stats['total_access']; ?></td>
                </tr>
                <tr>
                    <td><strong>Cursos Activos:</strong></td>
                    <td><?php echo $stats['active_courses']; ?></td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fff; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2>Shortcodes Disponibles</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Descripción</th>
                        <th>Uso</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[courses_list]</code></td>
                        <td>Lista de cursos en grid</td>
                        <td><code>[courses_list limit="6" columns="3"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[user_progress]</code></td>
                        <td>Dashboard de progreso del usuario</td>
                        <td><code>[user_progress show_stats="yes"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[course_progress]</code></td>
                        <td>Progreso de un curso específico</td>
                        <td><code>[course_progress id="123" style="bar"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[latest_courses]</code></td>
                        <td>Últimos cursos publicados</td>
                        <td><code>[latest_courses limit="6"]</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Renderizar página de estadísticas
 */
function courses_render_stats_page() {
    global $wpdb;
    
    // Obtener estadísticas globales
    $total_courses = wp_count_posts('course')->publish;
    $total_lessons = wp_count_posts('lesson')->publish;
    
    $table_progress = $wpdb->prefix . 'course_progress';
    $total_enrollments = $wpdb->get_var("SELECT COUNT(*) FROM $table_progress");
    $completed_courses = $wpdb->get_var("SELECT COUNT(*) FROM $table_progress WHERE status = 'completed'");
    
    ?>
    <div class="wrap">
        <h1>📊 Estadísticas del Sistema de Cursos</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: #fff; padding: 25px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 800;"><?php echo $total_courses; ?></div>
                <div style="font-size: 14px; opacity: 0.9;">Cursos Publicados</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; padding: 25px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 800;"><?php echo $total_lessons; ?></div>
                <div style="font-size: 14px; opacity: 0.9;">Lecciones Totales</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 25px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 800;"><?php echo $total_enrollments; ?></div>
                <div style="font-size: 14px; opacity: 0.9;">Inscripciones</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: #fff; padding: 25px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 800;"><?php echo $completed_courses; ?></div>
                <div style="font-size: 14px; opacity: 0.9;">Cursos Completados</div>
            </div>
        </div>
        
        <?php
        // Top 10 cursos más populares
        $popular = $wpdb->get_results(
            "SELECT course_id, COUNT(*) as students 
             FROM $table_progress 
             GROUP BY course_id 
             ORDER BY students DESC 
             LIMIT 10"
        );
        ?>
        
        <div style="background: #fff; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2>Top 10 Cursos Más Populares</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Estudiantes</th>
                        <th>Lecciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popular as $item) : 
                        $course = get_post($item->course_id);
                        if (!$course) continue;
                        $course_manager = Course_Manager::get_instance();
                        $lessons = $course_manager->count_lessons($course->ID);
                    ?>
                        <tr>
                            <td>
                                <a href="<?php echo get_edit_post_link($course->ID); ?>">
                                    <?php echo esc_html($course->post_title); ?>
                                </a>
                            </td>
                            <td><?php echo $item->students; ?></td>
                            <td><?php echo $lessons; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>