<?php

/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2022 Groupe Messagerie/MTE
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace LibMelanie\Storage;

/**
 * Storage class
 * 
 * Cette classe est la classe de base pour les classes de stockage (s3, local, postgresql et Swift)
 * 
 * @package LibMelanie
 * @subpackage Storage
 */

use LibMelanie\Config\Config;
use LibMelanie\Storage\LocalStorage\LocalStorage;
use LibMelanie\Storage\S3Storage\S3Storage;
use LibMelanie\Storage\SQLStorage\SQLStorage;
use LibMelanie\Storage\SwiftStorage\SwiftStorage;

class Storage
{
    /**
     * S3 storage type
     *
     * @var string
     */
    const S3 = 'S3';

    /**
     * Local storage type
     * 
     * @var string
     */
    const LOCAL = 'LOCAL';

    /**
     * Swift storage type
     *  
     * @var string
     */
    const SWIFT = 'SWIFT';

    /**
     * Postgresql storage type
     * 
     * @var string
     */
    const POSTGRESQL = 'POSTGRESQL';

    /**
     * Get a storage instance based on the provided storage type or configuration.
     *
     * @param string|null $storageType The storage type to retrieve (or null to use the configured type).
     * @return mixed|null A storage instance based on the storage type, or null if the type is not recognized.
     */
    public static function getStorage(string $storageType = null)
    {
        $storageType ?? $storageType = Config::get('storage_type');

        switch ($storageType) {
            case Storage::S3:
                return S3Storage::getInstance([
                    'client' => [
                        'region' => 'us-west-2',
                        'version' => '2006-03-01',
                        'endpoint' => 'http://172.27.161.177:9000',
                        'use_path_style_endpoint' => true,
                        // Set this to true for Minio
                        'credentials' => [
                            'key' => 'wlfvs7Ifm5UvA9KsVH1L',
                            'secret' => '6IsnlMUut0zM0NoDRpR7dCuM8H1hXgocOn418zdj',
                        ],
                    ],
                    'bucket' => 'test',
                ]); // TODO: config: Config::get('s3')
            case Storage::LOCAL:
                return LocalStorage::getInstance(
                    __DIR__ . "/tests/files/"
                ); // TODO: config: Config::get('local')
            case Storage::SWIFT:
                return SwiftStorage::getInstance(
                    [
                        'authUrl' => 'https://auth.cloud.ovh.net/v3/',
                        'region' => 'GRA3',
                        'user' => [
                            'id' => 'VoZGRAdnTyBeGyVQcyUl',
                            'password' => 'AGCXoFcQw7c6nHlvMf7q87LsKv1dQ8l11u0KfYFR'
                        ],
                        'scope' => ['project' => ['id' => 'your-project-id']]
                    ]
                ); // TODO: config: Config::get('swift')
            case Storage::POSTGRESQL:
                return SQLStorage::getInstance();
            // TODO: config: Config::get('postgresql')
            default:
                return null;
        }
    }
}