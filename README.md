# Operación Sombra Nocturna — Laboratorio CTF Dockerizado (15 banderas)

Universidad de Cundinamarca, extensión Chía — Hacking Ético
Autores: Sergio Alexander Prieto Castiblanco, Laura Bautista

## Uso

```
docker-compose up --build -d
docker ps        # confirma que ctf_sombra_v2 está "Up"
```

Servicios expuestos:
- **8082/tcp** — portal web vulnerable (Apache + PHP + MariaDB)
- **21/tcp** + **21100-21110/tcp** — FTP (vsftpd, modo pasivo)

## Mapa de banderas

| # | Nombre | Categoría | Técnica |
|---|---|---|---|
| F01 | recon_passive | Reconocimiento | Header HTTP oculto, visible en el `.pcap` o con `curl -I` |
| F02 | recon_banner | Reconocimiento | Banner del servicio FTP (`nmap -sC` o `ftp <IP>`) |
| F03 | recon_hidden_path | Reconocimiento | `robots.txt` → `/admin_backup/` |
| F04 | sqli_bypass | Web/SQLi | Comentario HTML tras bypass de login (ver código fuente) |
| F05 | sqli_union_dump | Web/SQLi | UNION-based SQLi contra tabla `operaciones` |
| F06 | exif_leak | Web/SQLi | EXIF de `manifiesto_alpha.jpg` |
| F07 | upload_filter_bypass | Web/SQLi | Subir `.phtml`/`.php3`/`.php4`/`.php5`/`.pht` en vez de `.php` |
| F08 | rce_confirm | RCE | Leer `/var/www/.rce_confirmed` tras subir la webshell |
| F09 | user_flag | RCE | `/var/www/user.txt` vía reverse shell |
| F10 | config_leak | Post-explotación | `.env.bak` descargable → credenciales FTP → `/home/operador_ftp/config_leak_flag.txt` |
| F11 | cronjob_write | Post-explotación | `/opt/backup.sh` (777) corre cada minuto como `operador` → `/home/operador/flag_f11.txt` |
| F12 | wallet_env | Post-explotación | Variable de entorno `WALLET_KEY` (`env` o `/proc/<pid>/environ`) |
| F13 | suid_discovery | Escalada | `find / -perm -4000` → `/usr/bin/flagsuid <ruta_del_archivo>` |
| F14 | root_flag | Escalada | `/usr/bin/vim-suid` → shell root → `/root/root.txt` |
| F15 | case_closed | Cierre | `/root/.operacion_final.log` (base64) — requiere ser root |

Solo F09 y F14 están en `flags/` como archivos "clásicos"; el resto vive directamente en el
servicio/archivo/variable donde se explota, tal como se describe en la tabla.

## Notas

- `pcap/trafico_interceptado.pcap` ya está incluido y contiene la bandera F01 en un header
  HTTP de la respuesta a `GET /login.php` (visible con *Follow → HTTP Stream* en Wireshark).
- El filtro de subida de archivos (F07) bloquea `.php` exacto pero Apache también interpreta
  `.phtml .php3 .php4 .php5 .pht` como PHP — es la vulnerabilidad a explotar.
- vsftpd corre en modo pasivo con el rango `21100-21110`; si tu cliente FTP falla en modo
  pasivo, prueba forzando modo activo o ajusta el rango según tu red.
- Este laboratorio es intencionalmente vulnerable en múltiples frentes (SQLi, subida de
  archivos, cron inseguro, SUID, variables de entorno, archivos de backup expuestos).
  Ejecutarlo únicamente en una red aislada.
