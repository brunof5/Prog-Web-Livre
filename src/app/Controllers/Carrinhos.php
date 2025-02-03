<?php namespace App\Controllers;

use App\Services\CompraService;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ItensCompraModel;
use Exception;

class Carrinhos extends ResourceController
{
    use ResponseTrait;
    // Lista todas os itens
    public function index()
    {
        $model = new ItensCompraModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Lista todos os itens de uma compra
    public function show($id = null)
    {
        $model = new ItensCompraModel();
        $data = $model->where('compra', $id)->findAll();

        if ($data) {
            return $this->respond($data);
        }
        
        return $this->failNotFound('Nenhum item encontrado para a compra com id ' . $id);
    }

    // Adiciona um item
    public function create()
    {
        try {
            $model = new ItensCompraModel();
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
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getCode') && $e->getCode() ? $e->getCode() : 400;

            return $this->respond([
                'status'   => $statusCode,
                'error'    => null,
                'messages' => [
                    'failure' => $e->getMessage()
                ]
            ], $statusCode);
        }
    }

    // Atualiza um item
    public function update($id = null)
    {
        try {
            $model = new ItensCompraModel();
            $dataCarrinho = $model->find($id);
            $data = $this->request->getJSON();
    
            if ($dataCarrinho) {
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
    
            return $this->failNotFound('Nenhum item encontrado com id '.$id); 
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getCode') && $e->getCode() ? $e->getCode() : 400;

            return $this->respond([
                'status'   => $statusCode,
                'error'    => null,
                'messages' => [
                    'failure' => $e->getMessage()
                ]
            ], $statusCode);
        }
    }

    // Deleta um item
    public function delete($id = null)
    {
        try {
            $model = new ItensCompraModel();
            $data = $model->find($id);
            
            if ($data) {
                $compraService = new CompraService();
                $compraService->removerItemCompra($id);
                $response = [
                    'status'   => 200,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Dados removidos'
                    ]
                ];
                return $this->respondDeleted($response);
            }
            
            return $this->failNotFound('Nenhum item encontrado com id '.$id);  
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getCode') && $e->getCode() ? $e->getCode() : 400;

            return $this->respond([
                'status'   => $statusCode,
                'error'    => null,
                'messages' => [
                    'failure' => $e->getMessage()
                ]
            ], $statusCode);
        }
    }
}
