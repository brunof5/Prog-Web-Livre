-- Criando o banco de dados
CREATE DATABASE Loja;

USE Loja;

-- Criando a tabela Cliente
CREATE TABLE Cliente (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    idade INT NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telefone VARCHAR(20) NOT NULL
);

-- Criando a tabela Fornecedor
CREATE TABLE Fornecedor (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE
);

-- Criando a tabela Produto
CREATE TABLE Produto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fornecedor INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    descricao TEXT,
    estoque INT NOT NULL DEFAULT 0,
    FOREIGN KEY (fornecedor) REFERENCES Fornecedor(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Criando a tabela Compra
CREATE TABLE Compra (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente INT NOT NULL,
    data_compra TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    estado ENUM('Pendente', 'Cancelado', 'Finalizado') DEFAULT 'Pendente' NOT NULL,
    FOREIGN KEY (cliente) REFERENCES Cliente(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Criando a tabela ItensCompra
CREATE TABLE ItensCompra (
    id INT PRIMARY KEY AUTO_INCREMENT,
    compra INT NOT NULL,
    produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL DEFAULT 0,
    FOREIGN KEY (compra) REFERENCES Compra(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (produto) REFERENCES Produto(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Criando a tabela Pagamento
CREATE TABLE Pagamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    compra INT NOT NULL,
    metodo ENUM('Pix', 'Cartao_Credito', 'Cartao_Debito', 'Dinheiro') NOT NULL,
    data_pagamento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendente', 'Aprovado', 'Cancelado') DEFAULT 'Pendente' NOT NULL,
    FOREIGN KEY (compra) REFERENCES Compra(id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Inserindo dados de exemplo
INSERT INTO Cliente (nome, idade, endereco, email, telefone) VALUES
('João Silva', 30, 'Rua A, 123', 'joao@email.com', '11987654321'),
('Maria Souza', 25, 'Av. B, 456', 'maria@email.com', '11876543210');

INSERT INTO Fornecedor (nome, endereco, email) VALUES
('Fornecedor X', 'Rua C, 789', 'fornecedorx@email.com'),
('Fornecedor Y', 'Av. D, 101', 'fornecedory@email.com');

INSERT INTO Produto (fornecedor, nome, valor, descricao, estoque) VALUES
(1, 'Notebook', 3500.00, 'Notebook Dell 16GB RAM', 10),
(1, 'Mouse', 50.00, 'Mouse óptico USB', 50),
(2, 'Teclado', 120.00, 'Teclado mecânico', 30);

INSERT INTO Compra (cliente, data_compra, valor_total, estado) VALUES
(1, '2024-02-01 12:00:00', 3550.00, 'Finalizado'),
(2, '2024-02-02 14:30:00', 120.00, 'Pendente');

INSERT INTO ItensCompra (compra, produto, quantidade, preco_unitario) VALUES
(1, 1, 1, 3500.00),
(1, 2, 1, 50.00),
(2, 3, 1, 120.00);

INSERT INTO Pagamento (compra, metodo, data_pagamento, estado) VALUES
(1, 'Cartao_Credito', '2024-02-01 12:30:00', 'Aprovado');