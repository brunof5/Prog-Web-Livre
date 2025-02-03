<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PagamentoModel;
use Exception;

class Pagamentos extends ResourceController
{
    use ResponseTrait;
    // Lista todos os pagamentos
    public function index()
    {
        $model = new PagamentoModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Lista um pagamento
    public function show($id = null)
    {
        $model = new PagamentoModel();
        $data = $model->getWhere(['id' => $id])->getResult();

        if ($data) {
            return $this->respond($data);
        }
        
        return $this->failNotFound('Nenhum pagamento encontrado com id '.$id);
    }

    // Adiciona um pagamento
    public function create()
    {
        try {
            $model = new PagamentoModel();
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

    // Atualiza um pagamento
    public function update($id = null)
    {
        try {
            $model = new PagamentoModel();
            $dataPagamento = $model->find($id);
            $data = $this->request->getJSON();
    
            if ($dataPagamento) {
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
    
            return $this->failNotFound('Nenhum pagamento encontrado com id '.$id); 
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

    // Deleta um pagamento
    public function delete($id = null)
    {
        try {
            $model = new PagamentoModel();
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
            
            return $this->failNotFound('Nenhum pagamento encontrado com id '.$id);  
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
