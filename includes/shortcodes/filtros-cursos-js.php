<?php
/**
 * JavaScript para el slider de precio de filtros
 */
if (!defined('ABSPATH')) exit;

add_action('wp_footer', 'filtros_cursos_slider_js');

function filtros_cursos_slider_js() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sliderMin = document.querySelector('.precio-slider-min');
        const sliderMax = document.querySelector('.precio-slider-max');
        
        if (!sliderMin || !sliderMax) return;

        const displayMin = document.querySelector('.precio-min-display');
        const displayMax = document.querySelector('.precio-max-display');
        const track = document.querySelector('.precio-slider-track');

        const precioMinInicial = parseInt(sliderMin.getAttribute('data-min'));
        const precioMaxInicial = parseInt(sliderMax.getAttribute('data-max'));

        function formatearPrecio(valor) {
            return new Intl.NumberFormat('es-CO').format(valor);
        }

        function actualizarSlider() {
            let min = parseInt(sliderMin.value);
            let max = parseInt(sliderMax.value);

            if (min > max - 10000) {
                if (this === sliderMin) {
                    sliderMin.value = max - 10000;
                    min = max - 10000;
                } else {
                    sliderMax.value = min + 10000;
                    max = min + 10000;
                }
            }

            displayMin.textContent = formatearPrecio(min);
            displayMax.textContent = formatearPrecio(max);

            const porcentajeMin = ((min - precioMinInicial) / (precioMaxInicial - precioMinInicial)) * 100;
            const porcentajeMax = ((max - precioMinInicial) / (precioMaxInicial - precioMinInicial)) * 100;

            const style = document.createElement('style');
            style.textContent = `.precio-slider-track::before { left: ${porcentajeMin}%; right: ${100 - porcentajeMax}%; }`;
            
            const oldStyle = document.head.querySelector('style[data-slider]');
            if (oldStyle) oldStyle.remove();
            style.setAttribute('data-slider', 'true');
            document.head.appendChild(style);
        }

        sliderMin.addEventListener('input', actualizarSlider);
        sliderMax.addEventListener('input', actualizarSlider);

        actualizarSlider();
    });
    </script>
    <?php
}
