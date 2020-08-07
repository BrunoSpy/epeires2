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

class NOTAMWebService
{
    const URL_NOTAMWEB = "http://notamweb.aviation-civile.gouv.fr/Script/IHM/Bul_Aerodrome.php?AERO_Langue=FR";
    const CURL_TIMEOUT = 5;
    const AERO_Rayon = 0;
    const AERO_Plafond = 30;

    private 
        $em, $config, 
        $timeout, $proxy, 
        $aero_rayon, $aero_plafond;

    public function __construct($em, $config)
    {
        $this->em = $em;
        $this->config = $config;

        if (isset($this->config['btiv']['af_notam_max_loading_seconds'])) {
            $this->timeout = $this->config['btiv']['af_notam_max_loading_seconds'];
        }
        else {
            $this->timeout = self::CURL_TIMEOUT;
        }

        $this->proxy = '';
        if(array_key_exists('proxy', $this->config)) {
            if (array_key_exists('proxy_host', $this->config['proxy'])) {
                $this->proxy = "http://" . $this->config['proxy']['proxy_host'];
                if (array_key_exists('proxy_port', $this->config['proxy'])) {
                    $this->proxy .= ":" . $this->config['proxy']['proxy_port'];
                }
            }
        }

        if (isset($this->config['btiv']['af_rayon'])) {
            $this->aero_rayon = $this->config['btiv']['af_rayon'];
        } else {
            $this->aero_rayon = self::AERO_Rayon;
        }

        if (isset($this->config['btiv']['af_plafond'])) {
            $this->aero_plafond = $this->config['btiv']['af_plafond'];
        } else {
            $this->aero_plafond = self::AERO_Plafond;
        }
    }

    public function testNOTAMWeb()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::URL_NOTAMWEB,
            CURLOPT_PROXY => $this->proxy,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ]);
        session_write_close();

        $output = curl_exec($curl);
        $res = ($output === false) ? 0 : 1;

        curl_close($curl);
        return $res;
    }

    public function getFromCode($code)
    {
        $fields = [
            'AERO_CM_GPS' => '2',
            'AERO_CM_INFO_COMP' => '1',
            'AERO_CM_REGLE' => '1',
            'AERO_Date_DATE' => urlencode((new \DateTime())->format('Y/m/d')),
            'AERO_Date_HEURE' => urlencode((new \DateTime())->format('H:i')),
            'AERO_Duree' => '24',
            'AERO_Langue' => 'FR',
            'AERO_Rayon' => $this->aero_rayon,
            'AERO_Plafond' => $this->aero_plafond,
            'AERO_Tab_Aero[0]' => $code,
            'AERO_Tab_Aero[1]' => '',
            'AERO_Tab_Aero[2]' => '',
            'AERO_Tab_Aero[3]' => '',
            'AERO_Tab_Aero[4]' => '',
            'AERO_Tab_Aero[5]' => '',
            'AERO_Tab_Aero[6]' => '',
            'AERO_Tab_Aero[7]' => '',
            'AERO_Tab_Aero[8]' => '',
            'AERO_Tab_Aero[9]' => '',
            'AERO_Tab_Aero[10]' => '',
            'AERO_Tab_Aero[11]' => '',
            'ModeAffichage' => 'COMPLET',
            'bImpression' => '',
            'bResultat' => 'true'
        ];

        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::URL_NOTAMWEB,
            CURLOPT_PROXY => $this->proxy,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
            CURLOPT_POST => $fields,
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ]);

        $content = "";
        $output = curl_exec($curl);
        if ($output !== false) {
            $content = preg_replace('/.*<body[^>]*>/msi','',$output);
            $content = preg_replace('/<\/body>.*/msi','',$content);
            $content = preg_replace('/<?\/body[^>]*>/msi','',$content);
            $content = preg_replace('/<img[^>]+\>/i', '', $content);
            // $content = preg_replace('/[\r|\n]+/msi','',$content);
            $content = preg_replace('/<--[\S\s]*?-->/msi','',$content);
            $content = preg_replace('/<noscript[^>]*>[\S\s]*?'.
                                  '<\/noscript>/msi',
                                  '',$content);
            $content = preg_replace('/<script[^>]*>[\S\s]*?<\/script>/msi',
                                  '',$content);
            $content = preg_replace('/<script.*\/>/msi','',$content);
        }

        curl_close($curl);
        return $content;
    }

    public function getNotamWebProxy()
    {
        return $this->proxy;
    }

    public function getNotamWebUrl()
    {
        return self::URL_NOTAMWEB;
    }
}
