<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\FornecedorModel;

class Fornecedores extends ResourceController
{
    use ResponseTrait;
    // Lista todos os fornecedores
    public function index()
    {
        $model = new FornecedorModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Lista um fornecedor
    public function show($id = null)
    {
        $model = new FornecedorModel();
        $data = $model->getWhere(['id' => $id])->getResult();

        if ($data) {
            return $this->respond($data);
        }
        
        return $this->failNotFound('Nenhum fornecedor encontrado com id '.$id);
    }

    // Adiciona um fornecedor
    public function create()
    {
        $model = new FornecedorModel();
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

    // Atualiza um fornecedor
    public function update($id = null)
    {
        $model = new FornecedorModel();
        $dataFornecedor = $model->find($id);
        $data = $this->request->getJSON();

        if ($dataFornecedor) {
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

        return $this->failNotFound('Nenhum fornecedor encontrado com id '.$id); 
    }

    // Deleta um fornecedor
    public function delete($id = null)
    {
        $model = new FornecedorModel();
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
        
        return $this->failNotFound('Nenhum fornecedor encontrado com id '.$id);  
    }
}
