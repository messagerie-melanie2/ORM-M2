<?php

/**
 * Ce fichier est développé pour la gestion des API de la librairie Mélanie2
 * Ces API permettent d'accéder à la librairie en REST
 *
 * ORM API Copyright © 2022  Groupe MCD/MTE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Configuration du routing
 * Si une route n'est pas présente dans routing elle ne sera pas utilisable
 * Par défaut la classe utilisée correspond au nom de la route avec une majuscule
 * La configuration est récursive et permet d'avoir du routing sur une route
 * 
 * @var array
 */
$routing = [
    'user'          => [
        'methods'   => [
            'GET'       => true,
        ],
        'routing' => [
            'calendars' => [
                'methods'   => [
                    'GET'       => true,
                ],
                'routing' => [
                    'default' => [
                        'class' => 'UserCalendars',
                        'methods'   => [
                            'GET'       => 'default',
                        ],
                    ],
                    'shared' => [
                        'class' => 'UserCalendars',
                        'methods'   => [
                            'GET'       => 'shared',
                        ],
                    ],
                ],
            ],
            'addressbooks' => [
                'methods'   => [
                    'GET'       => true,
                ],
                'routing' => [
                    'default' => [
                        'class' => 'UserAddressbooks',
                        'methods'   => [
                            'GET'       => 'default',
                        ],
                    ],
                    'shared' => [
                        'class' => 'UserAddressbooks',
                        'methods'   => [
                            'GET'       => 'shared',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'calendar'      => [
        'methods'   => [
            'GET'       => true,
            'POST'      => true,
            'DELETE'    => true,
        ],
        'routing' => [
            'events' => [
                'class' => 'Calendar',
                'methods'   => [
                    'GET'       => 'events',
                ],
            ],
            'shares' => [
                'class' => 'Calendar',
                'methods'   => [
                    'GET'       => 'shares',
                ],
            ],
            'share' => [
                'methods'   => [
                    'GET'       => true,
                    'POST'      => true,
                    'DELETE'    => true,
                ],
            ],
        ],
    ],
    'event'         => [
        'methods'   => [
            'GET'       => true,
            'POST'      => true,
            'DELETE'    => true,
        ],
    ],
    'attachment'         => [
        'methods'   => [
            'GET'       => true,
        ],
    ],
    'addressbook'      => [
        'methods'   => [
            'GET'       => true,
            'POST'      => true,
            'DELETE'    => true,
        ],
        'routing' => [
            'contacts' => [
                'class' => 'Addressbook',
                'methods'   => [
                    'GET'       => 'contacts',
                ],
            ],
            'groups' => [
                'class' => 'Addressbook',
                'methods'   => [
                    'GET'       => 'groups',
                ],
            ],
            'shares' => [
                'class' => 'Addressbook',
                'methods'   => [
                    'GET'       => 'shares',
                ],
            ],
            'share' => [
                'methods'   => [
                    'GET'       => true,
                    'POST'      => true,
                    'DELETE'    => true,
                ],
            ],
        ],
    ],
    'contact'         => [
        'methods'   => [
            'GET'       => true,
            'POST'      => true,
            'DELETE'    => true,
        ],
    ],
    'folder'         => [
        'methods'   => [
            'GET'       => true,
            'POST'      => true,
            'DELETE'    => false,
        ],
        'routing' => [
            'children' => [
                'class' => 'Folder',
                'methods'   => [
                    'GET'       => 'children',
                ],
            ],
            'search' => [
                'class' => 'Folder',
                'methods'   => [
                    'GET'       => 'search',
                ],
            ], 
            'export' => [
                'class' => 'Folder',
                'methods'   => [
                    'GET'       => 'export',
                    'POST'       => 'export',
                ],
            ],
        ]
    ],
    'group'         => [
        'methods'   => [
            'GET'       => true,
            'POST'      => true,
            'DELETE'    => false,
        ],
    ],
    'taskslist'     => [],
    'task'          => [],
    'share'         => [],
];
