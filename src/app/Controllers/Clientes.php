<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ClienteModel;

class Clientes extends ResourceController
{
    use ResponseTrait;
    // Lista todos os clientes
    public function index()
    {
        $model = new ClienteModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Lista um cliente
    public function show($id = null)
    {
        $model = new ClienteModel();
        $data = $model->getWhere(['id' => $id])->getResult();

        if ($data) {
            return $this->respond($data);
        }
        
        return $this->failNotFound('Nenhum cliente encontrado com id '.$id);
    }

    // Adiciona um cliente
    public function create()
    {
        $model = new ClienteModel();
        $data = $this->request->getJSON();

        if ($model->insert($data)) {
            $response = [
                'status'   => 201,
                'error'    => null,
                'messages' => [
                    'success' => 'Dados salvos'
                ]
            ];
            return $this->respondCreated($response);
        }

        return $this->fail($model->errors());
    }

    // Atualiza um cliente
    public function update($id = null)
    {
        $model = new ClienteModel();
        $dataClient = $model->find($id);
        $data = $this->request->getJSON();

        if ($dataClient) {
            if ($model->update($id, $data)) {
                $response = [
                    'status'   => 200,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Dados atualizados'
                        ]
                ];
                return $this->respond($response);
            };
    
            return $this->fail($model->errors());
        }

        return $this->failNotFound('Nenhum cliente encontrado com id '.$id); 
    }

    // Deleta um cliente
    public function delete($id = null)
    {
        $model = new ClienteModel();
        $data = $model->find($id);
        
        if ($data) {
            $model->delete($id);
            $response = [
                'status'   => 200,
                'error'    => null,
                'messages' => [
                    'success' => 'Dados removidos'
                ]
            ];
            return $this->respondDeleted($response);
        }
        
        return $this->failNotFound('Nenhum cliente encontrado com id '.$id);  
    }
}
