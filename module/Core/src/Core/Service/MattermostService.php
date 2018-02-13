<?php
/*
Copyright (C) 2018 Bruno Spyckerelle

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
namespace Core\Service;

use MattermostMessenger\Service\MattermostService as Mattermost;
use Gnello\Mattermost\Driver;
use Pimple\Container;

class MattermostService extends Mattermost
{

    private $auth;

    private $config;

    public function __construct($config, $auth)
    {
        $this->mattermost = $config['mattermost'];
        $this->auth = $auth;
        $this->config = $config;
    }

    protected function getClient()
    {
        if($this->client == null) {
            if ($this->auth->hasIdentity()) {
                $user = $this->auth->getIdentity();
                $password = openssl_decrypt(
                    base64_decode($user->getMattermostPassword()),
                    "AES-256-CBC",
                    $this->config['secret_key'],
                    0,
                    $this->config['secret_init']);
                $login = $user->getMattermostUsername();
                $containerOptions = array(
                    'driver' => array(
                        'url' => $this->mattermost['server_url'],
                        'login_id' => $login,
                        'password' => $password
                    )
                );
                if (array_key_exists('proxy', $this->mattermost)) {
                    $containerOptions['guzzle'] = array(
                        'proxy' => [
                            'http' => $this->mattermost['proxy'],
                            'https' => $this->mattermost['proxy'],
                        ]
                    );
                }
                $container = new Container($containerOptions);
                $this->client = new Driver($container);
                $result = $this->client->authenticate();
                if ($result->getStatusCode() == 200) {
                    //OK !
                    $this->myId = json_decode($result->getBody())->id;
                    $this->token = $result->getHeader('Token')[0];
                } else {
                    //TODO throw something or retry ?
                    error_log("Impossible de s'authentifier au serveur, erreur " . $result->getStatusCode());
                }
            } else {
                //error
            }
        }
        return $this->client;
    }
}