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

namespace Controller;

/**
 * Classe de traitement pour les pièces jointe dans les événements
 * 
 * @package Controller
 */
class Attachment extends Controller {
    /**
     * Récupération d'une pièce jointe
     */
    public static function get()
    {
        \Lib\Log::LogTrace("get(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['path', 'name'], \Lib\Request::INPUT_GET)) {
            
            $attachment = \Lib\Objects::gi()->attachment();
            $attachment->name = \Lib\Request::getInputValue('name', \Lib\Request::INPUT_GET);
            $attachment->path = \Lib\Request::getInputValue('path', \Lib\Request::INPUT_GET);

            if ($attachment->load()) {
                ob_end_clean();

                $filename = addcslashes($attachment->name, '"');
                $disposition = !empty($_GET['_download']) ? 'attachment' : 'inline';

                // Envoyer la pièce jointe
                header('Content-Type: ' . $attachment->contenttype); 
                header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . $attachment->size);

                echo $attachment->data;

                exit;
            }
            else {
                \Lib\Response::error("Attachment not found");
            }
        }
    }

    /**
     * Mapping pour les attachments
     * 
     * @param array $attachments Liste des pièces jointes ORM
     */
    public function getAttachments($attachments)
    {
        $ret = null;
        if (is_array($attachments)) {
            $ret = [];
            foreach ($attachments as $attachment) {
                $att = [
                    'name'          => $attachment->name,
                    'path'          => $attachment->path,
                    'contenttype'   => $attachment->contenttype,
                    'size'          => $attachment->size,
                    'modified'      => $attachment->modified,
                    'owner'         => $attachment->owner,
                ];

                if (!empty($_GET['_get_attachments_data'])) {
                    $att['encoding'] = 'base64';
                    $att['data'] = base64_encode($attachment->data);
                }

                $ret[] = $att;
            }
        }
        return $ret;
    }

    /**
     * Mapping pour les attachments
     * 
     * @param array $attachments Liste des pièces jointes JSON
     */
    public function setAttachments($attachments)
    {
        $ret = null;
        if (is_array($attachments)) {
            $ret = [];
            foreach ($attachments as $attachment) {
                $att = \Lib\Objects::gi()->attachment();

                $att = \Lib\Mapping::set('attachment', $att, $attachment);

                $att->data = base64_decode($attachment['data']);
                $att->isfolder = false;

                $att->save();

                $ret[] = $att;
            }
        }
        return $ret;
    }
}