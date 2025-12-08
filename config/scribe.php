<?php

use Knuckles\Scribe\Extracting\Strategies;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Config\AuthIn;
use function Knuckles\Scribe\Config\{removeStrategies, configureStrategy};

// Only the most common configs are shown. See the https://scribe.knuckles.wtf/laravel/reference/config for all.

return [
    'title' => 'Aonsoku Podcasts API Documentation',

    'description' => 'API for managing podcasts, episodes and tracking playback progress.',

    'intro_text' => <<<INTRO
        Welcome to the Podcasts API. This API allows you to search for podcasts, view episode details, and track your listening progress.

        <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
        You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>
    INTRO,

    'base_url' => config("app.url"),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
        ],
    ],

    'type' => 'external_laravel',

    'theme' => 'scalar',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],

    'external' => [
        'html_attributes' => [
            'data-configuration' => e(json_encode([
                'theme' => 'deepSpace',
                'defaultOpenAllTags' => true,
                'hideClientButton' => true,
                'expandAllResponses' => true,
                'hideDarkModeToggle' => true,
                'documentDownloadType' => 'none',
                'showDeveloperTools' => 'never',
            ])),
        ]
    ],

    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    'auth' => [
        'enabled' => false,
    ],

    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],

    'openapi' => [
        'enabled' => true,
        'version' => '3.0.3',
        'overrides' => [
            'components.securitySchemes' => [
                'username' => [
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'APP-USERNAME',
                ],
                'serverUrl' => [
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'APP-SERVER-URL',
                ],
            ],
            'security' => [
                [
                    'username' => [],
                    'serverUrl' => [],
                ],
            ],
        ],
        'generators' => [],
    ],

    'groups' => [
        'default' => 'Endpoints',
        'order' => [
            'Podcasts',
            'Episodes',
        ],
    ],

    'logo' => null,

    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only: ['GET *'],
                config: [
                    'app.debug' => false,
                ]
            )
        ),
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ]
    ],

    'database_connections_to_transact' => [config('database.default')],

    'fractal' => [
        'serializer' => null,
    ],
];
