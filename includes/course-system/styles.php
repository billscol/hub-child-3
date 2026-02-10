<?php
/**
 * Course System - Estilos
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'enqueue_course_system_styles');
function enqueue_course_system_styles() {
    wp_register_style('course-system-styles', false);
    wp_enqueue_style('course-system-styles');
    
    $css = '
    /* ========================================= */
    /* BADGES BASE - ALTURA Y PADDING UNIFICADOS */
    /* ========================================= */
    .author-badge-responsive,
    .calificacion-curso-badge,
    .fecha-actualizacion-responsive {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 18px;
        background: rgba(218, 4, 128, 0.1);
        border: 1px solid rgba(218, 4, 128, 0.3);
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #da0480;
        margin: 5px 0;
        transition: all 0.3s ease;
        width: fit-content;
        min-height: 36px;
        box-sizing: border-box;
    }
    
    .author-badge-responsive {
        text-decoration: none;
        position: relative;
    }
    
    .author-badge-responsive:hover {
        background: rgba(218, 4, 128, 0.2);
        border-color: rgba(218, 4, 128, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(218, 4, 128, 0.2);
        text-decoration: none;
        color: #da0480;
    }
    
    /* ========================================= */
    /* BADGE DE AUTOR                            */
    /* ========================================= */
    .author-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(218, 4, 128, 0.3);
        flex-shrink: 0;
    }
    
    .author-courses-badge {
        background: rgba(218, 4, 128, 0.2);
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 10px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    
    .author-text-full {
        display: inline;
    }
    
    .author-text-medium,
    .author-text-short {
        display: none;
    }
    
    .author-badge-responsive[data-bio]:hover::after {
        content: attr(data-bio);
        position: absolute;
        top: 100%;
        left: 0;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 400;
        max-width: 280px;
        z-index: 1000;
        margin-top: 8px;
        display: block;
        line-height: 1.4;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        white-space: normal;
    }
    
    /* ========================================= */
    /* BADGE DE CALIFICACIÓN                     */
    /* ========================================= */
    .rating-stars {
        display: flex;
        gap: 2px;
        align-items: center;
        font-size: 14px;
        line-height: 1;
    }
    
    .rating-stars .star-full {
        color: #da0480;
    }
    
    .rating-stars .star-empty {
        color: rgba(218, 4, 128, 0.3);
    }
    
    .rating-stars .star-half {
        position: relative;
        display: inline-block;
        color: rgba(218, 4, 128, 0.3);
    }
    
    .rating-stars .star-half-fill {
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        overflow: hidden;
        color: #da0480;
    }
    
    .rating-average {
        color: #da0480;
        font-weight: 600;
        white-space: nowrap;
        font-size: 11px;
    }
    
    .rating-count {
        color: rgba(255, 255, 255, 0.6);
        white-space: nowrap;
        display: inline;
        font-size: 10px;
    }
    
    .rating-count-short {
        display: none;
    }
    
    .no-reviews-text {
        color: rgba(218, 4, 128, 0.6);
        font-size: 10px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    /* ========================================= */
    /* BADGE DE FECHA                            */
    /* ========================================= */
    .update-icon {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        min-width: 14px;
    }
    
    .update-text-full {
        color: #da0480;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .update-text-medium,
    .update-text-short {
        display: none;
    }
    
    /* ========================================= */
    /* RESPONSIVE: TABLET (768px - 1024px)      */
    /* ========================================= */
    @media (max-width: 1024px) {
        .author-badge-responsive,
        .calificacion-curso-badge,
        .fecha-actualizacion-responsive {
            padding: 5px 14px;
            gap: 6px;
            font-size: 10.5px;
            min-height: 32px;
        }
        
        .author-avatar {
            width: 22px;
            height: 22px;
        }
        
        .author-text-full {
            display: none;
        }
        
        .author-text-medium {
            display: inline;
        }
        
        .author-courses-badge {
            padding: 2px 5px;
            font-size: 9px;
        }
        
        .rating-stars {
            font-size: 13px;
            gap: 1px;
        }
        
        .rating-average {
            font-size: 10px;
        }
        
        .rating-count {
            display: none;
        }
        
        .rating-count-short {
            display: inline;
            color: rgba(255, 255, 255, 0.6);
            font-size: 9px;
        }
        
        .no-reviews-text {
            font-size: 9px;
        }
        
        .update-icon {
            width: 12px;
            height: 12px;
        }
        
        .update-text-full {
            display: none;
        }
        
        .update-text-medium {
            display: inline;
            color: #da0480;
            font-weight: 600;
            white-space: nowrap;
        }
    }
    
    /* ========================================= */
    /* RESPONSIVE: MÓVIL (< 768px)              */
    /* ========================================= */
    @media (max-width: 768px) {
        .author-badge-responsive,
        .calificacion-curso-badge,
        .fecha-actualizacion-responsive {
            padding: 4px 12px;
            gap: 5px;
            font-size: 10px;
            min-height: 28px;
        }
        
        .author-avatar {
            width: 20px;
            height: 20px;
        }
        
        .author-text-full,
        .author-text-medium {
            display: none;
        }
        
        .author-text-short {
            display: inline;
        }
        
        .author-courses-badge {
            padding: 2px 4px;
            font-size: 8px;
        }
        
        .author-badge-responsive[data-bio]:hover::after {
            max-width: 200px;
            font-size: 9px;
        }
        
        .rating-stars {
            font-size: 12px;
            gap: 1px;
        }
        
        .rating-average {
            font-size: 9px;
        }
        
        .rating-count-short {
            font-size: 8px;
        }
        
        .no-reviews-text {
            font-size: 8px;
        }
        
        .update-icon {
            width: 11px;
            height: 11px;
        }
        
        .update-text-full,
        .update-text-medium {
            display: none;
        }
        
        .update-text-short {
            display: inline;
            color: #da0480;
            font-weight: 600;
            white-space: nowrap;
        }
    }
    
    /* ========================================= */
    /* RESPONSIVE: MÓVIL PEQUEÑO (< 480px)      */
    /* ========================================= */
    @media (max-width: 480px) {
        .author-badge-responsive,
        .calificacion-curso-badge,
        .fecha-actualizacion-responsive {
            padding: 3px 10px;
            gap: 4px;
            font-size: 9px;
            min-height: 26px;
        }
        
        .author-avatar {
            width: 18px;
            height: 18px;
        }
        
        .author-text-short {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .author-courses-badge {
            padding: 1px 3px;
            font-size: 7px;
        }
        
        .author-badge-responsive[data-bio]:hover::after {
            display: none;
        }
        
        .rating-stars {
            font-size: 11px;
        }
        
        .rating-average {
            font-size: 8px;
        }
        
        .rating-count-short {
            font-size: 7px;
        }
        
        .no-reviews-text {
            font-size: 7px;
        }
        
        .update-icon {
            width: 10px;
            height: 10px;
        }
        
        .update-text-short {
            font-size: 8px;
        }
    }
    ';
    
    wp_add_inline_style('course-system-styles', $css);
}
