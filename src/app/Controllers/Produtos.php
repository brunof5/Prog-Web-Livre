<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProdutoModel;

class Produtos extends ResourceController
{
    use ResponseTrait;
    // Lista todos os produtos
    public function index()
    {
        $model = new ProdutoModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Lista um produto
    public function show($id = null)
    {
        $model = new ProdutoModel();
        $data = $model->getWhere(['id' => $id])->getResult();

        if ($data) {
            return $this->respond($data);
        }
        
        return $this->failNotFound('Nenhum produto encontrado com id '.$id);
    }

    // Adiciona um produto
    public function create()
    {
        $model = new ProdutoModel();
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

    // Atualiza um produto
    public function update($id = null)
    {
        $model = new ProdutoModel();
        $dataProduto = $model->find($id);
        $data = $this->request->getJSON();

        if ($dataProduto) {
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

        return $this->failNotFound('Nenhum produto encontrado com id '.$id); 
    }

    // Deleta um produto
    public function delete($id = null)
    {
        $model = new ProdutoModel();
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
        
        return $this->failNotFound('Nenhum produto encontrado com id '.$id);  
    }
}
