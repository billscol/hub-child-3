# ✅ Checklist de Testing - Reorganización

## 📌 Antes de Hacer Merge

Usa este checklist para verificar que todo funciona correctamente después de aplicar los cambios.

---

## 🔑 1. Shortcode: [sp_auth]

### Frontend
- [ ] Botón "Acceder" aparece correctamente
- [ ] Modal de login se abre al hacer clic
- [ ] Tabs de "Iniciar sesión" y "Registrarme" funcionan
- [ ] Formulario de login envía correctamente
- [ ] Formulario de registro envía correctamente
- [ ] Usuario logueado ve su avatar y dropdown
- [ ] Links del dropdown funcionan (Mi cuenta, Pedidos, etc.)
- [ ] Cerrar sesión funciona

### Responsive
- [ ] Se ve bien en móvil
- [ ] Modal es responsive
- [ ] Dropdown funciona en móvil

---

## 📚 2. Shortcode: [course_curriculum]

### Backend (Admin)
- [ ] Metabox "Currículum del Curso" aparece en productos
- [ ] Se pueden agregar módulos
- [ ] Se pueden agregar lecciones a cada módulo
- [ ] Se puede marcar módulos como "bloqueados"
- [ ] Se pueden eliminar módulos y lecciones
- [ ] Los datos se guardan correctamente
- [ ] Contador de módulos y lecciones funciona

### Frontend (Página de Producto)
- [ ] Currículum se muestra correctamente
- [ ] Módulos se expanden/colapsan al hacer clic
- [ ] Módulos bloqueados muestran "Contenido Privado"
- [ ] Usuarios que compraron ven todo el contenido
- [ ] Estadísticas (Nº módulos y lecciones) se muestran

### Shortcode en Elementor
- [ ] `[course_curriculum]` funciona en Elementor
- [ ] Se ve correctamente en el diseño

---

## ⭐ 3. Shortcode: [resenas_producto]

### Frontend
- [ ] Reseña destacada se muestra (si existe)
- [ ] Formulario de reseña aparece
- [ ] Usuarios NO logueados ven mensaje para iniciar sesión
- [ ] Click en "iniciar sesión" abre modal de sp_auth
- [ ] Usuarios logueados pueden enviar reseñas
- [ ] Sistema de estrellas funciona (hover y click)
- [ ] Formulario se envía correctamente
- [ ] Mensaje de confirmación aparece

### Backend
- [ ] Reseñas aparecen como comentarios pendientes
- [ ] Se pueden aprobar/rechazar reseñas

---

## 🎥 4. Shortcode: [video_producto]

### Backend (Metabox)
- [ ] Metabox "Video del Producto" aparece en productos
- [ ] Se puede agregar URL de video
- [ ] Previsualización del video funciona
- [ ] Se puede eliminar el video
- [ ] Video se guarda correctamente

### Frontend
- [ ] Video se reproduce en silencio por 10 segundos
- [ ] Botón play aparece sobre el video
- [ ] Click en video abre modal con video completo
- [ ] Video en modal tiene controles
- [ ] Botón X cierra el modal
- [ ] Click fuera del modal lo cierra
- [ ] ESC cierra el modal
- [ ] Si no hay video, muestra imagen del producto

---

## ⚠️ 5. Shortcode: [boton_reporte]

### Frontend
- [ ] Botón "Reportar un problema" aparece
- [ ] Solo aparece si el usuario está logueado
- [ ] Click abre modal de reporte
- [ ] Select de tipo de problema funciona
- [ ] Textarea de descripción funciona
- [ ] Formulario se envía correctamente
- [ ] Mensaje de confirmación aparece
- [ ] Modal se cierra después de enviar

### Backend (Admin)
- [ ] Aparece menu "Reportes de Cursos"
- [ ] Lista de reportes se muestra correctamente
- [ ] Columnas personalizadas funcionan (Curso, Usuario, Estado)
- [ ] Se puede ver detalles de cada reporte
- [ ] Se puede cambiar estado a "Resuelto"
- [ ] Email se envía al resolver reporte

---

## 🛍️ 6. Shortcode: [dual_buy_buttons]

### Frontend
- [ ] Ambos botones aparecen correctamente
- [ ] Botón "Comprar Ahora" tiene estilo rosa
- [ ] Botón "Ver Carrito" tiene estilo outline
- [ ] Hover en botones funciona

### Funcionalidad "Comprar Ahora"
- [ ] Usuario NO logueado: abre modal de login
- [ ] Usuario logueado: agrega al carrito
- [ ] Muestra estado "cargando" durante proceso
- [ ] Redirige a checkout después de agregar
- [ ] Maneja errores correctamente

### Funcionalidad "Ver Carrito"
- [ ] Link a carrito funciona
- [ ] Redirige correctamente

---

## 🔧 7. Verificaciones Generales

### Archivos
- [ ] `functions.php` tiene solo ~150 líneas
- [ ] Todos los shortcodes están en `/includes/shortcodes/`
- [ ] `shortcodes-loader.php` carga todos los shortcodes
- [ ] No hay errores de PHP en logs
- [ ] No hay errores de JavaScript en consola

### Performance
- [ ] Página de producto carga rápido
- [ ] Shortcodes no ralentizan el sitio
- [ ] No hay consultas SQL innecesarias

### Compatibilidad
- [ ] Funciona en Chrome
- [ ] Funciona en Firefox
- [ ] Funciona en Safari
- [ ] Funciona en Edge
- [ ] Funciona en móviles (iOS y Android)

---

## 📧 8. Emails

- [ ] Email de reporte resuelto se envía
- [ ] Email tiene formato correcto (HTML)
- [ ] Email contiene información correcta

---

## 👨‍💻 9. Panel de Admin

- [ ] No hay errores en dashboard
- [ ] Metaboxes aparecen correctamente
- [ ] Custom Post Types funcionan
- [ ] Columnas personalizadas funcionan
- [ ] Guardado de datos funciona

---

## 🎉 Testing Completado

Si todos los items están marcados, ¡estás listo para hacer merge!

```bash
git checkout main
git merge refactor-shortcodes-organization
git push origin main
```

---

## 🐛 Si Encuentras un Bug

1. Anota qué item falló
2. Describe el comportamiento esperado vs el actual
3. Verifica el log de errores de PHP
4. Verifica la consola de JavaScript
5. Repórtalo para corregirlo

---

**¡Buena suerte con el testing!** 🚀
