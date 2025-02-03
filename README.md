# Programação Web - Implementação Tecnologia Livre

![Static Badge](https://img.shields.io/badge/GCC116_14A-UFLA-green)
![Static Badge](https://img.shields.io/badge/2024%2F2-gray)

Integrantes: [Bruno Crespo](https://github.com/brunof5), [Weverton Andrade](https://github.com/weverton1998)

## Tecnologia Utilizadas

* **Servidor**: Apache - v2.4.58 (Win64)
* **Banco de Dados**: MySQL Community Server - V8.0.33 (Win64)
* **Framework**: CodeIgniter 4 - v4.6.0

## Descrição

Este projeto é uma REST API desenvolvida em CodeIgniter para um Aplicativo de Exposição/Venda. O sistema permite a criação, gerenciamento e finalização de compras, controle de pagamentos e atualização dinâmica do estoque. Utiliza Apache como servidor web e MySQL Community Server como banco de dados.

## Principais Funcionalidades

✅ **Gestão de Produtos**: Cadastro, listagem e atualização de produtos disponíveis para venda.

✅ **Controle de Compras**: Criação e atualização de compras, com status dinâmico conforme os pagamentos.

✅ **Pagamentos**: Registro e aprovação de pagamentos, com impacto direto na finalização das compras.

✅ **Itens de Compra**: Adição, modificação e remoção de itens dentro de uma compra.

✅ **Cálculo Automático**: Atualização do valor total da compra conforme os itens adicionados ou modificados.

✅ **Gestão de Estoque**: Redução automática do estoque ao finalizar uma compra.

✅ **Validações de Negócio**: Regras implementadas para garantir a consistência das operações.

## Diretórios

📁 A pasta `Banco de Dados` apresenta o script e diagrama SQL implementados do projeto.

📁 A pasta `src` apresenta os códigos do projeto.

## Regras de Negócio

🔹 Uma compra finalizada ou cancelada não deve ser alterada.

🔹 Uma compra não deve ser criada finalizada.

🔹 Uma compra deve ser finalizada se o seu pagamento for aprovado.

🔹 Um pagamento aprovado ou cancelado não deve ser alterado.

🔹 Um pagamento deve referenciar uma compra pendente.

🔹 A data de pagamento não deve ser anterior à data de sua compra.

🔹 Ao aprovar um pagamento, a compra referenciada deve ser automaticamente finalizada.

🔹 Um item de uma compra deve ser adicionado/alterado/removido se a compra estiver pendente.

🔹 O preço unitário de um item de uma compra deve corresponder ao valor do produto referenciado.

🔹 O valor total da compra deve ser atualizado automaticamente ao adicionar/alterar/remover itens.

🔹 A quantidade do item de uma compra não deve ultrapassar o estoque disponível do produto referenciado.

🔹 Ao finalizar uma compra, o estoque dos produtos deve ser atualizado.

🔹 Pagamentos e itens de uma compra não devem ser removidos se a compra estiver finalizada/cancelada.

🔹 O valor total de uma compra não deve exceder a soma dos valores dos seus itens.
