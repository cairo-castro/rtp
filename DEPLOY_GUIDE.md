# Guia de Deploy para Produção - RTP Hospital Report

## ⚠️ IMPORTANTE - Preparação para Deploy via FTP

Este guia detalha como fazer o deploy do sistema RTP Hospital Report em um servidor de produção usando apenas FTP (FileZilla).

## 📋 Pré-requisitos

### No Servidor de Produção:
- PHP 7.4 ou superior
- MySQL/MariaDB
- Extensões PHP: pdo, pdo_mysql, json
- Apache/Nginx com mod_rewrite habilitado

### Ferramentas Necessárias:
- FileZilla ou cliente FTP similar
- Editor de texto (para configurações)

## 🚀 Passos para Deploy

### 1. Preparação Local

#### 1.1 Instalar Dependências (se ainda não fez)
```bash
# Se tiver Composer instalado localmente
composer install --no-dev --optimize-autoloader

# Se NÃO tiver Composer, pule esta etapa
# O sistema funciona sem dependências externas
```

#### 1.2 Configurar Ambiente de Produção
- Edite `src/config/database.php` com dados do servidor
- Configure `src/config/app.php` para produção
- Crie arquivo `.htaccess` na pasta public

### 2. Estrutura de Arquivos para Upload

#### Arquivos OBRIGATÓRIOS para enviar via FTP:
```
public/
├── index.php                 ← Ponto de entrada principal
├── .htaccess                ← Configurações Apache (criar se não existir)
├── favicon.ico
└── assets/
    ├── css/relatorio.css
    ├── js/relatorio.js
    ├── js/csrf.js
    └── images/logo-emserh-em-png.png

src/
├── config/
│   ├── app.php
│   ├── database.php
│   └── routes.php
├── controllers/
│   └── RelatorioController.php
├── core/
│   ├── Controller.php
│   ├── CsrfProtection.php
│   ├── ErrorHandler.php
│   └── Router.php
├── helpers/
│   └── relatorio_helpers.php
├── models/
│   └── RelatorioModel.php
└── views/
    ├── layouts/main.php
    └── relatorio/dashboard.php

logs/                        ← Criar pasta vazia (permissão 755)
```

### 3. Upload via FTP

#### 3.1 Conectar ao Servidor
1. Abra FileZilla
2. Conecte ao seu servidor FTP
3. Navegue até a pasta raiz do seu domínio (geralmente `public_html` ou `www`)

#### 3.2 Estrutura no Servidor
```
public_html/                 ← Raiz do domínio
├── index.php               ← Upload direto na raiz
├── .htaccess              ← Upload direto na raiz
├── favicon.ico            ← Upload direto na raiz
├── assets/                ← Criar pasta e enviar conteúdo
├── src/                   ← Criar pasta e enviar conteúdo
└── logs/                  ← Criar pasta vazia
```

#### 3.3 Upload dos Arquivos
1. **Envie TODOS os arquivos da pasta `public/` para a RAIZ do domínio**
2. **Envie a pasta `src/` completa para a raiz do domínio**
3. **Crie a pasta `logs/` no servidor**

### 4. Configurações Pós-Upload

#### 4.1 Permissões de Pastas (via FTP)
- `logs/` → 755 (leitura/escrita)
- `src/` → 755 
- `assets/` → 755

#### 4.2 Editar Configurações no Servidor
Usando o editor de arquivos do painel de controle ou baixando/editando/reenviando:

**Arquivo: `src/config/database.php`**
```php
// Altere as configurações do banco
'host' => 'localhost',           // ou IP do seu MySQL
'dbname' => 'seu_banco_dados',   // nome do seu banco
'username' => 'seu_usuario',     // usuário do banco
'password' => 'sua_senha'        // senha do banco
```

**Arquivo: `src/config/app.php`**
```php
// Configure para produção
'environment' => 'production',
'debug' => false,
'base_url' => 'https://seudominio.com',
```

### 5. Configuração do Banco de Dados

#### 5.1 Importar Banco
1. Acesse phpMyAdmin do seu hosting
2. Crie um novo banco de dados
3. Importe o arquivo `dump-all-u313569922_rtpdiario-202505271211.sql`

#### 5.2 Verificar Conexão
Acesse: `https://seudominio.com/` 
Se aparecer erro de conexão, verifique os dados em `database.php`

### 6. Arquivo .htaccess (IMPORTANTE)

Se não existir, crie o arquivo `.htaccess` na raiz:

```apache
RewriteEngine On

# Redirecionamento para HTTPS (recomendado)
# Descomente se tiver SSL
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Redirecionar para index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Segurança - Bloquear acesso a arquivos sensíveis
<Files "*.log">
    Order Allow,Deny
    Deny from all
</Files>

<Files "composer.json">
    Order Allow,Deny
    Deny from all
</Files>

# Bloquear acesso direto à pasta src
RedirectMatch 404 ^/src/

# Headers de segurança
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Cache de assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## 🔧 Solução de Problemas

### Erro 500 - Internal Server Error
1. Verifique permissões das pastas
2. Verifique se o `.htaccess` está correto
3. Consulte logs de erro do servidor

### Erro de Conexão com Banco
1. Verifique dados em `src/config/database.php`
2. Teste conexão via phpMyAdmin
3. Confirme se o banco foi importado

### Páginas em Branco
1. Ative debug temporariamente em `app.php`
2. Verifique logs em `logs/error.log`
3. Confirme se todos os arquivos foram enviados

### Charts não Aparecem
1. Verifique se `assets/js/relatorio.js` foi enviado
2. Confirme se CDNs do Chart.js estão acessíveis
3. Teste em navegador diferente

## ✅ Verificação Final

1. **Acesse:** `https://seudominio.com/`
2. **Teste:** Seleção de unidade e geração de relatório
3. **Verifique:** Se os gráficos estão funcionando
4. **Confirme:** Se não há erros no console do navegador

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs em `logs/error.log`
2. Teste cada componente separadamente
3. Confirme configurações do servidor

---

**⚠️ IMPORTANTE:** Mantenha sempre backup dos arquivos antes de fazer deploy!
