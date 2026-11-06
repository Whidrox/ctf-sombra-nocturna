# restore-lab.ps1 - restaura un snapshot previo del laboratorio
param(
    [Parameter(Mandatory=$true)]
    [string]$ArchivoBackup
)
$ErrorActionPreference = "Stop"

Write-Host "Deteniendo el laboratorio actual (si esta corriendo)..."
docker-compose down

Write-Host "Cargando la imagen desde $ArchivoBackup ..."
docker load -i $ArchivoBackup

Write-Host "Imagenes disponibles tras la carga:"
docker images | Select-String "sombra-nocturna-backup"

Write-Host ""
Write-Host "Para usar esta imagen como el laboratorio activo, edita docker-compose.yml:"
Write-Host "  reemplaza temporalmente 'build: .' por 'image: <nombre:tag de arriba>'"
Write-Host "y corre: docker-compose up -d"