#!/bin/bash
# Tarea de respaldo ejecutada cada minuto como el usuario "operador".
# VULNERABILIDAD: el script es escribible por www-data (chmod 777),
# asi que cualquier comando agregado aqui se ejecuta con los privilegios
# de "operador" en la siguiente corrida del cron.
echo "[$(date)] backup ok" >> /var/log/sombra_backup.log
