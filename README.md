# daycopass
Password Vault Web Application written in PHP &amp; MySQL

## Funcionalidades y Caracteristicas

- Aplicacion Web basada en software libre (LAMP).
- Acceso seguro con protocolo HTTPS/TLS (Configuracion de Apache).
- Autenticacion LDAP hacia el Directorio Activo de Windows / OpenLDAP.
- Autenticacion IMAP hacia servidor de correo electronico (Soporta TLS).
- Autenticacion via token compartido con aplicaciones externas (e.g. Crawler).
- Uso de "One Time Token" para: autenticacion del desbloqueo de usuario enviando 
  token a traves de correo electronico, prevencion de Cross Site Request Forgery 
  (CSRF) manteniendo token en la sesion (PHP) y autenticacion de integracion con 
  aplicaciones externas (e.g. Guacamole). 
- Usuario y credencial de emergencia para acceso al sistema (admin).
- Imagen de seguridad (Captcha) para proteccion de formulario de autenticacion.
- Notificacion de Bloqeo de Mayusculas Activo en formulario de autenticacion.
- Cifrado de claves con el estandar AES de 256 bits.
- Semilla (Salt) de cifrado (unica) de 256 bits para claves.
- Verificacion antes y despues del cifrado de claves con firma SHA-1.
- Vector de inicializacion (IV) aleatorio por cada clave.
- Archivo de configuracion global especificados bajo el estandar INI de PHP.
- Control de acceso por usuario con nivel numerico (Similar a PasswordMax).
- Control de acceso a credenciales segun el nivel (unico) del usuario.
- Presentacion de credenciales validas e invalidas (historial).
- Busqueda por todos los campos de las credenciales (excepto la clave).
- Paginacion y tabulacion de resultados de consultas de credenciales (DataTable).
- Utilidad de generacion de claves aleatorias para el usuario final.
- Codigo estructurado sobre PHP e independiente de frameworks (MVC).
- Bitacora de auditoria de accesos y acciones dentro del sistema.
- Bitacora integrable con el registro del sistema operativo (Syslog).
- Integracion con Guacamole para sesiones remotas via HTML 5 (SSH, RDP, VNC y Telnet).
- Deteccion de puertos abiertos en servidores remotos (Integracion con Guacamole).
- Operacion en modo de solo lectura (Read Only) para replicas del sistema.
- Respaldo configurable de la base de datos en disco local (CronJob).
- Respaldos cifrados (SQL) con semilla maestra.

## Codigo de Terceros

### v2.0

+ JQuery v1.12.4 - https://jquery.com/
+ JQuery UI v1.12.1 - http://jqueryui.com/
+ DataTables v1.10.12 - https://datatables.net/
+ Chosen v1.6.3 - https://harvesthq.github.io/chosen/
+ Secure Image PHP Captcha v3.6.4 - https://www.phpcaptcha.org/
+ PHPMailer v5.2.16 - https://github.com/PHPMailer/PHPMailer

### v1.0

* Ubuntu Linux 14.04 LTS - https://www.ubuntu.com/
* Apache Web Server 2.4.7 (Paquete del SO)
* PHP v5.5.9 (Paquete del SO)
* MySQL v5.5.2 (Paquete del SO)
* JQuery v1.11.2 - https://jquery.com/
* JQuery UI v1.11.4 - http://jqueryui.com/

## Versiones

### Diciembre de 2017 - v2.0 ###

- Version mas reciente y estable.
- Imagen de seguridad (Captcha) para proteccion de formulario de autenticacion.
- Auto-desbloqueo de usuario a traves Token remitido a su correo electronico.
- Proteccion con Captcha de 4 digitos y Cross Site Request Forgery (CSRF) con
  Token de sesion en el formulario de inicio de sesion.
- Aviso de bloqueo de mayusculas en el formulario de inicio de sesion.
- Presionar ENTER ahora hace envio del formulario de inicio de sesion.
- Ayuda visual de registros de clave validos e invalidos (Busqueda).
- Paginacion de consultas con tablas dinamicas (JQuery/DataTables).
- Cambio de la plantillas visual (JQuery UI) en colores verde/blanco.
- Disponibilidad de modo de solo lectura para replicas del sistema.
- Envio de registros de la bitacora al Syslog del sistema operativo.
- Nuevo formato .ini del archivo de configuracion del sistema.
- Integracion para acceso remoto con Guacamole (Version Beta).
- Mejoras de la documentacion de instalacion y configuracion (+CentOS 6.x).
- Verificada compatibilidad con MySQL 5.7, Apache 2.4 y PHP-FPM externo.
- Respaldo y restauracion cifrada de respaldos de base de datos (SQL).
- Generacion de clave aleatoria nuevamente esta disponible para todo usuario.
- Eliminacion de respaldos en texto plano debido al riesgo de seguridad (CSV).
- Correcion de scripts de modificacion de permisos durante instalacion.
- Actualizacion de la documentacion de replicacion cifrada de MySQL.

### Septiembre de 2014 - v1.0

- Version inicial.
- Acceso seguro con protocolo HTTPS/TLS (Configuracion de Apache).
- Autenticacion LDAP hacia el Directorio Activo de Windows / OpenLDAP.
- Autenticacion IMAP hacia servidor de correo electronico (Soporta TLS).
- Autenticacion via token compartido con aplicaciones externas (e.g. Crawler).
- Usuario y credencial de emergencia para acceso al sistema (admin).
- Cifrado de claves con el estandar AES de 256 bits.
- Semilla (Salt) de cifrado (unica) de 256 bits para claves.
- Verificacion antes y despues del cifrado de claves con firma SHA-1.
- Vector de inicializacion (IV) aleatorio por cada clave.
- Control de acceso por usuario con nivel numerico (Similar a PasswordMax).
- Control de acceso a credenciales segun el nivel (unico) del usuario.
- Presentacion de credenciales validas e invalidas (historial).
- Busqueda por todos los campos de las credenciales (excepto la clave).
- Utilidad de generacion de claves aleatorias para el usuario final.
- Codigo estructurado sobre PHP e independiente de frameworks (MVC).
- Bitacora de auditoria de accesos y acciones dentro del sistema (Dir. IP/Usuario).
- Bitacora integrable con el registro del sistema operativo (Syslog).
- Respaldo configurable de la base de datos en disco local (CronJob).
- Importacion CSV de claves de PasswordMax.
- Exportacion cifrada (SQL) y descifrada (CSV).
