# Galería de Imágenes - Despliegue en AWS

Este proyecto es una aplicación PHP con Bootstrap que permite:
- Ver un carrusel público de imágenes almacenadas en S3
- Filtrar imágenes por categorías
- Panel de administración protegido para subir/eliminar imágenes

---

## Tabla de Contenidos
1. [Requisitos Previos](#requisitos-previos)
2. [Configuración de AWS](#configuración-de-aws)
3. [Configuración del Proyecto](#configuración-del-proyecto)
4. [Despliegue en EC2](#despliegue-en-ec2)
5. [Verificación y Mantenimiento](#verificación-y-mantenimiento)

---

## Requisitos Previos

Antes de comenzar, necesitas una cuenta de AWS con los siguientes servicios:

- **RDS**: Base de datos MariaDB o MySQL
- **S3**: Almacenamiento para imágenes
- **EC2**: Servidor para ejecutar PHP
- **IAM**: Para credenciales de acceso

---

## Configuración de AWS

### Paso 1: Crear Base de Datos RDS

1. Ve a **RDS** > **Create database**
2. Selecciona **MariaDB** o **MySQL**
3. Configura:
   - **DB Instance Identifier**: `galeria-db`
   - **Master username**: `admin`
   - **Master password**: `ContraseñaSegura123!`
   - **DB Instance Class**: `db.t3.micro` (gratis)
   - **Allocated Storage**: 20 GB
4. En **Additional settings**, marca "Enable automated backups"
5. Crea la base de datos y espera a que esté disponible
6. Copia el **Endpoint** (ej: `galeria-db.xxxx.us-east-1.rds.amazonaws.com`)

### Paso 2: Crear Bucket S3

1. Ve a **S3** > **Create bucket**
2. Configura:
   - **Bucket name**: `galeria-imagenes-[tu-nombre]`
   - **AWS Region**: `us-east-1`
   - **Object Ownership**: `ACLs enabled`
   - **Block Public Access**: Desmarca todo (para que las imágenes sean públicas)
3. Crea el bucket
4. Ve a **Permissions** > **Bucket Policy** y pega:
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::galeria-imagenes-TU-NOMBRE/*"
        }
    ]
}
```
(Reemplaza `galeria-imagenes-TU-NOMBRE` con tu nombre de bucket)

### Paso 3: Crear Usuario IAM

1. Ve a **IAM** > **Users** > **Add user**
2. Configura:
   - **User name**: `galeria-admin`
   - **Access type**: `Programmatic access`
3. En **Permissions**, selecciona "Attach existing policies directly":
   - Busca y marca `AmazonS3FullAccess`
4. Crea el usuario
5. **IMPORTANTE**: Copia:
   - **Access Key ID**: `AKIA...`
   - **Secret Access Key**: `xxxxx...`

---

## Configuración del Proyecto

### Paso 1: Configurar archivo `.env`

Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

Edita `.env` con tus datos:

```env
# Base de datos RDS (del Paso 1)
DB_HOST=galeria-db.xxxx.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_NAME=galeria
DB_USER=admin
DB_PASSWORD=ContraseñaSegura123!

# AWS S3 (del Paso 2 y 3)
AWS_REGION=us-east-1
AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
AWS_BUCKET=galeria-imagenes-tu-nombre

# Sesión (genera una clave aleatoria)
SESSION_SECRET=a8f5f167f44f4964e6c998dee827110c

# Usuario Admin
ADMIN_USER=admin
ADMIN_PASSWORD=MiNuevaPassword123
```

### Paso 2: Instalar dependencias localmente (opcional)

```bash
composer install
```

### Paso 3: Probar localmente

```bash
# Iniciar servidor PHP
php -S localhost:8000

# Crear base de datos local (si tienes MySQL instalado)
mysql -u root -e "CREATE DATABASE galeria;"

# Abrir en navegador
# http://localhost:8000/install.php
# http://localhost:8000/index.php
```

---

## Despliegue en EC2

### Paso 1: Lanzar instancia EC2

1. Ve a **EC2** > **Launch Instance**
2. Configura:
   - **Name**: `galeria-server`
   - **AMI**: `Amazon Linux 2` o `Ubuntu Server 22.04`
   - **Instance type**: `t3.micro` (gratis)
   - **Key pair**: Crea una nueva o usa existente
3. En **Security Groups**, añade:
   - **HTTP**: Puerto 80 (tráfico web)
   - **HTTPS**: Puerto 443 (opcional, si usas SSL)
   - **SSH**: Puerto 22 (solo tu IP)
4. Lanza la instancia
5. Copia la **IPv4 Public Address** (ej: `54.123.45.67`)

### Paso 2: Conectarse a EC2

```bash
ssh -i tu-key.pem ec2-user@54.123.45.67
```

### Paso 3: Instalar PHP y dependencias

```bash
# En Amazon Linux 2:
sudo yum update -y
sudo yum install -y php php-mysqlnd php-xml php-mbstring composer

# En Ubuntu:
sudo apt update
sudo apt install -y php php-mysql php-xml php-mbstring composer
```

### Paso 4: Configurar Apache/Nginx

**Apache (Amazon Linux):**
```bash
sudo yum install -y httpd
sudo systemctl start httpd
sudo systemctl enable httpd
sudo chown -R ec2-user:ec2-user /var/www/html
```

**O Nginx + PHP-FPM (recomendado):**
```bash
sudo yum install -y nginx php-fpm
sudo systemctl start nginx php-fpm
sudo systemctl enable nginx php-fpm
```

### Paso 5: Subir archivos

Opción A - SCP:
```bash
scp -i tu-key.pem -r ./carpeta-proyecto ec2-user@54.123.45.67:/var/www/html/
```

Opción B - Git:
```bash
cd /var/www/html
git clone tu-repositorio .
composer install --no-dev --optimize-autoloader
```

### Paso 6: Configurar permisos

```bash
cd /var/www/html
chmod 755 .
chmod 644 .env
```

### Paso 7: Crear base de datos

Conecta a RDS y crea la base de datos:
```bash
mysql -h galeria-db.xxxx.us-east-1.rds.amazonaws.com -u admin -p -e "CREATE DATABASE galeria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Paso 8: Ejecutar instalación

Abre en tu navegador:
```
http://54.123.45.67/install.php
```

Deberías ver:
- Tabla 'imagenes' creada o ya existe
- Tabla 'usuarios' creada o ya existe
- Usuario admin creado correctamente

### Paso 9: Verificar funcionamiento

- **Landing pública**: `http://54.123.45.67/index.php`
- **Login admin**: `http://54.123.45.67/admin/login.php`
- **Credenciales**: Las que definiste en `.env` (`admin` / `MiNuevaPassword123`)

---

## Verificación y Mantenimiento

### Verificar que todo funcione

```bash
# Verificar PHP
php -v

# Verificar extensiones
php -m | grep -E "pdo_mysql|mbstring|curl"

# Verificar Composer
composer --version

# Verificar archivos
ls -la /var/www/html/
```

### Cambiar contraseña del admin

1. Ingresa al panel de admin
2. Click en "Cambiar Password"
3. Ingresa la contraseña actual y la nueva

### Solución de problemas comunes

**Error: "No se puede conectar a la base de datos"**
- Verifica que el Security Group de RDS permita conexiones desde EC2
- Verifica las credenciales en `.env`

**Error: "Access denied" al subir imagen**
- Verifica que el usuario IAM tenga permisos `AmazonS3FullAccess`
- Verifica que el bucket policy permita lectura pública

**Error: "Session timeout"**
- Aumenta el tiempo de vida de la sesión en PHP
- Verifica que `SESSION_SECRET` esté configurado

**Las imágenes no se muestran**
- Verifica que el bucket S3 sea público
- Copia la URL de una imagen y pruébalas en el navegador directamente

---

## Estructura del Proyecto

```
/var/www/html/
├── .env                    # Credenciales (NO subir a git)
├── .env.example            # Plantilla
├── config.php              # Conexiones BD y S3
├── index.php               # Landing con carrusel
├── install.php             # Instalador de BD
├── assets/
│   └── styles.css          # Estilos con fondo animado
├── admin/
│   ├── login.php           # Página de login
│   ├── admin.php           # Panel de administración
│   ├── cambiar_password.php
│   ├── subir_imagen.php
│   └── eliminar_imagen.php
└── vendor/                 # Dependencias (AWS SDK)
```

---

## Seguridad Recomendada

1. **No exponer `.env`**: Asegúrate de que no sea accesible desde el navegador
2. **Usar HTTPS**: Configura un certificado SSL (Let's Encrypt)
3. **Limitar acceso al admin**: Restringe por IP en el Security Group
4. **Contraseñas fuertes**: Usa contraseñas diferentes para BD, admin, y AWS
5. **Backups**: Configura backups automáticos en RDS
6. **Monitoreo**: Usa CloudWatch para monitorear la instancia

---

## Soporte

Si tienes problemas, revisa:
1. Los logs de PHP: `/var/log/php-fpm/error.log` o `/var/log/httpd/error_log`
2. Los logs de Apache/Nginx
3. La consola de AWS para errores de S3/RDS
4. Verifica que las credenciales en `.env` sean correctas

---

## Prompt para Soporte con LLM

Copia y pega este prompt junto con tu error específico para obtener ayuda:

---

**CONTEXTO DEL PROYECTO:**

Estoy desarrollando una aplicación web de galería de imágenes desplegada en AWS con la siguiente arquitectura:

- **Backend**: PHP 8+ con Composer y AWS SDK for PHP
- **Base de datos**: AWS RDS MariaDB/MySQL
- **Almacenamiento**: AWS S3 para imágenes
- **Frontend**: HTML + Bootstrap 5 con fondo animado CSS
- **Autenticación**: Sesiones PHP con hash bcrypt

**ESTRUCTURA DEL PROYECTO:**

```
/var/www/html/
├── .env                 # Credenciales (no expuesto)
├── config.php           # Funciones: getDBConnection(), getS3Client(), getS3Url(), getCategorias()
├── index.php            # Landing con carrusel + filtro de categorías
├── install.php          # Crea tablas: imagenes, usuarios
├── assets/styles.css    # Fondo animado con gradientes y formas flotantes
├── admin/
│   ├── login.php        # Login admin con verificación de contraseña
│   ├── admin.php        # Panel: subir imágenes, tabla, cambiar contraseña
│   ├── cambiar_password.php
│   ├── procesar_login.php
│   ├── subir_imagen.php # Sube a S3 y guarda URL en BD
│   └── eliminar_imagen.php
└── vendor/              # aws/aws-sdk-php
```

**TABLAS DE LA BASE DE DATOS:**

```sql
-- imagenes: id, titulo, descripcion, categoria, url, created_at
-- usuarios: id, usuario, password (bcrypt), created_at
```

**ERROR QUE ESTOY TENIENDO:**

[PEGAR AQUÍ EL ERROR EXACTO QUE APARECE]

**LO QUE YA HE INTENTADO:**

- [Describe qué has intentado]

**INFORMACIÓN ADICIONAL:**
- PHP versión: [ej: 8.2]
- Servidor: [Apache/Nginx]
- SO: [Amazon Linux 2 / Ubuntu]
- Las credenciales de .env están correctas (he verificado)

---

Por favor, analiza el error y dime cómo solucionarlo.

---

**EJEMPLO DE USO:**

Si el error es: "Fatal error: Uncaught Error: Call to undefined function mysqli_connect()"

El prompt completo sería:

> [Pega el prompt de arriba]
>
> **ERROR QUE ESTOY TENIENDO:**
> Fatal error: Uncaught Error: Call to undefined function mysqli_connect() in /var/www/html/config.php:27
>
> **LO QUE YA HE INTENTADO:**
> - Reinicié el servidor PHP
> - Verifiqué que las credenciales en .env son correctas

---

Este prompt proporciona todo el contexto necesario para que un LLM pueda ayudarte efectivamente.