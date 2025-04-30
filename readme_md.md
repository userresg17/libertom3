# Libertom Financial Platform

![Libertom Logo](public/images/logo.png)

## Sobre o Libertom

Libertom é uma plataforma financeira integrada para os mercados do Brasil, Argentina e Uruguai, oferecendo uma solução completa para gerenciamento financeiro transfronteiriço.

O sistema inclui:
- Conta multimoeda (USD, EUR, GBP, JPY, BRL, ARS, UYU)
- Carteira única com IBAN para transferências internacionais
- Compra e venda de ações, ETFs e títulos
- Integração com GoldStay (token ERC-20 lastreado em ouro)
- Suporte a gift cards
- Sistema de empréstimo/cheque especial em USD
- KYC obrigatório e compliance multicountry

## Requisitos Técnicos

- PHP 8.1 ou superior
- Composer
- Node.js e NPM
- MySQL 8.0 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP necessárias:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PCRE
  - PDO
  - Tokenizer
  - XML

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/libertom/platform.git
cd platform
```

2. Instale as dependências de PHP:
```bash
composer install
```

3. Instale as dependências de JavaScript:
```bash
npm install
```

4. Crie e configure o arquivo de ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure seu banco de dados no arquivo `.env`

6. Execute as migrações e seeds:
```bash
php artisan migrate --seed
```

7. Compile os assets:
```bash
npm run build
```

8. Inicie o servidor de desenvolvimento:
```bash
php artisan serve
```

## Estrutura do Projeto

- `app/` - Código PHP da aplicação
- `bootstrap/` - Arquivos de inicialização
- `config/` - Configurações da aplicação
- `database/` - Migrações e seeds
- `public/` - Ponto de entrada e assets compilados
- `resources/` - Views, assets não compilados e linguagens
- `routes/` - Definição de rotas
- `storage/` - Arquivos, logs e cache
- `tests/` - Testes automatizados
- `vendor/` - Dependências gerenciadas pelo Composer

## Funcionalidades Principais

### Sistema Multi-Moeda
Gerencie seu dinheiro em 7 moedas diferentes: USD, EUR, GBP, JPY, BRL, ARS e UYU.

### GoldStay
Token ERC-20 na rede Polygon, lastreado 1:1 em ouro físico, oferecendo proteção patrimonial contra inflação e desvalorização monetária.

### Investimentos
Dashboard completo para compra, venda e acompanhamento de:
- Ações de empresas
- ETFs
- Títulos de renda fixa

### Transferências Internacionais
Envie e receba dinheiro entre os países do Cone Sul (Brasil, Argentina e Uruguai) com taxas reduzidas e maior velocidade.

### Gift Cards
Compre gift cards de diversas marcas e serviços globais para uso pessoal ou presente.

## Integração com APIs

### Pagamentos
- Banco Cora (Brasil) para operações locais
- Stripe para pagamentos internacionais

### Blockchain
- Integração com a rede Polygon para o GoldStay
- Web3.js para interação com smart contracts

## Segurança

- Autenticação JWT para APIs
- Proteção CSRF em formulários
- KYC (Know Your Customer) obrigatório
- Verificação em duas etapas (2FA)
- Criptografia de dados sensíveis

## Licença

Este software é proprietário da Libertom Corporation S.A. Todos os direitos reservados.

## Contato

Para mais informações, contate:
- Email: suporte@libertom.com
- Website: https://libertom.com