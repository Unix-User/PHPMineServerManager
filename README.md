# PHPMineServerManager

[![Laravel Tests](https://github.com/Unix-User/PHPMineServerManager/actions/workflows/laravel.yml/badge.svg)](https://github.com/Unix-User/PHPMineServerManager/actions/workflows/laravel.yml)
[![License](https://img.shields.io/github/license/Unix-User/PHPMineServerManager)](https://github.com/Unix-User/PHPMineServerManager/blob/main/LICENSE)
[![Laravel Version](https://img.shields.io/badge/Laravel-v10-FF2D20?logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://php.net)
[![GitHub last commit](https://img.shields.io/github/last-commit/Unix-User/PHPMineServerManager)](https://github.com/Unix-User/PHPMineServerManager/commits)
[![GitHub issues](https://img.shields.io/github/issues/Unix-User/PHPMineServerManager)](https://github.com/Unix-User/PHPMineServerManager/issues)
[![Maintainability](https://img.shields.io/badge/maintainability-A-brightgreen)](https://github.com/Unix-User/PHPMineServerManager)

<p align="center"><a href="https://minecraft.udianix.com.br" target="_blank"><img src="https://github.com/Unix-User/PHPMineServerManager/assets/38821945/b2298d64-d45a-4f33-9c96-83b9ef1f86fb" />
</a></p>

<p align="center">
    Uma demonstração desse projeto esta disponivel <a href="https://minecraft.udianix.com.br/" target="_blank">aqui</a>
</p>

## Sobre o Projeto

Este é um gerenciador de servidor de Minecraft, construído com Laravel e Jetstream. Ele permite que você gerencie facilmente seu servidor de Minecraft, com uma interface de usuário intuitiva e poderosas funcionalidades.

## Instalação

1. Clone o repositório
    ```
    git clone https://github.com/Unix-User/PhPMineServerManager.git
    ```
2. Entre no diretório do projeto
    ```
    cd PhPMineServerManager
    ```
3. Instale as dependências
    ```
    composer install
    ```
4. Instale as dependências do npm
    ```
    npm install
    ```
5. Copie o arquivo de exemplo de ambiente e configure suas variáveis de ambiente
    ```
    cp .env.example .env
    ```
6. Gere uma chave de aplicativo
    ```
    php artisan key:generate
    ```
7. Execute as migrações
    ```
    php artisan migrate
    ```
8. Compile os assets do front-end
    ```
    npm run dev
    ```
9. Inicie o servidor
    ```
    php artisan serve
    ```


## Inicialização

Após a instalação, você pode acessar o aplicativo em seu navegador em `http://localhost:8000`.

## Contribuindo

Obrigado por considerar contribuir para este projeto! Por favor, leia o guia de contribuição antes de enviar qualquer pull request.

## Licença

Este projeto é um software de código aberto licenciado sob a [licença MIT](https://opensource.org/licenses/MIT).
