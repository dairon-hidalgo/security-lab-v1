<?php

declare(strict_types=1);

return [
    'sections' => [
        [
            'label' => 'Principal',
            'items' => [
                [
                    'label' => 'Panel de control',
                    'url' => '/panel',
                    'icon' => 'home',
                ],
                [
                    'label' => 'Cola de tickets',
                    'url' => '/tickets',
                    'icon' => 'activity',
                ],
            ],
        ],
        [
            'label' => 'Mesa de servicio',
            'items' => [
                [
                    'label' => 'Tickets',
                    'url' => '/tickets/adjuntos',
                    'icon' => 'upload',
                ],
                [
                    'label' => 'Documentación',
                    'url' => '/soporte/documentacion',
                    'icon' => 'folder',
                ],
                [
                    'label' => 'Comentarios',
                    'url' => '/soporte/comentarios',
                    'icon' => 'users',
                ],

                [
                    'label' => 'Anuncios',
                    'url' => '/soporte/anuncios',
                    'icon' => 'triangle-alert',
                ],
            ],
        ],
        [
            'label' => 'Directorio',
            'items' => [
                [
                    'label' => 'Usuarios',
                    'url' => '/directorio/usuarios',
                    'icon' => 'database',
                ],
                [
                    'label' => 'Consulta de datos',
                    'url' => '/directorio/consulta',
                    'icon' => 'activity',
                ],
                [
                    'label' => 'Verificación',
                    'url' => '/directorio/verificar',
                    'icon' => 'eye',
                ],
            ],
        ],
        [
            'label' => 'Operaciones',
            'items' => [
                [
                    'label' => 'Diagnóstico de red',
                    'url' => '/red/diagnostico',
                    'icon' => 'terminal',
                ],
                [
                    'label' => 'Búsqueda de ayuda',
                    'url' => '/soporte/buscar',
                    'icon' => 'code',
                ],
                [
                    'label' => 'Registro de accesos',
                    'url' => '/seguridad/accesos',
                    'icon' => 'lock',
                ],
            ],
        ],
    ],
    'ticket_statuses' => [
        'abierto' => ['label' => 'Abierto', 'class' => 'status-open'],
        'en proceso' => ['label' => 'En proceso', 'class' => 'status-in-progress'],
        'resuelto' => ['label' => 'Resuelto', 'class' => 'status-resolved'],
    ],
    'scenarios' => [
        'Autenticación' => 'Accesos y control de sesión',
        'Red y conectividad' => 'Diagnóstico y ejecución remota',
        'Documentación' => 'Consulta de recursos internos',
        'Directorio' => 'Consulta de registros',
        'Archivos' => 'Carga de adjuntos',
        'Atención al usuario' => 'Búsqueda y tablón de soporte',
        'Navegador' => 'Anuncios y contenido dinámico',
        'Auditoría' => 'Registro de eventos',
    ],
];
