<?php

/**
 * Copyright 2013-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * Copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 *
 * This file is part of Coco_XH.
 *
 * Coco_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Coco_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Coco_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * The environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. / or /cmsimple_xh/).
 */
 
namespace Coco;

use PHPUnit_Framework_TestCase;

class CSRFAttackTest extends PHPUnit_Framework_TestCase
{
    /**
     * The URL of the CMSimple installation.
     *
     * @var string
     */
    private $url;

    /**
     * The cURL handle.
     *
     * @var resource
     */
    private $curlHandle;

    /**
     * The filename of the cookie file.
     *
     * @var string
     */
    private $cookieFile;

    /**
     * Sets up the test fixture.
     *
     * Logs in to back-end and stores cookies in a temp file.
     *
     * @return void
     */
    public function setUp()
    {
        $this->url = 'http://localhost' . getenv('CMSIMPLEDIR');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'CC');

        $this->curlHandle = curl_init($this->url . '?&login=true&keycut=test');
        curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($this->curlHandle);
        curl_close($this->curlHandle);
    }

    /**
     * Sets the cURL options.
     *
     * @param array $fields A map of post fields.
     *
     * @return void
     */
    private function setCurlOptions($fields)
    {
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile
        );
        curl_setopt_array($this->curlHandle, $options);
    }

    /**
     * Tests an attack.
     *
     * @param array  $fields      A map of post fields.
     * @param string $queryString A query string.
     *
     * @return void
     *
     * @dataProvider dataForAttack
     */
    public function testAttack($fields, $queryString = null)
    {
        $url = $this->url . (isset($queryString) ? '?' . $queryString : '');
        $this->curlHandle = curl_init($url);
        $this->setCurlOptions($fields);
        curl_exec($this->curlHandle);
        $actual = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        curl_close($this->curlHandle);
        $this->assertEquals(403, $actual);
    }

    /**
     * Provides data for testAttack().
     *
     * @return array
     */
    public function dataForAttack()
    {
        return [
            [
                [
                    'action' => 'delete',
                    'coco_name' => 'test'
                ],
                '&coco&admin=plugin_main'
            ],
            // the following requires coco('test') on page "Languages"
            [
                [
                    'coco_text_test' => '<p>hacked</p>'
                ],
                'Languages&edit'
            ]
        ];
    }
}
