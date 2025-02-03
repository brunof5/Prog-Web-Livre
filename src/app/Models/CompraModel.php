<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\CompraService;
use Exception;

class CompraModel extends Model
{
    protected $table = 'Compra';
    protected $primaryKey = 'id';
    protected $allowedFields = ['cliente', 'data_compra', 'valor_total', 'estado'];
    protected $validationRules = [
        'cliente'      => 'required|integer|is_not_unique[Cliente.id]',
        'data_compra'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'valor_total'  => 'permit_empty|decimal|greater_than_equal_to[0]',
        'estado'       => 'permit_empty|in_list[Pendente,Cancelado,Finalizado]'
    ];
    protected $beforeInsert = ['validarEstadoCompra', 'validarValorTotalCompraInsert'];
    protected $beforeUpdate = ['verificaEstadoCompra', 'validarValorTotalCompraUpdate'];
    protected $afterUpdate = ['verificarFinalizacaoCompra'];

    // Uma Compra não pode ser inserida com estado 'Finalizado'.
    protected function validarEstadoCompra(array $data)
    {
        if (isset($data['data']['estado']) && $data['data']['estado'] === 'Finalizado') {
            throw new Exception('Uma compra não pode ser inserida como Finalizado', 400);
        }

        return $data;
    }

    // Uma Compra com o estado 'Finalizado' ou 'Cancelado' não deve ser alterada.
    protected function verificaEstadoCompra(array $data)
    {
        $compra = $this->find($data['id'][0]);

        if (isset($compra) && in_array($compra['estado'], ['Finalizado', 'Cancelado'])) {
            throw new Exception('Não é possível alterar uma compra finalizada/cancelada', 403);
        }

        return $data;
    }

    // Bloqueia o campo 'valor_total' ser maior que 0 na criação de uma Compra
    protected function validarValorTotalCompraInsert(array $data)
    {
        if (isset($data['data']['valor_total']) && $data['data']['valor_total'] != 0) {
            throw new Exception('O valor total de uma compra ao ser criada deve ser 0', 400);
        }

        return $data;
    }

    // Bloqueia o campo 'valor_total' ser maior que soma dos itens de uma Compra
    protected function validarValorTotalCompraUpdate(array $data)
    {
        if (!isset($data['id'][0])) {
            return $data;
        }

        $compraId = $data['id'][0];
        $itensCompraModel = new ItensCompraModel();
        $somaItens = $itensCompraModel->select('SUM(preco_unitario * quantidade) AS soma')
                                        ->where('compra', $compraId)
                                        ->first()['soma'] ?? 0;

        if (isset($data['data']['valor_total']) && $data['data']['valor_total'] > $somaItens) {
            throw new Exception('O valor total de uma compra não pode exceder a soma dos valores dos seus itens', 400);
        }

        return $data;
    }

    // Quando o estado da Compra for alterado para 'Finalizado', reduz o estoque dos Produtos
    protected function verificarFinalizacaoCompra(array $data)
    {
        if (!isset($data['id'][0]) || !isset($data['data']['estado'])) {
            return $data;
        }

        $compraId = $data['id'][0];

        if ($data['data']['estado'] === 'Finalizado') {
            $service = new CompraService();
            $service->reduzirEstoqueCompra($compraId);
        }

        return $data;
    }
}
