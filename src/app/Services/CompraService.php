<?php

namespace App\Services;

use CodeIgniter\Config\BaseService;
use App\Models\CompraModel;
use App\Models\ItensCompraModel;
use App\Models\PagamentoModel;
use App\Models\ProdutoModel;
use Exception;

class CompraService extends BaseService
{
    // Calcula o campo 'valor_total' da Compra
    public function calcularValorTotal($compraId)
    {
        $compraModel = new CompraModel();
        $itensCompraModel = new ItensCompraModel();

        $compra = $compraModel->find($compraId);
        if (!$compra) {
            throw new Exception('Compra não encontrada', 404);
        }

        $itens = $itensCompraModel->where('compra', $compraId)->findAll();

        $valorTotal = array_reduce($itens, function ($total, $item) {
            return $total + ($item['preco_unitario'] * $item['quantidade']);
        }, 0);

        $compraModel->update($compraId, ['valor_total' => $valorTotal]);
    }

    // Remove ItensCompra da Compra
    public function removerItemCompra($itemId)
    {
        $itensCompraModel = new ItensCompraModel();
        $compraModel = new CompraModel();

        $item = $itensCompraModel->find($itemId);
        if (!$item) {
            throw new Exception('Item de compra não encontrado', 404);
        }

        $compra = $compraModel->find($item['compra']);
        if (!$compra) {
            throw new Exception('Compra não encontrada', 404);
        }

        if ($compra['estado'] !== 'Pendente') {
            throw new Exception('Não é possível remover um item de uma compra finalizada/cancelada', 400);
        }

        $itensCompraModel->delete($itemId);
        $this->calcularValorTotal($item['compra']);
    }

    // Finaliza uma Compra
    public function finalizarCompra($compraId, $pagamentoId)
    {
        $compraModel = new CompraModel();
        $compra = $compraModel->find($compraId);
        
        $pagamentoModel = new PagamentoModel();
        $pagamento = $pagamentoModel->find($pagamentoId);

        if (!$compra) {
            throw new Exception('Compra não encontrada', 404);
        }

        if (!$pagamento) {
            throw new Exception('Pagamento não encontrado', 404);
        }

        if ($compra['estado'] === 'Pendente' && $pagamento['estado'] === 'Aprovado') {
            $compraModel->update($compraId, ['estado' => 'Finalizado']);
        } else {
            throw new Exception('Não foi possível finalizar a compra', 403);
        }
    }

    // Permite reduzir o estoque
    public function reduzirEstoqueCompra($compraId)
    {
        $itensCompraModel = new ItensCompraModel();
        $produtoModel = new ProdutoModel();

        $itens = $itensCompraModel->where('compra', $compraId)->findAll();

        foreach ($itens as $item) {
            $produto = $produtoModel->find($item['produto']);

            if ($produto) {
                $novoEstoque = max(0, $produto['estoque'] - $item['quantidade']);
                $produtoModel->update($produto['id'], ['estoque' => $novoEstoque]);
            }
        }
    }
}
