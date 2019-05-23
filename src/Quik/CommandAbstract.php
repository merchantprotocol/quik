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
namespace Quik;

class CommandAbstract
{
    /**
     * Red text
     * @var string
     */
    CONST RED      = "\033[0;31m";
    
    /**
     * Yellow Text
     * @var string
     */
    CONST YELLOW   = "\033[0;33m";
    
    /**
     * Green Text
     * @var string
     */
    CONST GREEN   = "\033[0;32m";
    
    /**
     * No color text
     * @var string
     */
    CONST NC       = "\033[0m";
    
    /**
     * List of commands that can activate this class
     * @var array
     */
    public static $commands = [];
    
    /**
     * 
     * @var \Quik\Shell
     */
    protected $_shell = NULL;
    
    /**
     * 
     * @var \Quik\Application
     */
    protected $_app = null;
    
    protected $__yes = null;
    protected $__args = null;
    protected $__quiet = null;
    
    protected $_user = null;
    protected $_group = null;
    
    /**
     * 
     * @param \Quik\Application $app
     */
    public function __construct(\Quik\Application $app)
    {
        $this->_app = $app;
        $this->_shell = new \Quik\Shell;
    }
    
    /**
     * placeholder for extended command classes
     */
    public function execute(){}
    
    /**
     * 
     * @param string $script
     */
    public function run($script = '')
    {
        $options = explode(' ', $script);
        $app = new \Quik\Application($this->_app->getWebrootDir(), new \Quik\Parameters($options));
    }
    
    /**
     * 
     * @return array
     */
    public static function getShorthands()
    {
        $class = get_called_class();
        return $class::$commands;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isQuiet()
    {
        if (is_null($this->__quiet)) {
            $this->__quiet = $this->_app->getParameters()->getQuiet();
        }
        return $this->__quiet;
    }
    
    /**
     *
     * @return boolean
     */
    public function getArgs()
    {
        if (is_null($this->__args)) {
            $this->__args = $this->_app->getParameters()->getArgs();
        }
        return $this->__args;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getYes()
    {
        if (is_null($this->__yes)) {
            $this->__yes = $this->_app->getParameters()->getYes();
        }
        return $this->__yes;
    }
    
    /**
     * 
     * @param string $message
     * @param string $color
     * @param boolean $linebreak
     * @return boolean
     */
    public function echo($message, $color = SELF::NC, $linebreak = true)
    {
        if ($this->isQuiet()) {
            return false;
        }
        echo $color."$message".SELF::NC.($linebreak?"\n":'');
    }
    
    /**
     * 
     * @param string $message
     * @return boolean
     */
    public function verbose($message)
    {
        if (!$this->_app->getParameters()->getVerbose()) {
            return false;
        }
        if (!$message) {
            return false;
        }
        echo "$message"."\n";
    }
    
    /**
     * 
     * @param unknown $message
     */
    public function confirm($message)
    {
        if ($this->isQuiet()) {
            return true;
        }
        if ($this->getYes()) {
            return true;
        }
        $this->echo($message." [Y/n] ", SELF::YELLOW, false);
        $handle = fopen ("php://stdin","r");
        $input = strtolower(trim(fgets($handle)));
        return $input=='y' ?true :false;
    }
    
    /**
     * 
     * @return string
     */
    public function getBinMagento()
    {
        return $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'bin/magento';
    }
    
    /**
     *
     * @return string
     */
    public function getUser()
    {
        if (is_null($this->_user)) {
            $args = $this->getArgs();
            if (isset($args[0])) {
                list($user, $group) = explode(':',$args[0]);
                if (strlen($user)) {
                    return $this->_user = $user;
                }
            }
            
            $apache = new \Quik\Apache();
            $this->_user = $apache->getUser();
        }
        return $this->_user;
    }
    
    /**
     *
     * @return string
     */
    public function getGroup()
    {
        if (is_null($this->_group)) {
            $args = $this->getArgs();
            if (isset($args[0])) {
                list($user, $group) = explode(':',$args[0]);
                if (strlen($group)) {
                    return $this->_group =$group;
                }
            }
            
            $apache = new \Quik\Apache();
            $this->_group = $apache->getGroup();
        }
        return $this->_group;
    }
    
    /**
     * 
     */
    public function showInfo()
    {
        if ($this->isQuiet()) {
            return true;
        }
        echo 'Parameters used in this command:'.PHP_EOL;
        echo '------------------------------------------------'.PHP_EOL;
        echo '|  Webserver User:        '.$this->getUser().PHP_EOL;
        echo '|  Webserver Group:       '.$this->getGroup().PHP_EOL;
        echo '|  Path to webroot:       '.$this->_app->getWebrootDir().PHP_EOL;
        echo '|  Path to bin/magento:   '.$this->getBinMagento().PHP_EOL;
        echo '------------------------------------------------'.PHP_EOL;
    }
}
