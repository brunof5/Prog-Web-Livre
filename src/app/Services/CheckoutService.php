<?php

namespace App\Services;

use CodeIgniter\Config\BaseService;
use App\Models\CompraModel;
use App\Models\PagamentoModel;
use Exception;

class CheckoutService extends BaseService
{
    // Uma Compra só pode ser 'Finalizado' se o estado de algum Pagamento dela for 'Aprovado'.
    public function verificarParaFinalizarCompra($compraId)
    {
        $compraModel = new CompraModel();
        $pagamentoModel = new PagamentoModel();

        $compra = $compraModel->find($compraId);

        if (!$compra) {
            throw new Exception('Compra não encontrada', 404);
        }

        if ($compra['estado'] !== 'Pendente') {
            throw new Exception('A compra já está finalizada/cancelada', 400);
        }

        // Verificar pagamentos associados
        $pagamentos = $pagamentoModel->where('compra', $compraId)->findAll();

        if (empty($pagamentos)) {
            throw new Exception('Não há como finalizar uma compra sem um pagamento', 400);
        }

        $temAprovado = false;
        $todosCancelados = true;

        foreach ($pagamentos as $pagamento) {
            if ($pagamento['estado'] === 'Aprovado') {
                $temAprovado = true;
            }
    
            if ($pagamento['estado'] !== 'Cancelado') {
                $todosCancelados = false;
            }
        }

        if ($todosCancelados) {
            throw new Exception('Não é possível finalizar uma compra com todos os pagamentos cancelados', 400);
        }
    
        if (!$temAprovado) {
            throw new Exception('Pagamento pendente', 400);
        }
    }
}
