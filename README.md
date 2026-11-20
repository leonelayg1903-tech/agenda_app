Agenda Personal de Contactos (PHP \& MySQL)



Aplicación web segura para la gestión de contactos personales con control de sesiones y autenticación cifrada.



1. Descripción del proyecto



La aplicación permite registrar usuarios con contraseñas cifradas, iniciar sesión de forma segura y administrar una lista de contactos mediante operaciones de creación, lectura, actualización y eliminación. El sistema incluye validaciones de formularios en el servidor, control de sesiones activas en las páginas protegidas y aislamiento de datos por usuario para impedir el acceso no autorizado a registros ajenos.



2. URL pública



* Aplicación desplegada en línea: http://agendaLeo.xo.je



3. Capturas de pantalla  



!\[Inicio de sesión](capturas/login.png)



!\[Panel de contactos](capturas/dashboard.png)



!\[Editar contacto](capturas/editar.png)





4. Tecnologías utilizadas



* PHP con PDO para la conexión segura a la base de datos.
* MySQL y phpMyAdmin para la gestión de las tablas de usuarios, categorías y contactos.
* HTML5 y CSS3 para la interfaz de usuario.
* XAMPP como entorno de desarrollo local.
* InfinityFree para el despliegue de la aplicación en línea.



5. Requisitos para su ejecución



* Servidor local XAMPP con Apache y MySQL activos.
* PHP con soporte para PDO.
* Navegador web actualizado.



6. Pasos para instalar la aplicación localmente



1. Copiar la carpeta del proyecto en el directorio raíz del servidor local (ejemplo: C:\\xampp\\htdocs\\agenda\_app).
2. Abrir phpMyAdmin, crear una base de datos vacía llamada agenda\_db e importar el archivo SQL de respaldo ubicado en el proyecto (agenda\_db.sql).
3. Revisar conexion.php y configurar los parámetros de conexión locales sin dejar contraseñas reales expuestas en el repositorio.
4. Abrir el navegador e ingresar a la ruta: http://localhost:8080/agenda\_app/registro.php (o http://localhost/agenda\_app/registro.php según el puerto de Apache).



1. Mejoras respecto a la versión anterior



* Categorías de contactos: Se agregó la tabla categorias (Familia, Amigos, Trabajo, Universidad, Otro) relacionada con contactos para clasificar cada registro y validar la categoría seleccionada.
* Contactos favoritos: Se incorporó el campo favorito para marcar contactos destacados al crear o editar y filtrar rápidamente la vista de favoritos en el panel.
* Actualización de contactos: Se ajustó el flujo de edición en editar.php para redirigir tras ejecutar la consulta preparada sin depender de rowCount().
* Seguridad en conexión y despliegue: Se configuró un mensaje genérico de error ante fallos de conexión, se mantuvo la plantilla de conexion.php sin credenciales reales para el repositorio y se publicó el sistema en http://agendaLeo.xo.je.

