# FUTZONE - Loja de Chuteiras

Projeto final do 4º bimestre: e-commerce em PHP, MySQL (PDO) e Bootstrap.

## Como rodar
1. Copie a pasta para `C:\xampp\htdocs\futzone` e ligue Apache e MySQL no XAMPP.
2. No phpMyAdmin, importe `database/schema.sql` (cria o banco `futzone`, as tabelas e os produtos).
3. Copie `.env.example` para `.env` e confira os dados do banco.
4. Acesse http://localhost/futzone/

## Acesso de teste
- Admin: admin@futzone.com / admin123 (painel em /futzone/admin/)
- Cliente: crie uma conta em "Cadastrar".

## Recursos
- Catálogo com busca e filtro por categoria
- Carrinho na sessão, com validação de estoque
- Checkout (PIX, Boleto ou Cartão) com baixa de estoque
- Painel admin: CRUD de produtos e lista de pedidos
- Segurança: prepared statements, password_hash e sessão no servidor