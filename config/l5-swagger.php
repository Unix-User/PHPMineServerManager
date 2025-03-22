<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'L5 Swagger UI',
            ],

            'routes' => [
                /*
                 * Rota para acessar a interface de documentação da API
                */
                'api' => 'api/documentation',
            ],
            'paths' => [
                /*
                 * Edite para incluir o URL completo na interface do usuário para ativos
                */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * Nome do arquivo da documentação JSON gerada
                */
                'docs_json' => 'api-docs.json',

                /*
                 * Nome do arquivo da documentação YAML gerada
                */
                'docs_yaml' => 'api-docs.yaml',

                /*
                * Defina isso como `json` ou `yaml` para determinar qual arquivo de documentação usar na interface do usuário
                */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Caminhos absolutos para o diretório onde as anotações do swagger são armazenadas.
                */
                'annotations' => [
                    base_path('app'),
                    base_path('storage/api-docs'),
                ],

            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            /*
             * Rota para acessar as anotações do swagger analisadas.
            */
            'docs' => 'docs',

            /*
             * Rota para o callback de autenticação Oauth2.
            */
            'oauth2_callback' => 'api/oauth2-callback',

            /*
             * O middleware permite prevenir o acesso inesperado à documentação da API
            */
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],

            /*
             * Opções do grupo de rotas
            */
            'group_options' => [],
        ],

        'paths' => [
            /*
             * Caminho absoluto para o local onde as anotações analisadas serão armazenadas
            */
            'docs' => storage_path('api-docs'),

            /*
             * Caminho absoluto para o diretório onde exportar as visualizações
            */
            'views' => base_path('resources/views/vendor/l5-swagger'),

            /*
             * Edite para definir o caminho base da API
            */
            'base' => env('L5_SWAGGER_BASE_PATH', null),

            /*
             * Edite para definir o caminho onde os ativos do swagger ui devem ser armazenados
            */
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

            /*
             * Caminho absoluto para os diretórios que devem ser excluídos da varredura
             * @deprecated Por favor, use `scanOptions.exclude`
             * `scanOptions.exclude` substitui isso
            */
            'excludes' => [],
        ],

        'scanOptions' => [
            /**
             * analisador: padrão para \OpenApi\StaticAnalyser .
             *
             * @see \OpenApi\scan
             */
            'analyser' => null,

            /**
             * análise: padrão para uma nova \OpenApi\Analysis .
             *
             * @see \OpenApi\scan
             */
            'analysis' => null,

            /**
             * Classes de processadores de caminho de consulta personalizados.
             *
             * @link https://github.com/zircote/swagger-php/tree/master/Examples/schema-query-parameter-processor
             * @see \OpenApi\scan
             */
            'processors' => [
                // new \App\SwaggerProcessors\SchemaQueryParameter(),
            ],

            /**
             * padrão: string       $pattern Padrão de arquivo(s) para varrer (padrão: *.php) .
             *
             * @see \OpenApi\scan
             */
            'pattern' => null,

            /*
             * Caminho absoluto para os diretórios que devem ser excluídos da varredura
             * @note Esta opção substitui `paths.excludes`
             * @see \OpenApi\scan
            */
            'exclude' => [],

            /*
             * Permite gerar especificações para OpenAPI 3.0.0 ou OpenAPI 3.1.0.
             * Por padrão, a especificação estará na versão 3.0.0
             */
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION),
        ],

        /*
         * Definições de segurança da API. Será gerado no arquivo de documentação.
        */
        'securityDefinitions' => [
            'securitySchemes' => [
                /*
                 * Exemplos de esquemas de segurança
                */
                /*
                'api_key_security_example' => [ // Nome único de segurança
                    'type' => 'apiKey', // O tipo do esquema de segurança. Os valores válidos são "basic", "apiKey" ou "oauth2".
                    'description' => 'Uma breve descrição para o esquema de segurança',
                    'name' => 'api_key', // O nome do cabeçalho ou parâmetro de consulta a ser usado.
                    'in' => 'header', // A localização da chave da API. Os valores válidos são "query" ou "header".
                ],
                'oauth2_security_example' => [ // Nome único de segurança
                    'type' => 'oauth2', // O tipo do esquema de segurança. Os valores válidos são "basic", "apiKey" ou "oauth2".
                    'description' => 'Uma breve descrição para o esquema de segurança oauth2.',
                    'flow' => 'implicit', // O fluxo usado pelo esquema de segurança OAuth2. Os valores válidos são "implicit", "password", "application" ou "accessCode".
                    'authorizationUrl' => 'http://example.com/auth', // O URL de autorização a ser usado para (implicit/accessCode)
                    //'tokenUrl' => 'http://example.com/auth' // O URL de autorização a ser usado para (password/application/accessCode)
                    'scopes' => [
                        'read:projects' => 'ler seus projetos',
                        'write:projects' => 'modificar projetos em sua conta',
                    ]
                ],
                */

                /* Suporte Open API 3.0
                'passport' => [ // Nome único de segurança
                    'type' => 'oauth2', // O tipo do esquema de segurança. Os valores válidos são "basic", "apiKey" ou "oauth2".
                    'description' => 'Segurança oauth2 do Laravel passport.',
                    'in' => 'header',
                    'scheme' => 'https',
                    'flows' => [
                        "password" => [
                            "authorizationUrl" => config('app.url') . '/oauth/authorize',
                            "tokenUrl" => config('app.url') . '/oauth/token',
                            "refreshUrl" => config('app.url') . '/token/refresh',
                            "scopes" => []
                        ],
                    ],
                ],
                'sanctum' => [ // Nome único de segurança
                    'type' => 'apiKey', // Os valores válidos são "basic", "apiKey" ou "oauth2".
                    'description' => 'Digite o token no formato (Bearer <token>)',
                    'name' => 'Authorization', // O nome do cabeçalho ou parâmetro de consulta a ser usado.
                    'in' => 'header', // A localização da chave da API. Os valores válidos são "query" ou "header".
                ],
                */
            ],
            'security' => [
                /*
                 * Exemplos de seguranças
                */
                [
                    /*
                    'oauth2_security_example' => [
                        'read',
                        'write'
                    ],

                    'passport' => []
                    */
                ],
            ],
        ],

        /*
         * Defina isso como `true` no modo de desenvolvimento para que os documentos sejam regenerados a cada solicitação
         * Defina isso como `false` para desativar a geração de swagger na produção
        */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),

        /*
         * Defina isso como `true` para gerar uma cópia da documentação no formato yaml
        */
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        /*
         * Edite para confiar no endereço IP do proxy - necessário para o balanceador de carga AWS
         * string[]
        */
        'proxy' => false,

        /*
         * O plugin de configurações permite buscar configurações externas em vez de passá-las para o SwaggerUIBundle.
         * Veja mais em: https://github.com/swagger-api/swagger-ui#configs-plugin
        */
        'additional_config_url' => null,

        /*
         * Aplique uma ordenação à lista de operações de cada API. Pode ser 'alpha' (ordena os caminhos alfabeticamente),
         * 'method' (ordena pelo método HTTP).
         * O padrão é a ordem retornada pelo servidor inalterada.
        */
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

        /*
         * Passe o parâmetro validatorUrl para o SwaggerUi init no lado JS.
         * Um valor nulo aqui desativa a validação.
        */
        'validator_url' => null,

        /*
         * Parâmetros de configuração do Swagger UI
        */
        'ui' => [
            'display' => [
                /*
                 * Controla a configuração de expansão padrão para as operações e tags. Pode ser :
                 * 'list' (expande apenas as tags),
                 * 'full' (expande as tags e operações),
                 * 'none' (não expande nada).
                 */
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),

                /**
                 * Se definido, habilita a filtragem. A barra superior mostrará uma caixa de edição que
                 * você pode usar para filtrar as operações marcadas que são mostradas. Pode ser
                 * Boolean para habilitar ou desabilitar, ou uma string, caso em que a filtragem
                 * será habilitada usando essa string como a expressão de filtro. A filtragem
                 * é correspondência sensível a maiúsculas e minúsculas correspondendo à expressão de filtro em qualquer lugar dentro
                 * da tag.
                 */
                'filter' => env('L5_SWAGGER_UI_FILTERS', true), // true | false
            ],

            'authorization' => [
                /*
                 * Se definido como true, persiste os dados de autorização, e eles não seriam perdidos no fechamento/refresh do navegador
                 */
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),

                'oauth2' => [
                    /*
                    * Se definido como true, adiciona PKCE ao fluxo AuthorizationCodeGrant
                    */
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],
        /*
         * Constantes que podem ser usadas em anotações
         */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://my-default-host.com'),
        ],
    ],
];
