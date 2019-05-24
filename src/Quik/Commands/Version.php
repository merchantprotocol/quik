<?php
/**
 * NOTICE OF LICENSE
 *
 * MIT License
 *
 * Copyright (c) 2019 Merchant Protocol
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 * @category   merchantprotocol
 * @package    merchantprotocol/quik
 * @copyright  Copyright (c) 2019 Merchant Protocol, LLC (https://merchantprotocol.com/)
 * @license    MIT License
 */
namespace Quik\Commands;

class Version extends \Quik\CommandAbstract
{
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo 'Usage: '.\Quik\CommandAbstract::GREEN.'quik version [options] <new.value>'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo PHP_EOL;
        echo 'Options:'.PHP_EOL;
        echo '  -h, --help        Show this message'.PHP_EOL;
        echo '  Just use one of the following options and the version will be automatically increased.'.PHP_EOL;
        echo '  --major      Increases the major, resets minor and patch to 0'.PHP_EOL;
        echo '  --minor      Ignores major, increases minor, resets patch to 0'.PHP_EOL;
        echo '  --patch      Ignores major and minor, increases patch by one'.PHP_EOL;
        echo PHP_EOL;
        echo 'If you enter a number after an option it will override the existing number, leaving other sections untouched.'.PHP_EOL;
        echo 'If current version is 1.1.1 and you enter `quik version --minor` the new version will be 1.2.0.'.PHP_EOL;
        echo 'If current version is 1.1.1 and you enter `quik version --minor 4` the new version will be 1.4.1.'.PHP_EOL;
        echo 'Ignore the options and just enter a new version number as major.minor.patch (e.g. 0.0.1)'.PHP_EOL;
        echo 'If current version is 1.1.1 and you enter `quik version 1.2.3` the new version will be 1.2.3.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * The name of the version file that we keep on record
     * @var string
     */
    CONST VERSION_FILENAME = 'version.json';
    
    /**
     * 
     * @var array
     */
    protected $NEW = [
        'major' => 0,
        'minor' => 0,
        'patch' => 0
    ];
    
    /**
     * 
     * @var array
     */
    protected $_data = [];
    
    /**
     * @see https://devdocs.magento.com/guides/v2.3/howdoi/php/php_clear-dirs.html
     */
    public function execute()
    {
        $this->_getVersionData();
        
        if ($this->getParameters()->get('__version_major')) {
            $this->uptick('major', $this->getValue());
            
        } elseif ($this->getParameters()->get('__version_minor')) {
            $this->uptick('minor', $this->getValue());
            
        } elseif ($this->getParameters()->get('__version_patch')) {
            $this->uptick('patch', $this->getValue());
            
        } elseif ($this->getValue()) {
            list($major,$minor,$patch) = explode('.', $this->getValue());
            $this->setVersion($major,$minor,$patch);
        } else {
            $this->showUsage();
            exit(0);
        }
        
        $this->_setVersionData();
        $this->echo("The new version is ".$this->getVersion(), SELF::GREEN);
    }
    
    /**
     * 
     * @param string $section
     * @param integer $value
     */
    public function uptick( $section, $value = false )
    {
        if ($value) {
            if (!$this->_data['major']) {
                $this->_data['major'] = 0;
            }
            if (!$this->_data['minor']) {
                $this->_data['minor'] = 0;
            }
            if (!$this->_data['patch']) {
                $this->_data['patch'] = 0;
            }
            $this->_data[$section] = (int)$value;
        } else {
            switch ($section) {
                case 'major':
                    $this->_data['major']++;
                    $this->_data['minor'] = 0;
                    $this->_data['patch'] = 0;
                    break;
                case 'minor':
                    if (!$this->_data['major']) {
                        $this->_data['major'] = 0;
                    }
                    $this->_data['minor']++;
                    $this->_data['patch'] = 0;
                    break;
                case 'patch':
                    if (!$this->_data['major']) {
                        $this->_data['major'] = 0;
                    }
                    if (!$this->_data['minor']) {
                        $this->_data['minor'] = 0;
                    }
                    $this->_data['patch']++;
                    break;
            }
        }
    }
    
    /**
     *
     * @return string
     */
    public function getValue()
    {
        $args = $this->getArgs();
        if (isset($args[0])) {
            return $args[0];
        }
        return false;
    }
    
    /**
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_data['major'].'.'.$this->_data['minor'].'.'.$this->_data['patch'];
    }
    
    /**
     * 
     * @param number $major
     * @param number $minor
     * @param number $patch
     * @return boolean
     */
    public function setVersion( $major = 0, $minor = 0, $patch = 0 )
    {
        $this->_data['major'] = (int)$major;
        $this->_data['minor'] = (int)$minor;
        $this->_data['patch'] = (int)$patch;
        return true;
    }
    
    /**
     * put the data back into the file
     * 
     * @param array $data
     * @return number
     */
    protected function _setVersionData()
    {
        $filename = $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.SELF::VERSION_FILENAME;
        $data = json_encode($this->_data);
        return file_put_contents($filename, $data);
    }
    
    /**
     * Return the version data 
     * @return boolean|string
     */
    protected function _getVersionData()
    {
        if (empty($this->_data)) {
            $filename = $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.SELF::VERSION_FILENAME;
            $contents = file_get_contents($filename);
            $contents = json_decode($contents, true);
            if (!$contents) {
                return $this->$NEW;
            }
            $this->_data = $contents;
        }
        return $this->_data;
    }
}
