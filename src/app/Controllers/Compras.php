<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CompraModel;
use \App\Services\CheckoutService;
use Exception;

class Compras extends ResourceController
{
    use ResponseTrait;
    // Lista todas as compras
    public function index()
    {
        $model = new CompraModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Lista uma compra
    public function show($id = null)
    {
        $model = new CompraModel();
        $data = $model->getWhere(['id' => $id])->getResult();

        if ($data) {
            return $this->respond($data);
        }
        
        return $this->failNotFound('Nenhuma compra encontrado com id '.$id);
    }

    // Adiciona uma compra
    public function create()
    {
        try {
            $model = new CompraModel();
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

    // Atualiza uma compra
    public function update($id = null)
    {
        try {
            $model = new CompraModel();
            $dataCompra = $model->find($id);
            $data = json_decode(json_encode($this->request->getJSON()), true);
            
            if ($dataCompra) {
                if (isset($data['estado'])) {
                    if ($data['estado'] === 'Finalizado') {
                        $checkoutService = new CheckoutService();
                        $checkoutService->verificarParaFinalizarCompra($id);
                    }
                }

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
    
            return $this->failNotFound('Nenhuma compra encontrado com id '.$id); 
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

    // Deleta uma compra
    public function delete($id = null)
    {
        $model = new CompraModel();
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
        
        return $this->failNotFound('Nenhuma compra encontrado com id '.$id);  
    }
}
