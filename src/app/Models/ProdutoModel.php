<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table = 'Produto';
    protected $primaryKey = 'id';
    protected $allowedFields = ['fornecedor', 'nome', 'valor', 'descricao', 'estoque', 'disponivel'];
    protected $validationRules = [
        'fornecedor' => 'required|integer|is_not_unique[Fornecedor.id]',
        'nome'       => 'required|string|min_length[3]|max_length[100]',
        'valor'      => 'required|decimal|greater_than[0]',
        'descricao'  => 'permit_empty|string|max_length[255]',
        'estoque'    => 'required|integer|greater_than_equal_to[0]',
        'disponivel' => 'required|in_list[0,1]'
    ];
}
