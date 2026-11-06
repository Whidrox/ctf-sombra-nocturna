FROM ubuntu:22.04
ENV DEBIAN_FRONTEND=noninteractive

# -----------------------------------------------------------------------
# 1. Instalación de servicios
#    apache2/php/mariadb -> portal web
#    vsftpd               -> servicio FTP para F02 (banner) y F10 (leak)
#    cron                 -> tarea periódica para F11
#    openssh-server        -> servicio SSH para F16 (credenciales filtradas)
# -----------------------------------------------------------------------
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    libapache2-mod-php \
    php-mysqli \
    mariadb-server \
    vsftpd \
    cron \
    openssh-server \
    netcat \
    exiftool \
    vim-tiny \
    sudo \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------------------
# 2. Apache: puerto no estándar 8082 (app real) + mod_headers (F01) +
#    ejecución de extensiones PHP alternativas (para que F07 sea real)
# -----------------------------------------------------------------------
RUN sed -i 's/Listen 80/Listen 8082/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8082>/' /etc/apache2/sites-available/000-default.conf && \
    a2enmod headers && \
    echo '<FilesMatch "\.(php|phtml|php3|php4|php5|pht)$">' > /etc/apache2/conf-available/php-alt-ext.conf && \
    echo '    SetHandler application/x-httpd-php' >> /etc/apache2/conf-available/php-alt-ext.conf && \
    echo '</FilesMatch>' >> /etc/apache2/conf-available/php-alt-ext.conf && \
    a2enconf php-alt-ext && \
    echo 'Header always set X-Internal-Note "CTF{f01_recon_passivo_pcap_2026}"' \
      >> /etc/apache2/sites-available/000-default.conf

# -----------------------------------------------------------------------
# 2b. Puertos decoy (80 y 8080): páginas señuelo sin ninguna bandera,
#     para reforzar la lección de "seguridad por oscuridad" del puerto
#     real (8082). Se suman como VirtualHosts nuevos, sin tocar el 8082.
# -----------------------------------------------------------------------
RUN mkdir -p /var/run/sshd && \
    echo "Listen 80" >> /etc/apache2/ports.conf && \
    echo "Listen 8080" >> /etc/apache2/ports.conf && \
    mkdir -p /var/www/decoy80 /var/www/decoy8080 && \
    echo '<html><body><h1>It works!</h1><p>Apache2 Ubuntu Default Page.</p></body></html>' > /var/www/decoy80/index.html && \
    echo '<html><body><h1>Mantenimiento programado</h1><p>El servicio estara disponible pronto.</p></body></html>' > /var/www/decoy8080/index.html && \
    printf '<VirtualHost *:80>\n    DocumentRoot /var/www/decoy80\n</VirtualHost>\n' > /etc/apache2/sites-available/decoy80.conf && \
    printf '<VirtualHost *:8080>\n    DocumentRoot /var/www/decoy8080\n</VirtualHost>\n' > /etc/apache2/sites-available/decoy8080.conf && \
    a2ensite decoy80 && a2ensite decoy8080

# -----------------------------------------------------------------------
# 3. Copiar la aplicación web y la base de datos
# -----------------------------------------------------------------------
COPY src/ /var/www/html/
RUN service mariadb start && \
    mysql -u root < /var/www/html/db.sql && \
    mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'rootpass'; FLUSH PRIVILEGES;"

# -----------------------------------------------------------------------
# 4. Metadatos EXIF ya vienen incrustados en las imágenes (src/uploads/),
#    se reafirman aquí por si el build las reescribe.
# -----------------------------------------------------------------------
RUN exiftool -Comment="PISTA_EXIF: El directorio de uploads permite ejecucion de scripts .php" \
    -Artist="Operador_Alpha_Chia" /var/www/html/uploads/evidencia_envio.jpg || true && \
    exiftool -Comment="CTF{f06_exif_manifiesto_alpha_2026}" \
    -Artist="Operador_Beta_Logistica" /var/www/html/uploads/manifiesto_alpha.jpg || true

# -----------------------------------------------------------------------
# 5. Permisos de carpeta de uploads y banderas "clásicas" de archivo
# -----------------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html/uploads && \
    chmod 777 /var/www/html/uploads && \
    echo "CTF{user_flag_sqli_exif_bypass_2026}" > /var/www/user.txt && \
    chmod 644 /var/www/user.txt && \
    echo "CTF{root_flag_cartel_takeover_2026}" > /root/root.txt && \
    chmod 600 /root/root.txt && \
    chmod 644 /var/www/html/.env.bak && \
    chmod -R 755 /var/www/html/admin_backup

# F08 - bandera de confirmación de RCE, legible por www-data una vez
# se tiene ejecución de comandos (antes de montar la reverse shell)
RUN echo "CTF{f08_rce_confirmado_2026}" > /var/www/.rce_confirmed && \
    chown www-data:www-data /var/www/.rce_confirmed && \
    chmod 644 /var/www/.rce_confirmed

# -----------------------------------------------------------------------
# 6. Usuario "operador" (cron, F11)
# -----------------------------------------------------------------------
RUN useradd -m -s /bin/bash operador && \
    echo "CTF{f11_cron_operador_2026}" > /home/operador/flag_f11.txt && \
    chown operador:operador /home/operador/flag_f11.txt && \
    chmod 600 /home/operador/flag_f11.txt

# CORRECCION encontrada en pruebas en vivo: vsftpd rechaza el login (530)
# si el shell del usuario no esta en /etc/shells, aunque la clave sea
# correcta. Hay que agregarlo ANTES de crear el usuario FTP.
RUN echo "/usr/sbin/nologin" >> /etc/shells

# Usuario FTP "operador_ftp" (F10)
RUN useradd -m -s /usr/sbin/nologin operador_ftp && \
    echo "operador_ftp:L0g1st1c4_2026!" | chpasswd && \
    echo "CTF{f10_env_bak_ftp_leak_2026}" > /home/operador_ftp/config_leak_flag.txt && \
    chown operador_ftp:operador_ftp /home/operador_ftp/config_leak_flag.txt && \
    chmod 644 /home/operador_ftp/config_leak_flag.txt

# Usuario SSH "tecnico_soporte" (F16) - credenciales filtradas via .env.bak
RUN useradd -m -s /bin/bash tecnico_soporte && \
    echo "tecnico_soporte:T3cn1c0_S0p0rt3_2026!" | chpasswd && \
    echo "CTF{f16_ssh_weak_creds_2026}" > /home/tecnico_soporte/flag_f16.txt && \
    chown tecnico_soporte:tecnico_soporte /home/tecnico_soporte/flag_f16.txt && \
    chmod 600 /home/tecnico_soporte/flag_f16.txt

# -----------------------------------------------------------------------
# 7. vsftpd: banner con F02, login con las credenciales filtradas en
#    .env.bak (permite llegar al archivo con F10 dentro del home)
# -----------------------------------------------------------------------
RUN { \
      echo 'listen=YES'; \
      echo 'listen_ipv6=NO'; \
      echo 'anonymous_enable=NO'; \
      echo 'local_enable=YES'; \
      echo 'write_enable=NO'; \
      echo 'chroot_local_user=YES'; \
      echo 'allow_writeable_chroot=YES'; \
      echo 'pasv_enable=YES'; \
      echo 'pasv_min_port=21100'; \
      echo 'pasv_max_port=21110'; \
      echo 'ftpd_banner=Sombra Nocturna FTP - acceso restringido - CTF{f02_banner_ftp_c2_2026}'; \
      echo 'userlist_enable=NO'; \
    } > /etc/vsftpd.conf

# -----------------------------------------------------------------------
# 8. Cron: tarea cada minuto como "operador" (F11)
# -----------------------------------------------------------------------
COPY src-extra/backup.sh /opt/backup.sh
RUN chmod 777 /opt/backup.sh && \
    touch /var/log/sombra_backup.log && chmod 666 /var/log/sombra_backup.log
COPY src-extra/sombra-cron /etc/cron.d/sombra-cron
RUN chmod 644 /etc/cron.d/sombra-cron

# -----------------------------------------------------------------------
# 9. Variable de entorno con "clave de wallet" (F12) - visible vía
#    `env` o /proc/<pid>/environ tras lograr RCE
# -----------------------------------------------------------------------
ENV WALLET_KEY="CTF{f12_wallet_env_leak_2026}"

# -----------------------------------------------------------------------
# 10. Binario SUID señuelo/puzzle (F13): copia de /bin/cat con bit u+s.
#     Al correr `flagsuid /root/f13_flag.txt` hereda el EUID root y
#     puede leer un archivo que normalmente solo root puede ver.
# -----------------------------------------------------------------------
RUN echo "CTF{f13_suid_flagsuid_2026}" > /root/f13_flag.txt && chmod 600 /root/f13_flag.txt && \
    cp /bin/cat /usr/bin/flagsuid && chmod u+s /usr/bin/flagsuid

# Vulnerabilidad de Escalada de Privilegios "real" (root shell completo)
RUN cp /usr/bin/vim.tiny /usr/bin/vim-suid && \
    chmod u+s /usr/bin/vim-suid

# -----------------------------------------------------------------------
# 11. Registro final cifrado en base64 (F15), solo legible por root
# -----------------------------------------------------------------------
RUN echo "T1BFUkFDSU9OIFNPTUJSQSBOT0NUVVJOQSAtIFJFR0lTVFJPIEZJTkFMClByb3hpbW8gZW52aW86IGNvbnRlbmVkb3IgTVNDVS03NzQyMDEsIHB1ZXJ0byBCdWVuYXZlbnR1cmEsIDAzOjAwIEFNLgpDb25maXJtYWNpb24gZGUgcnV0YTogQ1RGe2YxNV9jYXNvX2NlcnJhZG9fZW52aW9fZmluYWxfMjAyNn0K" \
      > /root/.operacion_final.log && chmod 600 /root/.operacion_final.log

EXPOSE 8082 80 8080 22 21 21100-21110

# -----------------------------------------------------------------------
# Arranque: MariaDB + cron + vsftpd + sshd + Apache en primer plano
# -----------------------------------------------------------------------
CMD service mariadb start && \
    service cron start && \
    service vsftpd start && \
    service ssh start && \
    apachectl -D FOREGROUND