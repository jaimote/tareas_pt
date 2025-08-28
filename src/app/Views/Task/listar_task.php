<!-- html -->
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Tareas - UI Simple</title>
    <!-- Opcional: meta CSRF si tu backend lo requiere -->
    <!-- <meta name="csrf-token" content="CSRF_TOKEN_AQUI"> -->
    <style>
        /* css */
        :root { --bg:#0f172a; --panel:#111827; --muted:#9ca3af; --text:#e5e7eb; --primary:#22c55e; --danger:#ef4444; --border:#334155; }
        html,body { height:100%; background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,Arial,sans-serif; }
        .container { max-width:720px; margin:40px auto; padding:24px; background:var(--panel); border:1px solid var(--border); border-radius:12px; }
        h1 { margin:0 0 16px; font-size:22px; }
        form#create-form { display:flex; gap:8px; margin-bottom:16px; }
        form#create-form input { flex:1; padding:10px 12px; border-radius:8px; border:1px solid var(--border); background:#0b1220; color:var(--text); }
        form#create-form button { padding:10px 14px; border:0; border-radius:8px; background:var(--primary); color:#052e13; font-weight:600; cursor:pointer; }
        #alerts { min-height:20px; margin-bottom:8px; font-size:13px; color:var(--muted); }
        ul#task-list { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:8px; }
        .item { display:flex; align-items:center; gap:10px; padding:12px; border:1px solid var(--border); border-radius:10px; background:#0b1220; }
        .title { flex:1; }
        .title[contenteditable="true"] { outline:2px solid #1e293b; border-radius:6px; padding:4px; background:#0a1426; }
        .actions { display:flex; gap:6px; }
        .btn { padding:6px 10px; border-radius:8px; border:1px solid var(--border); background:#0b1220; color:var(--text); cursor:pointer; font-size:13px; }
        .btn:hover { background:#0f1b33; }
        .btn-danger { border-color:#4c1d1d; color:#fecaca; background:#1f0a0a; }
        .empty { color:var(--muted); text-align:center; padding:16px; border:1px dashed var(--border); border-radius:10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Tareas</h1>

    <div id="alerts"></div>

    <form id="create-form" autocomplete="off">
        <input id="title-input" name="title" type="text" placeholder="Nueva tarea..." required minlength="3" maxlength="200" />
        <button type="submit">Crear</button>
    </form>

    <ul id="task-list"></ul>
</div>

<script>
    /* javascript */
    // Configura la URL base de tu API
    const API_BASE = 'http://localhost:8080/tasks';
    // Si tu backend usa CSRF, lee el token (opcional)
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

    // Helper: fetch JSON con manejo de errores
    async function jsonFetch(url, options = {}) {
        const headers = {
            'Accept': 'application/json',
            ...(options.body && { 'Content-Type': 'application/json' }),
            ...(CSRF_TOKEN && { 'X-CSRF-TOKEN': CSRF_TOKEN }),
            ...(options.headers || {}),
        };
        const res = await fetch(url, { credentials: 'same-origin', ...options, headers });
        if (!res.ok) {
            let detail;
            try { detail = await res.json(); } catch { detail = { message: res.statusText }; }
            throw new Error(detail?.message || `HTTP ${res.status}`);
        }
        // 204 No Content
        if (res.status === 204) return null;
        return res.json();
    }

    // Estado simple en memoria
    let tasks = [];

    // Renderizado
    function render() {
        const ul = document.getElementById('task-list');
        ul.innerHTML = '';
        if (!tasks.length) {
            ul.innerHTML = '<li class="empty">No hay tareas. ¡Crea la primera!</li>';
            return;
        }

        for (const t of tasks) {
            const li = document.createElement('li');
            li.className = 'item';
            li.dataset.id = t.id;
            //console.log(t);
            const title = document.createElement('div');
            title.className = 'title';
            title.textContent = t.title;

            const actions = document.createElement('div');
            actions.className = 'actions';

            const editBtn = document.createElement('button');
            editBtn.className = 'btn';
            editBtn.textContent = 'Editar';

            const saveBtn = document.createElement('button');
            saveBtn.className = 'btn';
            saveBtn.textContent = 'Guardar';
            saveBtn.style.display = 'none';

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'btn';
            cancelBtn.textContent = 'Cancelar';
            cancelBtn.style.display = 'none';

            const delBtn = document.createElement('button');
            delBtn.className = 'btn btn-danger';
            delBtn.textContent = 'Eliminar';

            actions.append(editBtn, saveBtn, cancelBtn, delBtn);
            li.append(title, actions);
            ul.appendChild(li);

            // Eventos de acción
            editBtn.addEventListener('click', () => {
                title.contentEditable = 'true';
                title.focus();
                editBtn.style.display = 'none';
                saveBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';
            });

            cancelBtn.addEventListener('click', () => {
                title.textContent = t.title;
                title.contentEditable = 'false';
                editBtn.style.display = 'inline-block';
                saveBtn.style.display = 'none';
                cancelBtn.style.display = 'none';
            });

            // Guardar con botón o Enter
            saveBtn.addEventListener('click', () => saveTitle(li, title, t));
            title.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveTitle(li, title, t);
                }
            });

            delBtn.addEventListener('click', async () => {
                if (!confirm('¿Eliminar esta tarea?')) return;
                try {
                    await jsonFetch(`${API_BASE}/${t.id}`, { method: 'DELETE' });
                    tasks = tasks.filter(x => x.id !== t.id);
                    render();
                    alertMsg('Tarea eliminada.');
                } catch (err) {
                    alertMsg('No se pudo eliminar: ' + err.message, true);
                }
            });
        }
    }

    async function saveTitle(li, titleEl, task) {
        const newTitle = titleEl.textContent.trim();
        if (!newTitle) {
            alertMsg('El título no puede estar vacío.', true);
            titleEl.textContent = task.title;
            titleEl.contentEditable = 'false';
            return;
        }
        try {
            // Actualización parcial usando PATCH (ajusta a PUT si tu API lo requiere)
            const payload = { title: newTitle };
            const updated = await jsonFetch(`${API_BASE}/${task.id}`, {
                method: 'PUT', // Cambia a 'PUT' si tu backend no maneja PATCH
                body: JSON.stringify(payload),
            });
            // Si la API devuelve el recurso actualizado:
            const merged = updated || { ...task, title: newTitle };
            tasks = tasks.map(x => (x.id === task.id ? merged : x));
            render();
            alertMsg('Tarea actualizada.');
        } catch (err) {
            alertMsg('No se pudo actualizar: ' + err.message, true);
            // revertir visual
            titleEl.textContent = task.title;
            titleEl.contentEditable = 'false';
            // volver a modo normal
            const [ , actions ] = li.children;
            const [ editBtn, saveBtn, cancelBtn ] = actions.children;
            editBtn.style.display = 'inline-block';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        }
    }

    // Carga inicial
    async function loadTasks() {
        try {
            tasks = await jsonFetch(API_BASE, { method: 'GET' });
            render();
        } catch (err) {
            alertMsg('No se pudo cargar la lista: ' + err.message, true);
        }
    }

    // Crear tarea
    document.getElementById('create-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('title-input');
        const title = input.value.trim();
        if (!title) return;
        try {
            const created = await jsonFetch(API_BASE, {
                method: 'POST',
                body: JSON.stringify({ title, completed: false }),
            });
            tasks.unshift(created);
            render();
            input.value = '';
            alertMsg('Tarea creada.');
        } catch (err) {
            alertMsg('No se pudo crear: ' + err.message, true);
        }
    });

    function alertMsg(msg, isError = false) {
        const el = document.getElementById('alerts');
        el.textContent = msg;
        el.style.color = isError ? '#fca5a5' : '#9ca3af';
        // Limpia el mensaje después de unos segundos
        clearTimeout(alertMsg._t);
        alertMsg._t = setTimeout(() => (el.textContent = ''), 3000);
    }

    // Inicia
    loadTasks();
</script>
</body>
</html>