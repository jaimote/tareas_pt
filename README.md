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

# bash
docker compose up -d --build
# Verifica el servicio en [http://localhost:8080](http://localhost:8080)
# contenedor
docker exec -it tareas-pt-app /bin/bash
## API del controlador de Tareas

Se asume un recurso "Task" con campos básicos:
- id: int
- title: string
- completed: bool
- created_at, updated_at: timestamps

Rutas típicas (ajústalas a tu configuración actual):

- GET /tasks
    - Lista todas las tareas.
    - Respuesta:
      ```json
      [
        { "id": 1, "title": "Ejemplo", "completed": false, "created_at": "...", "updated_at": "..." }
      ]
      ```

- GET /tasks/{id}
    - Obtiene una tarea por ID.
    - 404 si no existe.

- POST /tasks
    - Crea una tarea.
    - Cuerpo (JSON):
      ```json
      { "title": "Nueva tarea", "completed": false }
      ```
    - Respuesta 201/200 con la tarea creada.

- PUT /tasks/{id} (o PATCH /tasks/{id})
    - Actualiza una tarea (PUT completo o PATCH parcial).
    - Ejemplo (parcial):
      ```json
      { "title": "Título actualizado" }
      ```

- DELETE /tasks/{id}
    - Elimina una tarea.
    - Respuesta recomendada: 204 No Content.
## Vista: Task/listar_task

La vista `listar_task` es una página HTML que:
- Muestra la lista de tareas obtenida del backend.
- Permite crear nuevas tareas.
- Permite editar el título de tareas existentes.
- Permite eliminar tareas.
- Usa fetch (AJAX) para todas las interacciones con la API.

### Flujo de trabajo

1. Al cargar la vista, un script JS hace `GET /tasks` y renderiza la lista.
2. El formulario “Nueva tarea” envía `POST /tasks` con `{ title, completed:false }`.
3. El botón “Editar/Guardar” dispara `PATCH /tasks/{id}` (o `PUT` según tu API) con `{ title }`.
4. El botón “Eliminar” hace `DELETE /tasks/{id}` y actualiza la lista.

### Ejemplo mínimo de integración (frontend)

Dentro de `listar_task.php`, puedes incluir un esqueleto como este y un script que consume el backend. Ajusta las rutas si tu servidor está en otra URL.


