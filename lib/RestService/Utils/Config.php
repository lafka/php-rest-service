<?php

/**
* Copyright 2013 François Kooman <fkooman@tuxed.net>
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

namespace RestService\Utils;

class Config
{
    private $_configFile;
    private $_configValues;

    public function __construct($configFile)
    {
        $this->_configFile = $configFile;

        if (!file_exists($configFile) || !is_file($configFile) || !is_readable($configFile)) {
            throw new ConfigException("configuration file not found");
        }

        $this->_configValues = parse_ini_file($configFile, TRUE);
    }

    public function getValue($key, $required = TRUE)
    {
        if (array_key_exists($key, $this->_configValues)) {
            return $this->_configValues[$key];
        } else {
            if ($required) {
                throw new ConfigException("configuration key '$key' not set in configuration file'");
            }

            return NULL;
        }
    }

    public function getSectionValue($section, $key, $required = TRUE)
    {
        if (array_key_exists($section, $this->_configValues) && array_key_exists($key, $this->_configValues[$section])) {
            return $this->_configValues[$section][$key];
        } else {
            if ($required) {
                throw new ConfigException("configuration key '$key' in section '$section' not set in configuration file'");
            }

            return NULL;
        }
    }

    public function getSectionValues($section, $required = TRUE)
    {
        if (array_key_exists($section, $this->_configValues)) {
            return $this->_configValues[$section];
        } else {
            if ($required) {
                throw new ConfigException("configuration section '$section' not set in configuration file'");
            }

            return NULL;
        }
    }

    public function setValue($key, $value)
    {
        $this->_configValues[$key] = $value;
    }

    public function setSectionValue($section, $key, $value)
    {
        $this->_configValues[$section][$key] = $value;
    }

    public function toIni()
    {
        return self::arrayToIni($this->_configValues);
    }

    public static function arrayToIni(array $iniArray)
    {
        $output = "";
        foreach ($iniArray as $k => $v) {
            if (!is_array($v)) {
                $output .= $k . "=" . $v . PHP_EOL;
            } else {
                $arrayKeys = array_keys($v);
                if (is_int($arrayKeys[0])) {
                    foreach ($v as $v2) {
                        $output .= $k . "[]=" . $v2 . PHP_EOL;
                    }
                } else {
                    $output .= "[" . $k . "]" . PHP_EOL;
                    $output .= self::arrayToIni($v, $output);
                }
            }
        }

        return $output;
    }

}
