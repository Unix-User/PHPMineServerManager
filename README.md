<p align="center"><a href="https://udianix.com.br" target="_blank"><img src="https://github.com/Unix-User/PhPMineServerManager/assets/38821945/9d1f7264-9ccd-4369-ba37-5bc409a22caf" />
</a></p>

<p align="center">
<a href="https://github.com/Unix-User/PhPMineServerManager/actions"><img src="https://img.shields.io/github/workflow/status/Unix-User/PhPMineServerManager/tests" alt="Build Status"></a>
<a href="https://github.com/Unix-User/PhPMineServerManager"><img src="https://img.shields.io/github/downloads/Unix-User/PhPMineServerManager/total" alt="Total Downloads"></a>
<a href="https://github.com/Unix-User/PhPMineServerManager/releases"><img src="https://img.shields.io/github/v/release/Unix-User/PhPMineServerManager" alt="Latest Stable Version"></a>
<a href="https://github.com/Unix-User/PhPMineServerManager/blob/main/LICENSE"><img src="https://img.shields.io/github/license/Unix-User/PhPMineServerManager" alt="License"></a>

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
