# Proyecto bases de datos

Este repositorio contiene la estructura y el respaldo de la base de datos diseñada para la gestión integral de una papelería. El modelo permite administrar el control de inventario (registro de productos, existencias, precios y proveedores) así como el procesamiento y registro de ventas.

## Requisitos previos

* **Git** instalado en tu equipo.
* **PostgreSQL** con las herramientas de línea de comandos (`psql` y `pg_restore`) configuradas.

---

## Restauración de la Base de Datos

En la raíz de este repositorio encontrarás el archivo `papeleria.dump`, el cual contiene tanto la estructura de las tablas como los datos iniciales. Para restaurarlo en tu entorno local, sigue estos pasos:

1. Abre tu terminal (o consola de comandos) y crea una base de datos vacía llamada `papeleria`:
   ```bash
   psql -U postgres -c "CREATE DATABASE papeleria;"
   ```

2. Ejecuta el siguiente comando desde la carpeta donde se encuentra el archivo `papeleria.dump` para importar el respaldo:
   ```bash
   pg_restore -U postgres -d papeleria -v papeleria.dump
   ```
   *(Nota: Si tu usuario de PostgreSQL es distinto a `postgres`, reemplázalo en ambos comandos. Se te solicitará tu contraseña antes de iniciar).*

---

## Cómo empezar a trabajar

Para descargar el proyecto en tu computadora y comenzar a realizar modificaciones:

1. Clona el repositorio en tu máquina local:
   ```bash
   git clone https://github.com/Organizacion-Base-de-datos/Proyecto-bases-de-datos.git
   ```

2. Ingresa a la carpeta del proyecto:
   ```bash
   cd Proyecto-bases-de-datos
   ```

3. Asegúrate de estar ubicado en la rama `main` y tener los últimos cambios antes de editar:
   ```bash
   git checkout main
   git pull origin main
   ```

---

## Subir cambios a la rama `main`

Una vez que hayas realizado modificaciones en los scripts, diagramas o archivos del proyecto, súbelos directamente al repositorio remoto ejecutando los siguientes comandos en orden:

1. Agrega todos los archivos modificados o nuevos al área de preparación (*staging*):
   ```bash
   git add .
   ```

2. Guarda los cambios con un mensaje descriptivo:
   ```bash
   git commit -m "Descripción clara de los cambios realizados"
   ```

3. Envía tus cambios directamente a la rama `main`:
   ```bash
   git push origin main
   ```
