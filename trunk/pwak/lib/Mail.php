<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK (PHP Web Application Kit) framework.
 *
 * PWAK is a php framework initially developed for the
 * {@link http://onlogistics.googlecode.com Onlogistics} ERP/Supply Chain
 * management web application.
 * It provides components and tools for developers to build complex web
 * applications faster and in a more reliable way.
 *
 * PHP version 5.1.0+
 * 
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @package   PWAK
 * @author    ATEOR dev team <dev@ateor.com>
 * @copyright 2003-2008 ATEOR <contact@ateor.com> 
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   SVN: $Id: Mail.php,v 1.4 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once('Mail.php');

/**
 * Classe de gestion de l'envoi de mail.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package    Framework
 */
class MailTools {
    /**
     * Envoie un mail aux destinataires $destinator avec le sujet $subject et le
     * body $body, si $isHTML vaut true, le mail est envoyé en html.
     * Cette fonction utilise la librairie PEAR::Mail.
     *
     * @static
     * @param mixed $recipients une chaine ou tableau contenant les destinataires
     * @param string $subject le sujet du mail
     * @param string $body le contenu du message
     * @param string $isHTML le corps est du html
     * @param mixed $attachment non vide si piece jointe
     * Possibilite de joindre un fichier ou une string en memoire; exple:
     * <code>
     * $attachment = array(
     *     'content' => $pdfContent,
     *     'contentType' => 'application/pdf',
     *     'fileName' => 'facture.pdf',
     *     'isFile' => false
     * );
     * </code>
     * @see http://pear.php.net/manual/fr/package.mail.mail-mime.addattachment.php
     * @param boolean $notification true pour accuse de reception
     * @param string $from mail de l'expediteur; si '', ce sera MAIL_SENDER
     * @return mixed true si le mail a pu être envoyé ou une exception sinon.
     */
    public static function send($recipients, $subject, $body, $isHTML=false,
        $attachment=array(), $notification=false, $from='')
    {
        // on instancie le "mailer", on utilise le proto smtp. Pour cela la const.
        // MAIL_SMTP_RELAY doit être définie dans le fichier de conf, si elle n'est
        // pas définie, la valeur est "localhost"
        $params = array();
        $params['host'] = defined('MAIL_SMTP_RELAY')?MAIL_SMTP_RELAY:'localhost';
        // on donne la possibilité de spécifier un port smtp different.
        if (defined('MAIL_SMTP_PORT')) {
            $params['port'] = MAIL_SMTP_PORT;
        }
        // on est capable d'utiliser l'auth smtp si les constantes necessaires
        // (MAIL_SMTP_USER et MAIL_SMTP_PWD) sont définies dans le fichier de conf
        if (defined('MAIL_SMTP_USER') && defined('MAIL_SMTP_PWD')) {
            $params['auth'] = true;
            $params['username'] = MAIL_SMTP_USER;
            $params['password'] = MAIL_SMTP_PWD;
        }
        //$recipients = 'bruno@ateor.com';  // Pour test
        // si le param $recipients est null on retourne, une exception
        if (is_null($recipients) || false == $recipients || empty($recipients)) {
            return new Exception(_('No addressee provided.'));
        }
        // si le param $recipients n'est pas un tableau on le converti
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }
        // si on n'est pas en prod, il ne faut surtout pas envoyer les mails aux
        // vrais destinataires, on l'envoi seulement à l'e-mail défini par la
        // constante MAIL_DEV
        if (DEV_VERSION) {
            $recipients = array(MAIL_DEV);
        }
        // construction des headers du mail
        $headers = array();
        $headers['From'] = (empty($from))?MAIL_SENDER:$from;
        $headers['Subject'] = $subject;
        $headers['To'] = implode(', ', $recipients);
        $headers['X-Onlogistics'] = 1;
        // Accuse de reception
        if ($notification) {
            $headers['Disposition-Notification-To'] = $from;
        }
        // si le mail est en html il faut spécifier ces en-têtes:
        if ($isHTML) {
            $headers['MIME-Version'] = '1.0';
            $headers['Content-Type'] = 'text/html; charset=iso-8859-1';
        }
        if (!empty($attachment)) {
            // XXX en attendant le patchage de Mail::mime
            error_reporting(E_ALL & ~E_NOTICE);
            require_once('Mail/mime.php');
            $mailMime = new Mail_mime();
            if ($isHTML) {
                $mailMime->setTXTBody(strip_tags($body));
                $mailMime->setHTMLBody($body); 
            } else {
                $mailMime->setTXTBody($body);
            }
            if (!isset($attachment['contentType'])) {
                $attachment['contentType'] = 'application/octet-stream';
            }
            if (!isset($attachment['fileName'])) {
                $attachment['fileName'] = '';
            }
            if (!isset($attachment['isFile'])) {
                $attachment['isFile'] = true;
            }
            $mailMime->addAttachment(
                $attachment['content'],
                $attachment['contentType'],
                $attachment['fileName'],
                $attachment['isFile']
            );
            $body = $mailMime->get();
            $headers = $mailMime->headers($headers);
            // XXX en attendant le patchage de Mail::mime
            error_reporting(E_ALL);
        }
        // on instancie notre mailer en lui passant la conf construite plus haut
        // et on envoie du mail (doit retourner true ou un objet PEAR Error)
        $mailer = Mail::factory('smtp', $params);
        $result = $mailer->send($recipients, $headers, $body);
        // on loggue les envois/echecs
        $logger = Tools::loggerFactory();
        $recs = implode(', ', $recipients);
        if ($result) {
            $logger->log("Mail sent: $recs", PEAR_LOG_NOTICE);
        } else {
            $logger->log("ERROR, mail not sent: $recs. " . $result->getMessage(),
                PEAR_LOG_ALERT);
        }
        return $result;
    }
}

?>
