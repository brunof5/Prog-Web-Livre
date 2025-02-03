<?php

namespace App\Models;

use CodeIgniter\Model;

class FornecedorModel extends Model
{
    protected $table = 'Fornecedor';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nome', 'endereco', 'email'];
    protected $validationRules = [
        'nome'      => 'required|string|min_length[3]|max_length[100]',
        'endereco'  => 'required|string|max_length[255]',
        'email'     => 'required|valid_email|is_unique[Fornecedor.email,id,{id}]',
    ];
}
