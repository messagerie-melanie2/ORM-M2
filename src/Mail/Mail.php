<?php

/**
 * Mail library, used to send mail with the ORM
 * 
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
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

namespace LibMelanie\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;
use PHPMailer\PHPMailer\Exception;

/**
 * Classe d'envoi de mail via la lib PHPMailer
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Mail
 */
class Mail
{
    /**
     * @var PHPMailer Instance de PHPMailer
     */
    protected static $_mail;

    /**
     * Initialisation de PHPMailer
     */
    protected static function Init(&$from = '') {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Mail->Init()");

        self::$_mail = new PHPMailer();

        // Utiliser SMTP pour l'envoi
        self::$_mail->isSMTP();

        // Récupération de la configuration des senders
        $senders = Config::get(Config::SENDERS, [], 'Mail');

        // Récupération du sender en fonction du from ou du default
        if (isset($senders[$from])) {
            $sender = $senders[$from];
        }
        else {
            $sender = $senders['default'];
        }

        // Gestion du from
        if (isset($sender['from'])) {
            $from = $sender['from'];
        }

        // Configuration de l'envoi
        self::$_mail->Host = $sender['host'] ?: 'localhost';
        self::$_mail->Port = $sender['port'] ?: 25;

        // Utilisation de l'authentification
        if ($sender['smtpAuth']) {
            self::$_mail->SMTPAuth = true;
            self::$_mail->Username = $sender['username'];
            self::$_mail->Password = $sender['password'];
        }

        // Debugging
        if (Config::get(Config::DEBUG, false, 'Mail')) {
            self::$_mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        else {
            self::$_mail->SMTPDebug = SMTP::DEBUG_OFF;
        }

        // Charset
        if (isset($sender['charset'])) {
            self::$_mail->CharSet = $sender['charset'];
        }
        else {
            self::$_mail->CharSet = PHPMailer::CHARSET_UTF8;
        }

        // Secure
        if (isset($sender['smtpSecure'])) {
            switch ($sender['smtpSecure']) {
                case 'ssl':
                    self::$_mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;
                case 'tls':
                default:
                    self::$_mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    break;
            }
        }

        // Options
        if (isset($sender['options'])) {
            self::$_mail->SMTPOptions = $sender['options'];
        }
    }

    /**
     * Envoi un mail au format php mail()
     * utilise PHPMailer si trouvé, sinon mail()
     * 
     * @param string $from Adresse mail de l'expéditeur
     * @param string $to Adresse mail du destinataire
     * @param string $subject Sujet du mail
     * @param string $message Contenu du mail
     * @param array|string $additional_headers En-têtes supplémentaires
     * @param string $additional_params Paramètres supplémentaires
     * 
     * @return bool
     */
    public static function mail($to, $subject, $message, $additional_headers = [], $additional_params = "", $from = '') {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Mail->mail($from, $to)");

        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $replyTo = null;
            $htmlBody = null;
            $plainBody = null;

            // Convertir les headers en string
            if (is_string($additional_headers)) {
                $additional_headers = explode("\r\n", $additional_headers);
            }
            
            // Récupérer les headers From et Reply-To
            if (isset($additional_headers)) {
                foreach ($additional_headers as $key => $header) {
                    if (strpos($header, 'From:') === 0) {
                        $from = trim(str_replace('From:', '', $header));
                        unset($additional_headers[$key]);
                    }
                    else if (strpos($header, 'Reply-To:') === 0) {
                        $replyTo = trim(str_replace('Reply-To:', '', $header));
                        unset($additional_headers[$key]);
                    }
                    else if (strpos($header, 'Content-Type:') === 0) {
                        if (strpos($header, 'text/html') !== false) {
                            $htmlBody = $message;
                            $plainBody = null; 
                        }
                        else if (strpos($header, 'text/plain') !== false) {
                            $plainBody = $message;
                            $htmlBody = null;
                        }
                        unset($additional_headers[$key]);
                    }
                    else if (strpos(strtolower($header), 'mime-version:') === 0
                            || strpos(strtolower($header), 'content-transfer-encoding:') === 0) {
                        unset($additional_headers[$key]);
                    }
                }
            }

            // Positionnement du htmlBody
            if (!isset($htmlBody) && !isset($plainBody)) {
                $htmlBody = $message;
            }

            // Retrouver le from depuis les params
            if (empty($from)) { 
                if (strpos($additional_params, '-f') !== false) {
                    $params = explode('-', $additional_params);
                    foreach ($params as $param) {
                        if (strpos($param, 'f ') === 0) {
                            $from = trim(substr($param, 2));
                        }
                    }
                }
            }

            // Traitement du to
            if (is_string($to) && strpos($to, ',') !== false) {
                $to = array_map('trim', explode(',', $to));
            }

            // Envoyer le message via PHPMailer
            return self::Send($from, $to, $subject, $htmlBody, $plainBody, [], [], $replyTo, $additional_headers);
        }
        else {
            M2Log::Log(M2Log::LEVEL_DEBUG, "Mail->mail() : PHPMailer not found, use mail() instead");

            return mail($to, $subject, $message, $additional_headers, $additional_params);
        }
    }

    /**
     * Envoi un mail via PHPMailer
     * 
     * @param string $from Adresse mail de l'expéditeur
     * @param string|array $to Adresse mail du ou des destinataires
     * @param string $subject Sujet du mail
     * @param string $htmlBody Corps du mail en HTML
     * @param string $plainBody Corps du mail en texte brut
     * @param array $cc Liste des destinataires en copie
     * @param array $bcc Liste des destinataires en copie cachée
     * @param string $replyTo Information de Reply-To
     * @param array $additional_headers Headers supplémentaires a ajouter au mail
     * @param string $htmlFile Chemin vers un fichier HTML pour le contenu du mail, remplace $htmlBody
     * @param string $imagesFolder Chemin vers un dossier contenant les pièces jointes images à intégrer au body en HTML
     * @param array $attachments Liste des pièces jointes à ajouter au mail, soit liste de string pour le chemin, soit liste d'array ['name' => '', 'path' => '']
     * 
     * @return boolean
     */
    public static function Send(
            $from = '', 
            $to, 
            $subject, 
            $htmlBody = null, 
            $plainBody = null, 
            $cc = [], 
            $bcc = [], 
            $replyTo = '', 
            $additional_headers = [],
            $htmlFile = null,
            $imagesFolder = null,
            $attachments = []) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Mail->Send($from, $to)");
        try {
            // Initialiser PHPMailer
            self::Init($from);

            // Gestions de l'expéditeur
            self::$_mail->setFrom(self::getEmail($from), self::getName($from));

            if (isset($replyTo)) {
                self::$_mail->addReplyTo(self::getEmail($replyTo), self::getName($replyTo));
            }

            // Gestion des destinataires
            if (is_string($to)) {
                $to = [$to];
            }

            foreach ($to as $recipient) {
                self::$_mail->addAddress(self::getEmail($recipient), self::getName($recipient));
            }

            // Cc
            if (is_string($cc)) {
                $cc = [$cc];
            }

            foreach ($cc as $recipient) {
                self::$_mail->addCC(self::getEmail($recipient), self::getName($recipient));
            }

            // Bcc
            if (is_string($bcc)) {
                $bcc = [$bcc];
            }

            foreach ($bcc as $recipient) {
                self::$_mail->addBCC(self::getEmail($recipient), self::getName($recipient));
            }

            // Gestion du sujet
            self::$_mail->Subject = $subject;

            // Gestion du body
            if (isset($htmlFile)) {
                // Message HTML depuis un fichier
                self::$_mail->msgHTML(file_get_contents($htmlFile), $imagesFolder ?: __DIR__);
            }
            else if (isset($htmlBody) && isset($plainBody)) {
                // Message HTML avec alternative plain text
                self::$_mail->isHTML(true);
                self::$_mail->Body      = $htmlBody;
                self::$_mail->AltBody   = $plainBody;
            }
            else if (isset($htmlBody)) {
                // Message HTML sans alternative, on demande a PHPMailer de le calculer
                self::$_mail->msgHTML($htmlBody, $imagesFolder ?: __DIR__);
            }
            else if (isset($plainBody)) {
                // Message en texte brut
                self::$_mail->isHTML(false);
                self::$_mail->Body      = $plainBody;
            }
            else {
                // Si on est ici, c'est pas terrible
                return false;
            }

            // Ajouter des headers supplémentaires
            if (!empty($additional_headers) && is_array($additional_headers)) {
                foreach ($additional_headers as $header => $value) {
                    self::$_mail->addCustomHeader($header, $value);
                }
            }

            // Ajout des pièces jointes
            if (!empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        self::$_mail->addAttachment($attachment['path'], $attachment['name']);
                    }
                    else {
                        self::$_mail->addAttachment($attachment);
                    }
                }
            }

            // Envoi du message
            return self::$_mail->send();
        }
        catch (Exception $ex) {
            M2Log::Log(M2Log::LEVEL_ERROR, "Mail->Send() : PHPMailer/Exception : " . $ex->errorMessage());
        }
        catch (\Exception $ex) {
            M2Log::Log(M2Log::LEVEL_ERROR, "Mail->Send() : Exception : " . $ex->getMessage());
        }
        return false;
    }
        
    /**
     * Returns the whole MIME message. 
     * Includes complete headers and body. Only valid post preSend().
     */
    public static function getSentMIMEMessage() {
        if (isset(self::$_mail)) {
            return self::$_mail->getSentMIMEMessage();
        }
    }

    /**
     * Récupération de la dernirre erreur SMTP
     * 
     * @return string
     */
    public static function getLastError() {
        if (isset(self::$_mail)) {
            return self::$_mail->ErrorInfo;
        }
        else {
            return "";
        }
    }

    /**
     * Récupération du nom à partir de l'adresse mail
     * 
     * @param string $email Adresse mail
     * 
     * @return string
     */
    protected static function getName($email) {
        if (strpos($email, '<') !== false) {
            $name = trim(substr($email, 0, strpos($email, '<')));
        }
        else {
            $name = '';
        }

        return $name;
    }

    /**
     * Récupération de l'adresse mail à partir de l'adresse mail
     * 
     * @param string $email Adresse mail
     * 
     * @return string
     */
    protected static function getEmail($email) {
        if (strpos($email, '<') !== false) {
            $email = trim(substr($email, strpos($email, '<') + 1, strpos($email, '>') - strpos($email, '<') - 1));
        }

        return $email;
    }
}