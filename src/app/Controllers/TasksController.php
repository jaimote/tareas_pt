<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Tasks;
use App\Models\TasksModel;
use CodeIgniter\HTTP\ResponseInterface;

class TasksController extends BaseController
{
    public function index(): ResponseInterface
    {
        $q = $this->request->getGet('q');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = 20;

        $model = new TasksModel();

        $builder = $model->builder();
        if ($q) {
            $builder->like('title', $q);
        }

        $tasks = $builder
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'page'  => $page,
            'count' => count($tasks),
            'data'  => $tasks,
        ]);

    }

    public function show(int $id): ResponseInterface
    {
        $model = new TasksModel();
        $task  = $model->find($id);

        if (!$task) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Tarea no encontrada']);
        }

        return $this->response->setJSON($task);
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getPost();
        $data = [
            'title' => $payload['title'] ?? null,
            'completed' => isset($payload['completed']) ? (bool) $payload['completed'] : false,
        ];

        $model = new TasksModel();

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'message' => 'Errores de validación',
                    'errors'  => $model->errors(),
                ]);
        }

        $task = new Tasks($data);

        if (!$model->save($task)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'message' => 'No se pudo crear la tarea',
                ]);
        }

        $id = $model->getInsertID();
        $task = $model->find($id);

        return $this->response
            ->setStatusCode(201)
            ->setHeader('Location', site_url("tasks/{$id}"))
            ->setJSON($task);

    }

    public function update(int $id): ResponseInterface
    {
        $model = new TasksModel();

        $existing = $model->find($id);
        if (!$existing) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Tarea no encontrada']);
        }

        $payload = $this->request->getRawInput();

        if (empty($payload)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Cuerpo de la solicitud vacío']);
        }

        $data = [
            'id'        => $id,
            'title'     => $payload['title'] ?? null,
            'completed' => isset($payload['completed']) ? (bool) $payload['completed'] : null,
        ];


        $errors = [];
        if ($data['title'] === null || trim((string) $data['title']) === '') {
            $errors['title'] = 'El título es obligatorio.';
        } elseif (mb_strlen((string) $data['title']) > 200) {
            $errors['title'] = 'El título no debe exceder 200 caracteres.';
        }

        if (!empty($errors)) {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => 'Errores de validación',
                'errors'  => $errors,
            ]);
        }

        $entity = new Tasks($data);

        if (!$model->save($entity)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'message' => 'No se pudo actualizar la tarea',
                    'errors'  => $model->errors(),
                ]);
        }

        $updated = $model->find($id);

        return $this->response
            ->setStatusCode(200)
            ->setJSON($updated);

    }

    public function delete(int $id): ResponseInterface
    {
        if ($id <= 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'ID inválido']);
        }

        $model = new TasksModel();

        $existing = $model->find($id);
        if (!$existing) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Tarea no encontrada']);
        }

        $ok = $model->delete($id);

        if (!$ok) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'No se pudo eliminar la tarea']);
        }

        return $this->response->setStatusCode(200)->setJSON(['message' => 'Tarea eliminada']);

    }

}
