# 🏔️ San Pedro de Cajas - Página Web

Una página web moderna, limpia y completamente responsiva dedicada a **San Pedro de Cajas**, la tierra de artesanos en la provincia de Tarma, Junín, Perú.

## 🎨 Características

### Diseño Moderno
- **HTML5** semántico y accesible
- **CSS3** con animaciones y gradientes
- **Responsive** para todos los dispositivos (desktop, tablet, móvil)
- Colores inspirados en la cultura andina (tonos tierra, azules y acentos vivos)

### Secciones Incluidas
1. **Inicio (Hero)** - Presentación principal con Radio San Pedro en vivo
2. **Nosotros** - Historia, tradición textil y datos del distrito
3. **Turismo** - Qué ver y hacer, cómo llegar y recomendaciones
4. **Hospedajes** - Alojamientos y turismo vivencial
5. **Galería** - Espacio para mostrar artesanías
6. **Paisajes** - Vistas y naturaleza de la región
7. **Nuestra Gente** - Historias de la comunidad
8. **Contacto** - Formulario de contacto e información
9. **Publicidad** - Dos espacios para Google AdSense (superior e inferior) y tres espacios privados (300×250) para patrocinadores

### Funcionalidades
✅ Menú de navegación responsivo (hamburguesa en móvil, solo CSS, sin JavaScript)  
✅ Reproductor de audio en vivo integrado  
✅ Formulario de contacto con validación nativa HTML5  
✅ Scroll suave entre secciones  
✅ Animaciones de carga de elementos  
✅ Transiciones y hover effects  
✅ Optimizado para SEO  

## 📁 Estructura de Archivos

```
san-pedro-de-cajas/
├── index.html          # Archivo principal HTML5
├── styles.css          # Estilos CSS3 responsivos
└── README.md           # Este archivo
```

## 🎨 Paleta de Colores (Andina)

| Color | Código | Uso |
|-------|--------|-----|
| Azul de puna | `#1B4D5C` / `#0F2F3A` | Header, hero, pie de página |
| Tierra | `#8B4A2B` | Acentos, etiquetas de sección |
| Rojo cochinilla | `#B7322C` | Botón principal, radio en vivo |
| Dorado de ichu | `#D9A441` | Detalles decorativos, cifras |
| Verde valle | `#4F7A3A` | Paisajes, franja tejida |
| Crema de lana | `#F6F1E7` / `#FFFDF8` | Fondos |

## 📱 Responsividad

La página está optimizada para:
- **Desktop** (1200px+)
- **Tablet** (768px - 1199px)
- **Móvil** (480px - 767px)
- **Móvil pequeño** (menos de 480px)

## 🔊 Reproductor de Audio

- URL del stream: `http://stream.zenolive.com/g9gc78mmn3quv`
- Reproductor HTML5 nativo
- Controles completos (play, pause, volumen)
- Responsive y adaptable

## 💬 Formulario de Contacto

Incluye:
- Campos: Nombre, Email, Mensaje
- Validación de email
- Validación de campos requeridos
- Mensaje de confirmación al enviar

## 🚀 Cómo Usar

1. **Descargar o clonar el repositorio**
   ```bash
   git clone https://github.com/kushirflp8/san-pedro-de-cajas.git
   ```

2. **Abrir en navegador**
   - Simplemente abre el archivo `index.html` en tu navegador favorito

3. **Personalizar contenido**
   - Edita los textos en `index.html`
   - Modifica los colores en las variables CSS en `styles.css`
   - Añade tus imágenes reemplazando los placeholders

4. **Integrar publicidad**
   - Pega el código de Google AdSense dentro de los bloques `.ad__slot` con `data-ad="adsense-top"` y `data-ad="adsense-bottom"`
   - Coloca banners de patrocinadores en los bloques `data-ad="private-1..3"` (300×250)

## 📊 Secciones de Publicidad

- `data-ad="adsense-top"`: leaderboard 728×90 / responsive debajo del hero
- `data-ad="adsense-bottom"`: bloque responsive antes del pie de página
- `data-ad="private-1"`, `private-2`, `private-3`: anuncios privados de patrocinadores locales

## 🔧 Personalización

### Cambiar Colores
Edita las variables CSS en `styles.css`:
```css
:root {
    --tierra: #8B4A2B;
    --azul: #1B4D5C;
    --cochinilla: #B7322C;
    --dorado: #D9A441;
    /* ... más colores */
}
```

### Cambiar Textos
Modifica el contenido en `index.html`:
```html
<h1>San Pedro de Cajas</h1>
<p>Tu contenido aquí</p>
```

### Añadir Imágenes
Reemplaza el fondo de cada `gallery__item` con una etiqueta `<img>`:
```html
<figure class="gallery__item">
    <img src="tu-imagen.jpg" alt="Descripción">
    <figcaption>Título</figcaption>
</figure>
```

## 🌐 Navegadores Compatibles

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Navegadores móviles modernos

## 📲 Características Móviles

- Menú hamburguesa automático
- Touch-friendly
- Optimizado para velocidad de carga
- Imágenes responsive
- Texto legible sin zoom

## 🔐 Seguridad

- Sin dependencias externas peligrosas
- Código limpio y validado
- Formulario con validación básica
- Stream de audio de fuente confiable

## 📝 SEO

- Etiquetas meta correctas
- Estructura HTML5 semántica
- Títulos y descripciones optimizadas
- Accesibilidad mejorada

## 💡 Sugerencias de Mejora

Para versiones futuras:
- [ ] Galería de imágenes con lightbox
- [ ] Integración con redes sociales
- [ ] Blog o sección de noticias
- [ ] Sistema de comentarios
- [ ] Versión en inglés
- [ ] PWA (Progressive Web App)
- [ ] Backend para formulario de contacto

## 👥 Créditos

- **Diseño**: Inspirado en la cultura andina
- **Ubicación**: San Pedro de Cajas, Tarma, Junín, Perú
- **Creado**: 2026

## 📄 Licencia

Libre para uso y distribución.

---

**Hecho con ❤️ para San Pedro de Cajas**

Para más información, contacta a:
- 📧 Email: contacto@sanpedrodecajas.com
- 📍 Ubicación: Tarma, Junín, Perú