#!/bin/bash

if [ ! -d ~/daycopass_backup ]
then 
	mkdir ~/daycopass_backup 2>/dev/null; 
fi

if [ -f /etc/scl.sh ]
then 
	source /etc/scl.sh; 
fi

sudo chown -R $(id -u -n):root ~/daycopass_backup \
&& chmod 700 ~/daycopass_backup \
&& cd ~/daycopass \
&& php dbcheckpw.php \
&& php dbdumpsec.php ~/daycopass_backup \
&& tar cfz ~/daycopass_backup/$(date "+%Y%m%d_%H%M%S")_daycopass_php.tar.gz -C ~ daycopass daycopass.ini \
&& find ~/daycopass_backup -type f -mtime +2 -exec rm {} \;

