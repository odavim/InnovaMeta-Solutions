# InnovaMeta Solutions

Site institucional e sistema de orçamentos da InnovaMeta Solutions (projeto de TCC), feito em PHP + MySQL.

## Funcionalidades
- Cadastro, login e recuperação de senha por e-mail
- Solicitação e acompanhamento de orçamentos por plano
- Painel administrativo (orçamentos, preços, configurações, níveis de usuário, exportação de usuários em CSV)
- Formulário de contato com envio por e-mail (PHPMailer)

## Como rodar localmente
1. Instale XAMPP (PHP 8.x + MariaDB/MySQL) e coloque o projeto em `htdocs`.
2. Crie o banco `innova` e importe `innova.sql` (só estrutura e dados de configuração/planos, sem usuários).
3. Copie `src/config.example.php` para `src/config.local.php` e preencha banco, SMTP e `base_url`.
   Use uma **senha de app** do Google para o SMTP. O `config.local.php` está no `.gitignore`.
4. Cadastre um usuário pelo site e promova-o a admin direto no banco:
   `UPDATE usuarios SET nivel = 'admin' WHERE email = 'seu@email';`
5. Acesse `http://localhost/<pasta>/index.php`.

## Estrutura
- `index.php` — página inicial
- `src/` — páginas, dashboards e lógica
- `src/pages/` — carrinho, recuperação/redefinição de senha, logout
- `src/portfolios/` — projetos antigos da equipe (linkados em "Sobre")
- `src/PHPMailer-master/` — biblioteca de e-mail

## Segurança
Nunca coloque senhas, dumps com dados reais ou CPFs no repositório. Configurações sensíveis ficam em `src/config.local.php` ou em variáveis de ambiente.
