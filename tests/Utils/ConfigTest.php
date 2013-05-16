
<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


define("BASEPATH", dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RestService');

require_once BASEPATH . DIRECTORY_SEPARATOR . 'Utils' .  DIRECTORY_SEPARATOR . 'Config.php';
require_once BASEPATH . DIRECTORY_SEPARATOR . 'Utils' .  DIRECTORY_SEPARATOR . 'ConfigException.php';

use \RestService\Utils\Config as Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $customvar = "dynamicval";

        $configPath = array(dirname(__DIR__), 'data', 'config.ini');
        $cfg = new Config(implode(DIRECTORY_SEPARATOR, $configPath), array('customvar' => $customvar));

        $p = function(array $p) {
            return implode(DIRECTORY_SEPARATOR, $p);
        };

        $cwd = getcwd();

        $match = array(
            'rootdir'         => $cwd,
            'libdir'          => $p(array($cwd, 'lib')),
            'wwwdir'          => $p(array($cwd, 'www')),
            'path'            => implode(':', array($cwd,
                $p(array($cwd, 'lib')),
                $p(array($cwd, 'www')))),
            'constant'        => 'constant',
            'dynamicpath'     => implode('/', array($cwd, $customvar)),
        );

        $this->assertEquals($cwd, $cfg->getValue('rootdir'));
        $this->assertEquals($match, $cfg->getSectionValues('section'));

        foreach ($match as $k => $v) {
            $this->assertEquals($v, $cfg->getSectionValue('section', $k));
        }
    }
}
