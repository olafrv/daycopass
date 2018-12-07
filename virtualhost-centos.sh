#!/bin/bash

# ESTE SCRIPT DE EJECUTARSE CON SUDO (ROOT)

if [ $(id -u -n) != "root" ] || [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ]
then
        echo "Ejecute sudo ./virtualhost.sh <usuario> <servidor> <puerto>"
        echo "Para mayor informacion vea el manual de instalacion."
        exit 1
fi

# VARIABLES GLOBALES
USUARIO=$1
DOMINIO=$2
PUERTO=$3

cat - > /opt/rh/httpd24/root/etc/httpd/conf.d/z-daycopass-${USUARIO}.conf <<END
<VirtualHost *:$PUERTO>
				ProxyPassMatch "^/(.*.php)$" "fcgi://127.0.0.1:9000/home/daycopass/daycopass/web"
        SSLEngine on
        ServerName https://$DOMINIO:$PUERTO
        UseCanonicalName On
        SSLCertificateFile   /home/$USUARIO/apache.crt
        SSLCertificateKeyFile /home/$USUARIO/apache.key
        ErrorLog logs/error_log
        CustomLog logs/access_log combined

        # Directorio principal
        DocumentRoot /home/$USUARIO/daycopass/web
        <Directory /home/$USUARIO/daycopass/web>
                Order Allow,Deny
                Allow from All
                Options FollowSymLinks
                AllowOverride None
                # Apache 2.4
                require all granted
        </Directory>

</VirtualHost>
END
service httpd24-httpd restart

