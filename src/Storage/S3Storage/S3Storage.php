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

namespace LibMelanie\Storage\S3Storage;

/**
 * Storage class
 * 
 * Cette classe est la classe dédiée au stockage des fichiers dans un bucket S3
 * 
 * @package LibMelanie
 * @subpackage S3Storage
 */

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use LibMelanie\Storage\IStorage;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use LibMelanie\Lib\MceObject;
use LibMelanie\Log\M2Log;

class S3Storage extends MceObject implements IStorage
{
    protected $filesystem;

    private static $instance = null;

    private function __construct(Filesystem $filesystem)
    {
        // Défini la classe courante
        $this->get_class = get_class($this);

        M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");

        $this->filesystem = $filesystem;
    }

    public static function getInstance(array $s3AdapterConfig)
    {
        if (self::$instance == null) {
            // check required config
            if (!isset($s3AdapterConfig['client'])) {
                throw new \InvalidArgumentException('Missing required `client` config');
            }
            if (!isset($s3AdapterConfig['bucket'])) {
                throw new \InvalidArgumentException('Missing required `bucket` config');
            }

            $client = new S3Client($s3AdapterConfig['client']);
            $adapter = new AwsS3V3Adapter($client, $s3AdapterConfig['bucket']);

            self::$instance = new S3Storage(new Filesystem($adapter));
        }

        return self::$instance;
    }

    public function write($path, $contents)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->write()");

        $response = false;
        try {
            $this->filesystem->write($path, $contents);
            $response = true;
        } catch (UnableToWriteFile $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->write(" . $path . ", fileContent) Exception: " . $exception);
        }

        return $response;
    }

    public function read($path)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->read()");

        $response = null;
        try {
            $response = $this->filesystem->read($path);
        } catch (UnableToReadFile $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->read() Exception: " . $exception);
        }

        return $response;
    }

    public function delete($path)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");

        $response = false;
        try {
            $this->filesystem->delete($path);
            $response = true;
        } catch (UnableToDeleteFile $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->delete(" . $path . ") Exception: " . $exception);
        }

        return $response;
    }
}

