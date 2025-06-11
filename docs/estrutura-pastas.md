# 📁 Estrutura de Pastas - Sistema RTP

## 📋 Visão Geral

Este documento detalha a organização completa de arquivos e pastas do sistema RTP (Relatório de Produtividade Territorializada), explicando a função de cada componente.

## 🏗️ Estrutura Hierárquica

```
rtp/
├── 📄 composer.json                    # Dependências PHP (PHPUnit, etc.)
├── 📄 composer.lock                    # Lock das versões das dependências  
├── 📄 phpunit.xml                      # Configuração dos testes unitários
├── 📄 README.md                        # Documentação principal do projeto
├── 📄 README_GAUGE.md                  # Documentação específica dos gauges
├── 📄 CORREÇÕES_PRODUTIVIDADE.md       # Log de correções e melhorias
├── 📄 test_db.php                      # Script de teste de conexão DB
├── 📄 test_gerencia.php                # Script de teste módulo gerência
│
├── 📂 docs/                            # 📚 Documentação técnica completa
│   ├── 📄 README.md                    # Índice da documentação
│   ├── 📄 estrutura-pastas.md          # Este arquivo
│   ├── 📄 gauge.md                     # Sistema de medidores circulares
│   ├── 📄 graficos-barras.md           # Gráficos de barras temporais
│   ├── 📄 ui-vs-logica.md              # Separação UI/Lógica
│   ├── 📄 produtividade-metas.md       # Cálculos e indicadores
│   ├── 📄 agendado-pactuado.md         # Diferenças conceituais
│   ├── 📄 grupos-servicos.md           # Organização hospitalar
│   ├── 📄 calculos-produtividade.md    # Fórmulas e algoritmos
│   ├── 📄 meta-pdt.md                  # Plano Diretor Territorializado
│   └── 📄 produtividade-max.md         # Análise comparativa
│
├── 📂 logs/                            # 📋 Logs do sistema
│   └── 📄 error.log                    # Log de erros
│
├── 📂 public/                          # 🌐 Arquivos públicos (web root)
│   ├── 📄 favicon.ico                  # Ícone do site
│   ├── 📄 index.php                    # Ponto de entrada principal
│   └── 📂 assets/                      # Recursos estáticos
│       ├── 📂 css/
│       │   └── 📄 relatorio.css        # Estilos do dashboard
│       ├── 📂 images/
│       │   └── 📄 logo-emserh-em-png.png # Logo institucional
│       └── 📂 js/
│           ├── 📄 gauge.html           # Configurações de gauge
│           └── 📄 relatorio.js         # JavaScript dos relatórios
│
├── 📂 src/                             # 🔧 Código fonte da aplicação
│   ├── 📂 config/                      # ⚙️ Configurações
│   │   ├── 📄 app.php                  # Configurações gerais
│   │   ├── 📄 database.php             # Configuração do banco
│   │   ├── 📄 routes.php               # Definição de rotas
│   │   └── 📄 session.php              # Configuração de sessões
│   │
│   ├── 📂 controllers/                 # 🎮 Controladores MVC
│   │   └── 📄 RelatorioController.php  # Controlador de relatórios
│   │
│   ├── 📂 core/                        # 🏛️ Núcleo do framework
│   │   ├── 📄 Controller.php           # Classe base dos controladores
│   │   ├── 📄 ErrorHandler.php         # Tratamento de erros
│   │   └── 📄 Router.php               # Sistema de roteamento
│   │
│   ├── 📂 helpers/                     # 🛠️ Funções auxiliares
│   │   └── 📄 relatorio_helpers.php    # Helpers específicos
│   │
│   ├── 📂 models/                      # 🗄️ Modelos de dados
│   │   └── 📄 RelatorioModel.php       # Modelo de relatórios
│   │
│   └── 📂 views/                       # 👁️ Camada de apresentação
│       ├── 📂 layouts/                 # Layout base
│       │   └── 📄 main.php             # Template principal
│       ├── 📂 partials/                # Componentes reutilizáveis
│       │   ├── 📄 header.php           # Cabeçalho padrão
│       │   └── 📄 header_gerencia.php  # Cabeçalho gerencial
│       └── 📂 relatorio/               # Views específicas
│           ├── 📄 dashboard.php        # Dashboard principal
│           └── 📄 gerencia.php         # Interface gerencial
│
├── 📂 tests/                           # 🧪 Testes automatizados
│   ├── 📂 Integration/                 # Testes de integração
│   │   ├── 📄 DatabaseIntegrationRobustTest.php
│   │   ├── 📄 DatabaseIntegrationTest.php
│   │   └── 📄 DatabaseIntegrationValidatedTest.php
│   └── 📂 Performance/                 # Testes de performance
│       ├── 📄 ChartOptimizationTest.php
│       ├── 📄 DateFormattingTest.php
│       └── 📄 PerformanceBenchmarkTest.php
│
└── 📂 vendor/                          # 🏪 Dependências do Composer
    ├── 📄 autoload.php                 # Autoloader principal
    ├── 📂 composer/                    # Arquivos do Composer
    ├── 📂 phpunit/                     # Framework de testes
    └── 📂 [outras-dependencias]/       # Outras bibliotecas
```

## 📂 Detalhamento das Pastas

### 🌐 **public/** - Arquivos Públicos
**Propósito**: Diretório raiz do servidor web, contém todos os arquivos acessíveis publicamente.

- **index.php**: Ponto de entrada único (front controller)
- **assets/**: Recursos estáticos organizados por tipo
  - **css/**: Folhas de estilo
  - **js/**: Scripts JavaScript
  - **images/**: Imagens e ícones

### 🔧 **src/** - Código Fonte
**Propósito**: Contém toda a lógica da aplicação seguindo padrão MVC.

#### ⚙️ **src/config/**
- **app.php**: Configurações gerais (timezone, debug, etc.)
- **database.php**: Parâmetros de conexão com banco
- **routes.php**: Mapeamento de URLs para controladores
- **session.php**: Configurações de sessão e segurança

#### 🎮 **src/controllers/**
- **RelatorioController.php**: Lógica de negócio dos relatórios
  - Processamento de dados
  - Validações
  - Orquestração entre Model e View

#### 🏛️ **src/core/**
- **Controller.php**: Classe abstrata base
- **ErrorHandler.php**: Tratamento centralizado de erros
- **Router.php**: Sistema de roteamento URL

#### 🛠️ **src/helpers/**
- **relatorio_helpers.php**: Funções utilitárias
  - Formatação de números
  - Cálculos de produtividade
  - Conversões de data

#### 🗄️ **src/models/**
- **RelatorioModel.php**: Acesso a dados
  - Consultas SQL
  - Processamento de resultados
  - Validações de banco

#### 👁️ **src/views/**
- **layouts/**: Templates base
- **partials/**: Componentes reutilizáveis
- **relatorio/**: Views específicas do módulo

### 📚 **docs/** - Documentação
**Propósito**: Documentação técnica completa e atualizada.

- **README.md**: Índice principal
- **estrutura-pastas.md**: Este documento
- **gauge.md**: Sistema de medidores
- **[outros].md**: Documentação específica

### 🧪 **tests/** - Testes
**Propósito**: Testes automatizados para garantir qualidade.

- **Integration/**: Testes de integração com banco
- **Performance/**: Testes de performance e benchmarks

## 📋 Padrões de Nomenclatura

### 📁 **Pastas**
- **Minúsculas**: `config`, `models`, `views`
- **Plural**: `controllers`, `helpers`, `tests`
- **Descritivo**: `partials`, `layouts`

### 📄 **Arquivos PHP**
- **PascalCase**: `RelatorioController.php`
- **snake_case**: `relatorio_helpers.php`
- **Descritivos**: `database.php`, `routes.php`

### 🎨 **Assets**
- **kebab-case**: `relatorio.css`, `gauge.html`
- **Extensão clara**: `.css`, `.js`, `.png`

## 🔄 Fluxo de Execução

### 📊 **Requisição de Relatório**
```
1. public/index.php (entrada)
2. src/core/Router.php (roteamento)
3. src/controllers/RelatorioController.php (processamento)
4. src/models/RelatorioModel.php (dados)
5. src/helpers/relatorio_helpers.php (cálculos)
6. src/views/relatorio/dashboard.php (apresentação)
7. public/assets/ (recursos estáticos)
```

### 🧪 **Execução de Testes**
```
1. phpunit.xml (configuração)
2. tests/[categoria]/[Teste].php (execução)
3. src/ (código testado)
4. logs/error.log (resultado)
```

## 📦 Dependências e Componentes

### 🏪 **Composer (vendor/)**
- **PHPUnit**: Framework de testes
- **Autoloader**: Carregamento automático de classes
- **Dependências**: Bibliotecas externas

### 🔧 **Bibliotecas JavaScript**
- **ApexCharts**: Gráficos e gauges
- **Vanilla JS**: Sem frameworks pesados
- **CSS Grid/Flexbox**: Layout responsivo

## 🛡️ Segurança e Permissões

### 📁 **Estrutura de Permissões**
```bash
# Pastas somente leitura
docs/         -> 644
src/config/   -> 644 (exceto dados sensíveis)
tests/        -> 644

# Pastas de escrita
logs/         -> 755
public/assets/ -> 755 (cache de assets)

# Arquivo de entrada
public/index.php -> 644
```

### 🔒 **Arquivos Sensíveis**
- **src/config/database.php**: Credenciais DB
- **logs/error.log**: Informações internas
- **composer.lock**: Versões específicas

## 📈 Escalabilidade

### 🔄 **Expansão Futura**
```
Preparado para:
├── src/api/          # API REST futura
├── src/services/     # Camada de serviços
├── src/middleware/   # Middlewares personalizados
├── src/events/       # Sistema de eventos
└── src/jobs/         # Processamento assíncrono
```

### 🗄️ **Banco de Dados**
- **Configuração**: Centralizada em `src/config/`
- **Migrações**: Futuro suporte a migrações
- **Seeds**: Dados de exemplo/teste

## 📋 Manutenção

### 🧹 **Limpeza Periódica**
- **logs/**: Rotacionar logs mensalmente
- **vendor/**: Atualizar dependências
- **public/assets/**: Limpar cache se necessário

### 📊 **Monitoramento**
- **logs/error.log**: Monitorar erros
- **tests/**: Executar regularmente
- **docs/**: Manter atualizada

---

*Última atualização: Junho 2025*