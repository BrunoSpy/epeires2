<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Core\Service;

use Application\Entity\Organisation;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Message;

/**
 * Class EmailService
 * @package Core\Service
 */
class EmailService
{

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param Organisation|null $org
     * @return mixed|void
     */
    private function getEmailFrom(?Organisation $org)
    {
        if($org !== null && $org->getEmailFrom() !== null && strlen($org->getEmailFrom()) > 0) {
            return $org->getEmailFrom();
        } else {
            return $this->config['emailfrom'] ?? null;
        }
    }


    /**
     * @param $emailTo
     * @param $subject
     * @param Message $content
     * @param Organisation|null $fromOrg
     * @param null $from
     */
    public function sendEmailTo($emailTo, $subject, Message $content, ?Organisation $fromOrg, $from = null)
    {
        $emailFrom = $from == null ? $this->getEmailFrom($fromOrg) : $from;
        if($emailFrom == null) {
            throw new \RuntimeException("Aucun expéditeur, impossible d'envoyer l'email.");
        }

        if(!array_key_exists('smtp', $this->config)) {
            throw new \RuntimeException('Aucune configuration SMTP trouvée');
        }

        $message = new \Laminas\Mail\Message();
        $message->addTo($emailTo)
            ->addFrom($emailFrom)
            ->setSubject($subject)
            ->setBody($content);

        $transport = new Smtp();

        $transportOptions = new SmtpOptions($this->config['smtp']);

        if($fromOrg !== null) {
            if($fromOrg->getEmailAccount() && $fromOrg->getEmailPassword()) {
                $password = openssl_decrypt(
                base64_decode($fromOrg->getEmailPassword()),
                "AES-256-CBC",
                $this->config['secret_key'],
                0,
                $this->config['secret_init']);
            $transportOptions->setConnectionConfig(array(
                'username' => $fromOrg->getEmailAccount(),
                'password' => $password,
                'ssl' => 'tls'
            ));
            }
        }

        $transport->setOptions($transportOptions);
        $transport->send($message);

    }
}