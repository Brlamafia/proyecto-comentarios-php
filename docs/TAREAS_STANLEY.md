# Responsabilidades de Stanley

## Rol principal

Stanley es responsable del backend en PHP, la persistencia de los comentarios, las pruebas funcionales y la publicación remota del proyecto **Voces**.

## Backend y almacenamiento

- Revisar `src/CommentRepository.php` y comprobar que la lectura y escritura utilicen bloqueo de archivos.
- Verificar que `data/comments.json` conserve los comentarios después de recargar la página.
- Mantener la recepción de datos mediante `POST` y el patrón Post/Redirect/Get.
- Revisar las validaciones del servidor para nombre, tipo de participante y comentario.
- Comprobar las protecciones CSRF, XSS y el campo antispam.
- Confirmar que los errores de escritura se muestren sin exponer información interna del servidor.

## Integración con el frontend

- Confirmar que los comentarios nuevos aparezcan primero.
- Verificar que nombres, mensajes, fechas y roles se muestren correctamente.
- Probar que los datos inválidos conserven el formulario y muestren mensajes claros.
- Coordinar con Bryant cualquier cambio que afecte los campos o la presentación.

## Pruebas obligatorias

1. Enviar un comentario válido y confirmar que se publica.
2. Recargar la página y confirmar que el comentario permanece.
3. Intentar enviar el formulario vacío.
4. Probar un comentario de menos de 10 caracteres y otro mayor de 500.
5. Escribir etiquetas HTML y confirmar que se muestran como texto, sin ejecutarse.
6. Probar el buscador de comentarios.
7. Revisar el portal en computadora y teléfono.
8. Abrir el enlace remoto desde una ventana privada o un segundo dispositivo.

## Replit y publicación

- Revisar que el comando de ejecución sea `php -S 0.0.0.0:3000`.
- Confirmar que la vista previa abra sin errores.
- Publicar el proyecto con acceso público.
- Verificar el dominio `.replit.app` después de publicar.
- Realizar una prueba completa directamente en el enlace público.
- Entregar a Bryant el enlace definitivo y una captura de la prueba.

## Evidencia personal requerida

Stanley debe entrar personalmente al sitio publicado y dejar un comentario con su nombre. Bryant también debe publicar el suyo. Esta evidencia es obligatoria porque la asignación exige que cada integrante pruebe el sistema remoto.

## Flujo de Git

```bash
git clone https://github.com/Brlamafia/proyecto-comentarios-php.git
cd proyecto-comentarios-php
git checkout feature/backend-stanley
git pull origin feature/backend-stanley
```

Para entregar cambios:

```bash
git add .
git commit -m "Revisar backend y pruebas de publicación"
git push origin feature/backend-stanley
```

Después debe abrir un Pull Request desde `feature/backend-stanley` hacia `main`.

## Criterios para considerar su parte terminada

- Los comentarios se reciben, validan, almacenan y muestran correctamente.
- No se ejecuta HTML o JavaScript escrito por usuarios.
- El enlace remoto funciona sin iniciar sesión.
- Su comentario personal permanece visible después de recargar.
- La rama de Stanley está subida y lista para revisión.

