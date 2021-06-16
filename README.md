
## Code Challenge Grupo ZAP - Backend

Projeto escrito com framework Laravel.

## Requisitos:
- PHP 7.2 ou superior
- Extensão PHP Mbstring
- Dependências solicitadas pelo Laravel (https://laravel.com/docs).
- Composer

## Instalação
Clonar o repositório
Na pasta escolhida rodar o comando composer install.
Dentro do projeto executar o comando php artisan serve
Subi juntamente o arquivo .env para pegar a key, tambem pode copiar o arquivo .env.example para dentro do .env

Caso não rode executar php artisan key:generate para gerar uma nova key

## Endpoints

O projeto contem os endpoints:  
- GET /properties/zap  
    Retorna todos os imóveis elegíveis para o grupo Zap  
- GET /properties/viva
    Retorna todos os imoveis elegíveis para o grupo Viva Real  
Os endpoints podem receber a paginação.

## Linkedin
https://www.linkedin.com/in/jessicaremediobarbosa/
