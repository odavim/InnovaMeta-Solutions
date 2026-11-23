<?php
// Copie este arquivo para "config.local.php" (que NÃO vai para o Git) e preencha os valores.
// Também é possível usar variáveis de ambiente com os mesmos nomes em maiúsculas
// (ex.: DB_PASS, SMTP_PASS).
return [
    'db_host'   => 'localhost',
    'db_user'   => 'root',
    'db_pass'   => '',
    'db_name'   => 'innova',

    // Conta que ENVIA os e-mails (use uma senha de app do Google, nunca a senha normal)
    'smtp_host' => 'smtp.gmail.com',
    'smtp_user' => 'seu-email@gmail.com',
    'smtp_pass' => 'senha-de-app-aqui',
    'smtp_port' => 587,

    // Para onde chegam as mensagens do formulário de contato
    'contato_destino' => 'seu-email@gmail.com',

    // URL pública da pasta "src" (sem barra no final). Usada nos links dos e-mails.
    'base_url'  => 'http://localhost/Innovameta/src',
];
