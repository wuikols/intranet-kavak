# 🚀 Kavak OS - Staff Intranet Portal

Kavak OS es la plataforma interna definitiva diseñada para potenciar la productividad y la colaboración dentro de **Kavak**. Basada en una arquitectura PHP MVC sólida, esta intranet incorpora una interfaz de usuario completamente renovada bajo el modelo **Bento Grid** y una fuerte identidad de marca corporativa (Azul, Blanco, Acentos Dorados).

## 🛠️ Stack Tecnológico
- **Backend:** PHP 8.x (Arquitectura MVC limpia).
- **Base de Datos:** MySQL / MariaDB (Optimizado con PDO y borrado en cascada para mantener la integridad relacional de foros y wiki).
- **Frontend:** HTML5, CSS3 (Modern Variables y Bento Grid Layout).
- **Librerías:** FullCalendar (Agenda), Quill.js (Editor Texto IA), DOMPurify (Sanitización XSS y Seguridad Vistas).

## 📂 Estructura del Proyecto
- `/models`: Lógica de datos, acceso a BD, y orquestación de relaciones (Eliminación en cascada segura).
- `/views`: Plantillas PHP utilizando el esquema visual Bento Grid, responsivo y dinámico (modos claro/oscuro integrados).
- `/controllers`: Archivos de ruteo basados en acciones URL.
- `/assets/css/style.css`: Estilos maestros que dictan toda la identidad de **Kavak OS**.
- `/config`: Configuración local y variables de entorno.

## 🚀 Instalación y Despliegue Local
1. Clona o descarga el repositorio en tu entorno local (XAMPP / WAMP) dentro de la carpeta `htdocs` o `www`. 
   > Si deseas probar sin XAMPP, ubícate en la raíz del proyecto y corre: `php -S localhost:8080` y accede desde tu navegador web.
2. Configura las variables de tu base de datos en `config/database.php`.
3. Importa el dump inicial de la base de datos (con la estructura de usuarios, noticias, wiki y foro).
4. Accede al sistema. Si la base de datos requiere usuarios de demostración, créalos o utiliza scripts en tu manejador SQL.

## 🎨 Aspectos Claves del UI/UX
1. **Dashboard Bento Grid**: Diseño en bloques, noticias a 3 columnas, banner de bienvenida dinámico (css animado).
2. **Kavak Digital ID**: Al hacer click en el perfil, aparece la tarjeta corporativa diseñada como una placa identificadora real.
3. **Scroll Independiente en Wiki/Foro**: Vistas unificadas con dos paneles lado a lado donde el menú (lateral) y el contenido tienen su propio scroll, evitando que la página entera crezca innecesariamente.
4. **Modo Oscuro**: Implementado de fábrica en base a variables CSS puras (`dark-mode` toggle state).
5. **Universal Search (CTRL+K)**: Funcionalidad unificada en topbar para buscar usuarios, wiki o foros velozmente.

---
Desarrollado con ❤️ para el equipo de Kavak.