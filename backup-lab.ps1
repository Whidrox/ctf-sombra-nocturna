# backup-lab.ps1 - snapshot del laboratorio Sombra Nocturna
$ErrorActionPreference = "Stop"
$fecha = Get-Date -Format "yyyyMMdd-HHmmss"
$imagen = "sombra-nocturna-backup:$fecha"
$archivo = "sombra-nocturna-backup-$fecha.tar"

Write-Host "Congelando el estado actual del contenedor ctf_sombra_v2..."
docker commit ctf_sombra_v2 $imagen

Write-Host "Exportando la imagen a $archivo ..."
docker save -o $archivo $imagen

Write-Host "Listo. Backup guardado en $archivo (imagen: $imagen)"