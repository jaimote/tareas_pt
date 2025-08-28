<?php

namespace Feature;

use App\Models\TasksModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class TasksControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    // Ejecuta migraciones y refresca la DB entre tests
    protected $refresh = true;

    public function testIndexReturnsTasksList(): void
    {
        // Arrange: insertar algunos registros
        $model = new TasksModel();
        $id1 = $model->insert(['title' => 'Tarea A', 'completed' => false]);
        $id2 = $model->insert(['title' => 'Tarea B', 'completed' => true]);

        // Act
        $res = $this->get('/tasks');
        /*
         $res = $this->call('get', '/tasks', [], [
            'Accept' => 'application/json'
        ]);
        */
        // Assert
        $res->assertStatus(200);

        // Decodificar y validar estructura/contenido
        $payload = json_decode($res->getBody(), true);
        print_r($payload);die;
        $this->assertNotNull($payload, 'La respuesta no es JSON válido');

        // Soporta ambas formas: lista directa o envoltura con "data"
        $list = is_array($payload) && array_is_list($payload)
            ? $payload
            : ($payload['data'] ?? []);

        $this->assertIsArray($list, 'La respuesta no contiene una lista de tareas');
        $this->assertNotEmpty($list, 'La lista de tareas está vacía');

        // Verifica que estén los títulos esperados
        $titles = array_column($list, 'title');
        $this->assertContains('Tarea A', $titles);
        $this->assertContains('Tarea B', $titles);

        // Verifica que al menos contenga los IDs insertados (si el endpoint los devuelve)
        $ids = array_column($list, 'id');
        if (!empty($ids)) {
            $this->assertContains($id1, $ids);
            $this->assertContains($id2, $ids);
        }

    }

    public function testCreateTaskPersistsAndReturnsResource(): void
    {
        // Act: enviar JSON
        $create = $this->withBodyFormat('json')->post('/tasks', [
            'title'     => 'Nueva tarea',
            'completed' => false,
        ]);

        // Assert: aceptar 200 o 201
        $status = $create->getStatusCode();
        $this->assertContains($status, [200, 201], 'El endpoint de creación debe devolver 200 o 201');

        $create->assertHeader('Content-Type', 'application/json');

        $payload = json_decode($create->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload['id'] ?? null);
        $this->assertSame('Nueva tarea', $payload['title'] ?? null);

        // Verifica persistencia vía modelo
        $model = new TasksModel();
        $row = $model->find((int) $payload['id']);
        $this->assertNotEmpty($row);
        $this->assertSame('Nueva tarea', $row['title'] ?? ($row->title ?? null));
    }

    public function testUpdateThenDeleteTask(): void
    {
        // Arrange: crear una tarea inicial
        $model = new TasksModel();
        $id = $model->insert(['title' => 'Original', 'completed' => false]);

        $update = $this->withBodyFormat('json')->put('/tasks/' . $id, [
            'title' => 'Actualizada',
        ]);
        $update->assertStatus(200);

        // Assert: título cambiado en DB
        $updated = $model->find($id);
        $this->assertSame('Actualizada', $updated['title'] ?? ($updated->title ?? null));

        // Act: eliminar
        $delete = $this->delete('/tasks/' . $id);
        // 204 No Content recomendado
        $this->assertContains($delete->getStatusCode(), [200, 204]);

        // Assert: obtener por ID debe fallar (404)
        $show = $this->get('/tasks/' . $id);
        $show->assertStatus(404);
    }

}