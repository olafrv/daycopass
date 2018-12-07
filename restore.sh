#!/bin/sh

ls -1 ~/daycopass_backup/*.sql.enc | less

echo "Archivo (Copy&Paste)?"
read archivo

if [ -f /etc/scl.sh ]
then
  source /etc/scl.sh;
fi

if [ -f "$archivo" ]
then
	cd ~/daycopass && php dbrestoresec.php $archivo
else
	echo "El archivo '$archivo' es invalido."
fi
