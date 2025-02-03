<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 'Cliente';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nome', 'idade', 'endereco', 'email', 'telefone'];
    protected $validationRules = [
        'nome'      => 'required|string|min_length[3]|max_length[100]',
        'idade'     => 'required|integer|greater_than[0]',
        'endereco'  => 'required|string|max_length[255]',
        'email'     => 'required|valid_email|is_unique[Cliente.email,id,{id}]',
        'telefone'  => 'required|string|min_length[10]|max_length[20]'
    ];
}
