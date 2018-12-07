#!/bin/bash

udft=$(id -u -n)
gdft=$udft

if [ $(id -u -n) != "root" ]
then
				gwww="www-data"
        grep -q www-data /etc/group
        if [ $? -ne 0 ]; then gwww="apache"; fi
        cd
        echo ~
        sudo chown -R $udft:$gdft ~/daycopass
        sudo chown $udft:$gwww ~/apache.* ~/daycopass.ini
        sudo chown $udft:$gwww ~/daycopass/aaa.php
        sudo chown $udft:$gwww ~/daycopass/config.php
        sudo chown $udft:$gwww ~/daycopass/libcommon.php
        sudo chown $udft:$gwww ~/daycopass/sslenforce.php
        sudo chown -R $udft:$gwww ~/daycopass/{web,lib,guacamole}
        find ~/daycopass -type d -exec chmod 750 {} \;
        find ~/daycopass -type f -exec chmod 640 {} \;
        find ~/daycopass -type f -name '*.sh' -exec chmod 750 {} \;
        chmod 755 ~
        chmod 755 ~/daycopass
else
        echo "Solo debe ejecutarse con el usuario '$udft'."
fi
