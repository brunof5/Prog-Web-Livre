<?php

namespace App\Models;

use CodeIgniter\Model;
use \App\Models\CompraModel;
use \App\Models\ProdutoModel;
use \App\Services\CompraService;
use Exception;

class ItensCompraModel extends Model
{
    protected $table = 'ItensCompra';
    protected $primaryKey = 'id';
    protected $allowedFields = ['compra', 'produto', 'quantidade', 'preco_unitario'];
    protected $validationRules = [
        'compra'         => 'required|integer|is_not_unique[Compra.id]',
        'produto'        => 'required|integer|is_not_unique[Produto.id]',
        'quantidade'     => 'required|integer|greater_than[0]',
        'preco_unitario' => 'permit_empty|decimal|greater_than_equal_to[0]'
    ];
    protected $beforeInsert = ['verificarEstadoCompra', 'validarPrecoUnitario', 'verificarItemExistente'];
    protected $afterInsert = ['calcularValorTotal'];
    protected $beforeUpdate = ['verificarEstadoCompra', 'validarPrecoUnitario', 'verificarAtualizacaoItem'];
    protected $afterUpdate = ['calcularValorTotal'];

    // Bloquear adição/alteração de itens a uma compra finalizada/cancelada.
    protected function verificarEstadoCompra(array $data)
    {
        $compraModel = new CompraModel();
        $compra = null;
        $outraCompra = null;
        
        if (isset($data['id'])) {     // UPDATE
            $itemCompra = $this->find($data['id'][0]);
            $compra = $compraModel->find($itemCompra['compra']);
            $outraCompra = isset($data['data']['compra']) ? $compraModel->find($data['data']['compra']) : $compraModel->find($itemCompra['compra']);
        } else {    // INSERT
            $compra = $compraModel->find($data['data']['compra']);
            $outraCompra = $compra;
        }

        if (isset($compra) && isset($outraCompra) && in_array($compra['estado'], ['Finalizado', 'Cancelado']) || 
                in_array($outraCompra['estado'], ['Finalizado', 'Cancelado'])) {
            throw new Exception('Não é possível adicionar/alterar um item a/de uma compra finalizada/cancelada', 400);
        }

        return $data;
    }

    // 'preco_unitario' deve corresponder ao 'valor' do Produto referenciado.
    protected function validarPrecoUnitario(array $data)
    {
        $produtoModel = new ProdutoModel();
        $produto = null;
        
        if (isset($data['id'])) {     // UPDATE
            $itemCompra = $this->find($data['id'][0]);
            $produto = isset($data['data']['produto']) ? $produtoModel->find($data['data']['produto']) : $produtoModel->find($itemCompra['produto']);
        } else {    // INSERT
            $produto = $produtoModel->find($data['data']['produto']);
        }

        if (!$produto) {
            throw new Exception('Produto não encontrado', 404);
        }

        if (isset($data['data']['preco_unitario']) && $data['data']['preco_unitario'] != $produto['valor']) {
            throw new Exception('O valor do item não corresponde ao valor do produto', 400);
        }
        else {
            $data['data']['preco_unitario'] = $produto['valor'];
        } 

        return $data;
    }

    // Função que verifica várias condições quando um ItemCompra é inserido em uma Compra
    protected function verificarItemExistente(array $data)
    {
        $compraModel = new CompraModel();
        $produtoModel = new ProdutoModel();

        if (!isset($data['data']['compra'], $data['data']['produto'], $data['data']['quantidade'])) {
            return $data;
        }

        $compraId = $data['data']['compra'];
        $produtoId = $data['data']['produto'];
        $quantidade = $data['data']['quantidade'];

        $compra = $compraModel->find($compraId);
        $produto = $produtoModel->find($produtoId);
        
        if (!$compra) {
            throw new Exception('Compra não encontrada', 404);
        }
        if ($compra['estado'] !== 'Pendente') {
            throw new Exception('Não é possível adicionar um item à uma compra finalizada/cancelada', 400);
        }
        if (!$produto) {
            throw new Exception('Produto não encontrado', 404);
        }
        // A 'quantidade' de um ItensCompra não deve ultrapassar o 'estoque' do Produto referenciado.
        if ($quantidade > $produto['estoque']) {
            throw new Exception('Não é possível ultrapassar o estoque do produto', 400);
        }

        // Verifica se já existe um ItemCompra com a mesma compra e produto
        $itemExistente = $this->where('compra', $compraId)->where('produto', $produtoId)->first();

        if ($itemExistente) {
            $novaQuantidade = $itemExistente['quantidade'] + $quantidade;

            // A 'quantidade' de um ItensCompra não deve ultrapassar o 'estoque' do Produto referenciado.
            if ($novaQuantidade > $produto['estoque']) {
                throw new Exception('Não é possível ultrapassar o estoque do produto', 400);
            }

            // Atualiza o item existente com a nova quantidade
            $this->update($itemExistente['id'], ['quantidade' => $novaQuantidade]);

            throw new Exception('Item já existente. Quantidade atualizada com sucesso.', 200);
        }

        return $data;
    }

    // Função que verifica várias condições quando um ItemCompra é modificado em uma Compra
    protected function verificarAtualizacaoItem(array $data)
    {
        $compraModel = new CompraModel();
        $produtoModel = new ProdutoModel();

        if (!isset($data['id'])) {
            return $data;
        }

        $itemAtual = $this->find($data['id'][0]);
        
        if (!$itemAtual) {
            throw new Exception('Item de compra não encontrado', 404);
        }

        $compraId = $data['data']['compra'] ?? $itemAtual['compra'];
        $produtoId = $data['data']['produto'] ?? $itemAtual['produto'];
        $quantidade = $data['data']['quantidade'] ?? $itemAtual['quantidade'];

        $compra = $compraModel->find($compraId);
        $produto = $produtoModel->find($produtoId);
        
        if (!$compra) {
            throw new Exception('Compra não encontrada', 404);
        }
        if ($compra['estado'] !== 'Pendente') {
            throw new Exception('Não é possível modificar um item de uma compra finalizada/cancelada', 400);
        }
        if (!$produto) {
            throw new Exception('Produto não encontrado', 404);
        }
        // A 'quantidade' de um ItensCompra não deve ultrapassar o 'estoque' do Produto referenciado.
        if ($quantidade > $produto['estoque']) {
            throw new Exception('Não é possível ultrapassar o estoque do produto', 400);
        }

        // Verifica se existe outro item com o mesmo compra/produto
        $itemDuplicado = $this->where('compra', $compraId)->where('produto', $produtoId)->where('id !=', $data['id'])->first();

        if ($itemDuplicado) {
            $novaQuantidade = $itemDuplicado['quantidade'] + $quantidade;

            // A 'quantidade' de um ItensCompra não deve ultrapassar o 'estoque' do Produto referenciado.
            if ($novaQuantidade > $produto['estoque']) {
                throw new Exception('Não é possível ultrapassar o estoque do produto', 400);
            }

            // Atualiza o item existente e remove o antigo
            $this->update($itemDuplicado['id'], ['quantidade' => $novaQuantidade]);
            $this->delete($data['id']);

            throw new Exception('Item já existente. Quantidade atualizada com sucesso.', 200);
        }

        return $data;
    }

    protected function calcularValorTotal(array $data)
    {
        $compraService = new CompraService();
        if (isset($data['id']) && is_array($data['id'])) {  // UPDATE
            $itemAtual = $this->find($data['id'][0]);
            $compraId = $data['data']['compra'] ?? $itemAtual['compra'];
            $compraService->calcularValorTotal($compraId);
        } else {    // INSERT
            $compraService->calcularValorTotal($data['data']['compra']);
        }
    }
}
