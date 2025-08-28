# Tareas PT — Controlador y Vista listar_task

Este proyecto expone un controlador para gestionar tareas (CRUD) y una vista sencilla llamada `listar_task` para mostrarlas e interactuar con ellas usando AJAX.

## Requisitos

- PHP 8.1+
- Composer
- Base de datos configurada (según `.env`)
- Opcional: Docker

## Puesta en marcha

### Opción A: Local (Spark)

# bash
composer install
# Migraciones (si aplica)
php spark migrate
# Levanta el servidor
php spark serve
