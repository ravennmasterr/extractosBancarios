###### Pasos para instalar OpenSSH Server en Windows 10/11
# 1️⃣ Verificar si ya está disponible, abre PowerShell como administrador y ejecuta:

Get-WindowsCapability -Online | Where-Object Name -like 'OpenSSH.Server*'

# Si el estado es NotPresent, significa que no está instalado.
# Si el estado es Installed, entonces el problema es otro (servicio deshabilitado).

# 2️⃣ Instalar OpenSSH Server, ejecuta en PowerShell como administrador:

Add-WindowsCapability -Online -Name OpenSSH.Server~~~~0.0.1.0

# 3️⃣ Iniciar y habilitar el servicio
# Iniciar el servicio
Start-Service sshd

# Configurarlo para que arranque automáticamente
Set-Service -Name sshd -StartupType 'Automatic'

# 4️⃣ Abrir el puerto 22 en el firewall
New-NetFirewallRule -Name sshd -DisplayName 'OpenSSH Server (sshd)' -Enabled True -Direction Inbound -Protocol TCP -LocalPort 22 -Action Allow

# 5️⃣ Verificar que esté corriendo
Get-Service sshd


###### Para crear un usuario de Windows, abre PowerShell como administrador:

# 1️⃣ Crear usuario local (sin permisos de administrador)
net user extractosDev "3xtr@ct0" /add

# 2️⃣ Asegura que la cuenta esta activa para permitir la conexion
net user extractosDev /active:yes

# 3️⃣ Dar control total al usuario
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4" /grant extractosDev:(OI)(CI)F /T

# Ejecutando el comando icacls en PowerShell interpreta (OI)(CI)F como operadores, no como parte del comando. En CMD clásico, el comando funciona tal cual, pero en PowerShell necesitas escapar los paréntesis o poner el comando entre comillas para que PowerShell lo ejecute literalmente.
icacls "C:\laragon\www\extractosDevelop\storage\files\encryptedFiles" /grant 'extractosDev:(OI)(CI)F' /T

# icacls es la utilidad de Windows para otorgar, revocar o copiar permisos. (ACLs) de archivos y carpetas.
# /grant extractosDev:(OI)(CI)F
# Esta parte es la más importante, vamos por partes:
# /grant → indica que vas a otorgar permisos.
# /remove → indica que vas a revocar permisos.
# extractosDev → nombre del usuario o grupo al que se le otorgarán los permisos.

## 1️⃣ Permisos básicos (códigos)
## Código	Significado
## F	Full control → Control total (lectura, escritura, borrar, cambiar permisos, tomar posesión)
## M	Modify → Modificar (lectura, escritura, borrar, ejecutar; no cambia permisos)
## RX	Read & Execute → Leer y ejecutar archivos, navegar carpetas
## R	Read → Solo lectura
## W	Write → Solo escritura (crear archivos/carpetas)

## 2️⃣ Herencia y alcance
## Código	Significado
## (OI)	    Object inherit → hereda a archivos dentro de la carpeta
## (CI)	    Container inherit → hereda a subcarpetas
## (IO)	    Inherit only → el permiso solo se hereda, no se aplica a la carpeta actual

## Ejemplos combinados:
## (OI)(CI)F → control total para archivos y carpetas dentro de esta carpeta (heredado)
## (CI)(IO)M → modificar solo subcarpetas, sin afectar la carpeta principal
## (OI)R → solo lectura para todos los archivos dentro

## 3️⃣ Modificadores importantes
## Modificador	Significado
## /grant	    Otorga permisos
## /grant:r	    Reemplaza permisos existentes del usuario especificado
## /remove	    Quita todos los permisos del usuario especificado
## /T	        Aplica de forma recursiva a todos los subdirectorios y archivos


# Para ver los permisos actuales, solo ejecuta:
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4"

# Para revocar los permisos actuales, solo ejecuta:
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4" /remove extractosDev /T

# Si quieres cambiar permisos sin eliminar todo, ejemplo: quitar control total y dar solo lectura y ejecución
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4" /grant 'extractosDev:(OI)(CI)RX' /T

# /grant otorgar permisos adicionales a un usuario
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4" /grant 'extractosDev:M' /T

# /grant:r para reemplazar permisos a un usuario
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4" /grant 'extractosDev:M' /T


# Dentro del directorio raíz del chroot crea una “carpeta virtual” /H2H que apunta a la ubicación externa. (No funciona en powershell)  NOTA. Esta opcion se maneja con el comando artisan storage:link
mklink /D C:\laragon\www\extractosDevelop\storage\files\DS4\H2H "C:\laragon\www\extractosDevelop\storage\files\filesToUpload"

# /D → directorio.

# 4️⃣ Revocar permisos de otros usuarios (opcional)
icacls "C:\laragon\www\extractosDevelop\storage\files\DS4" /remove "Users" /T


###### Configurar OpenSSH para limitar al usuario
# 1️⃣ Edita el archivo sshd_config (normalmente en C:\ProgramData\ssh\sshd_config) y agrega al final:

Match User extractosDev
    ChrootDirectory C:\laragon\www\extractosDevelop\storage\files\DS4
    ForceCommand internal-sftp
    AllowTcpForwarding no
    X11Forwarding no

# Tip: Siempre verifica los usuarios creados en Windows con:

net user
    
# 4️⃣ Reiniciar el servicio SSH
Restart-Service sshd

# 5️⃣ Probar conexión desde cualquier cliente SFTP (FileZilla, WinSCP, o CMD/PowerShell):
Host: 10.20.101.115
Usuario: extractosDev
Contraseña: 3xtr@ct0
Puerto: 22
