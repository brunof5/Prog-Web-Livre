<?php

namespace App\Models;

use CodeIgniter\Model;
use \App\Services\CompraService;
use \Exception;

class PagamentoModel extends Model
{
    protected $table = 'Pagamento';
    protected $primaryKey = 'id';
    protected $allowedFields = ['compra', 'metodo', 'data_pagamento', 'estado'];
    protected $validationRules = [
        'compra'            => 'required|integer|is_not_unique[Compra.id]',
        'metodo'            => 'required|in_list[Pix,Cartao_Credito,Cartao_Debito,Dinheiro]',
        'data_pagamento'    => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'estado'            => 'permit_empty|in_list[Pendente,Aprovado,Cancelado]'
    ];
    protected $beforeInsert = ['validaDataPagamento', 'verificaCompraPendente'];
    protected $afterInsert = ['atualizarEstadoCompraInsert'];
    protected $beforeUpdate = ['verificaPagamentoEstado', 'validaDataPagamento', 'verificaCompraPendente'];
    protected $afterUpdate = ['atualizarEstadoCompraUpdate'];
    protected $beforeDelete = ['bloquearRemocaoPagamentoCompraFinalizadaCancelada'];

    // Um Pagamento com o estado 'Aprovado' ou 'Cancelado' não deve ser alterado.
    protected function verificaPagamentoEstado(array $data)
    {
        $pagamento = $this->find($data['id'][0]);

        if (isset($pagamento) && in_array($pagamento['estado'], ['Aprovado', 'Cancelado'])) {
            throw new Exception('Não é possível alterar um pagamento aprovado/cancelado', 403);
        }

        return $data;
    }

    // A 'data_pagamento' de um Pagamento não pode ser anterior à 'data_compra' Compra referenciada.
    protected function validaDataPagamento(array $data)
    {
        $pagamento = null;
        $compraModel = new CompraModel();
        $compra = null;

        if (isset($data['id'])) {   // UPDATE
            $pagamento = $this->find($data['id'][0]);
            $compra = isset($data['data']['compra']) ? $compraModel->find($data['data']['compra']) : $compraModel->find($pagamento['compra']);
        } else {    // INSERT
            $compra = $compraModel->find($data['data']['compra']);
        }

        if (isset($data['data']['data_pagamento']) && $data['data']['data_pagamento'] < $compra['data_compra']) {
            throw new Exception('Não é possível pagar uma compra antes da sua data', 400);
        }

        return $data;
    }

    // Um Pagamento só pode referenciar uma Compra se o estado dela for 'Pendente'.
    protected function verificaCompraPendente(array $data)
    {
        $pagamento = null;
        $compraModel = new CompraModel();
        $compra = null;

        if (isset($data['id'])) {   // UPDATE
            $pagamento = $this->find($data['id'][0]);
            $compra = isset($data['data']['compra']) ? $compraModel->find($data['data']['compra']) : $compraModel->find($pagamento['compra']);
        } else {    // INSERT
            $compra = $compraModel->find($data['data']['compra']);
        }

        if (isset($compra) && $compra['estado'] !== 'Pendente') {
            throw new Exception('A compra já está finalizada/cancelada', 400);
        }

        return $data;
    }

    // Ao aprovar um Pagamento por inserção, a Compra referenciada deve ser finalizada
    protected function atualizarEstadoCompraInsert(array $data)
    {
        if (!isset($data['id']) || !isset($data['data']['compra']) || !isset($data['data']['estado'])) {
            return $data;
        }

        $pagamento = $this->find($data['id']);
        $compraModel = new CompraModel();
        $compra = $compraModel->find($data['data']['compra']);

        if (isset($pagamento) && isset($compra) && $data['data']['estado'] === 'Aprovado') {
            $service = new CompraService();
            $service->finalizarCompra($data['data']['compra'], $data['id']);
        }
        
        return $data;
    }

    // Ao aprovar um Pagamento por atualização, a Compra referenciada deve ser finalizada
    protected function atualizarEstadoCompraUpdate(array $data)
    {
        if (!isset($data['id']) || !isset($data['data']['estado'])) {
            return $data;
        }

        $pagamento = $this->find($data['id'][0]);
        $compraModel = new CompraModel();
        $compraId = $data['data']['compra'] ?? $pagamento['compra'];
        $compra = $compraModel->find($compraId);

        if (isset($pagamento) && isset($compra) && $data['data']['estado'] === 'Aprovado') {
            $service = new CompraService();
            $service->finalizarCompra($compraId, $data['id'][0]);
        }
        
        return $data;
    }

    // Bloquea a remoção de um Pagamento de uma Compra finalizada/cancelada
    protected function bloquearRemocaoPagamentoCompraFinalizadaCancelada(array $data)
    {
        $pagamento = $this->find($data['id'][0]);

        if (!$pagamento) {
            throw new Exception('Pagamento não encontrado', 404);
        }

        $compraModel = new CompraModel();
        $compra = $compraModel->find($pagamento['compra']);

        if ($compra && in_array($compra['estado'], ['Finalizado', 'Cancelado'])) {
            throw new Exception('Não é possível remover um pagamento de uma compra finalizada/cancelada', 400);
        }

        return $data;
    }
}
