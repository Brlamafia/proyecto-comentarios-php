# Voces — Portal de comentarios

Aplicación grupal desarrollada con HTML, CSS, JavaScript, Bootstrap 5 y PHP 8. Permite recibir, almacenar y publicar comentarios en un archivo JSON local o en PostgreSQL durante la publicación.

## Ejecutar localmente

```bash
php -d display_errors=0 -S localhost:8000 -t assets index.php
```

Abrir `http://localhost:8000`.

## Publicación

El repositorio incluye configuración para CodeSandbox y Replit. En CodeSandbox, la tarea `Servidor PHP` inicia automáticamente el proyecto en el puerto 8000. En Replit se utiliza el puerto 3000. El despliegue requiere que Replit configure DATABASE_URL con la base PostgreSQL de producción. Antes de publicar por primera vez, conserva el JSON actual del proyecto para que sus comentarios se importen al inicializar la base.

## División del equipo

### Bryant — Diseño y frontend

- Identidad visual y plantilla responsive.
- Estructura HTML y componentes Bootstrap.
- Interacciones JavaScript, contador y validación visual.
- Revisión de accesibilidad y experiencia móvil.

### Stanley — Backend, publicación y pruebas

- Recepción y validación de datos en PHP.
- Persistencia segura de comentarios en el servidor.
- Pruebas funcionales y manejo de errores.
- Publicación remota y verificación del enlace final.

La lista detallada de entregables de Stanley está disponible en [docs/TAREAS_STANLEY.md](docs/TAREAS_STANLEY.md).

## Prueba grupal obligatoria

Cuando el enlace remoto esté publicado, Bryant y Stanley deben entrar personalmente y dejar un comentario. La entrega debe incluir el enlace remoto y los nombres de ambos participantes.

