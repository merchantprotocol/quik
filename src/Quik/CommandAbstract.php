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
     *
     * @var string
     */
    CONST RELEASES_JSON = 'releases.json';
    
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
        $webdir = $this->_app->getWebrootDir();
        if ($changedDir = $this->getParameters()->getDirectory()) {
            $webdir = $changedDir;
        }
        $app = new \Quik\Application($webdir, $options);
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
    public function getParameters()
    {
        return $this->_app->getParameters();
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
     * @return \Quik\Shell
     */
    public function getShell()
    {
        return $this->_shell;
    }
    
    /**
     *
     * @return string
     */
    public function getBinMagento()
    {
        return $this->_app->getWebrootDir().'bin/magento';
    }
    
    /**
     *
     * @param string   $message
     * @param string   $color
     * @param boolean  $linebreak
     * @param boolean  $cut
     * @return boolean
     */
    public function echo($message, $color = SELF::NC, $linebreak = true, $cut = false)
    {
        $wascut = false;
        if ($this->isQuiet()) {
            return false;
        }
        if ($cut) {
            $_message = substr($message, 0, $this->_shell->getMaxLength());
            if (strlen($_message)<strlen($message)) {
                $wascut = true;
            }
            $message = $_message;
        }
        echo $color."$message".($wascut?'...':'').SELF::NC.($linebreak?"\n":'');
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
     * @param string $message
     * @return boolean
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
     * Ask the user a question and return their input
     *
     * @param string $message
     * @param string $color
     * @return string
     */
    public function prompt($message, $color = SELF::NC)
    {
        $input = false;
        // If this is an interactive terminal
        if (!$this->isQuiet()) {
            $this->echo($message." ", $color, false);
            $handle = fopen ("php://stdin","r");
            $input = trim(fgets($handle));
        }
        // If there's no input then look for the default in the message
        if (!$input) {
            preg_match('/(?<=\[).+?(?=\])/', $message, $match);
            if (isset($match[0])) {
                $input = $match[0];
            }
        }
        return $input;
    }
    
    /**
     * 
     * @var array
     */
    protected $_keyMap = [
        '65' => 'up',
        '67' => 'right',
        '66' => 'down',
        '68' => 'left',
        '113'=> 'q',
        '115'=> 's',
        '10' => 'ENTER',
    ];
    
    /**
     * 
     * @param array $for
     * @param array $quit
     * @return array|string[]
     */
    public function listen( $for = [], $quit = ['q'] )
    {
        if (!is_array($quit)) {
            $quit = [$quit];
        }
        readline_callback_handler_install('', function() { });
        while (true) {
            $r = array(STDIN);
            $w = NULL;
            $e = NULL;
            $n = stream_select($r, $w, $e, null);
            if ($n) {
                $c = ord(stream_get_contents(STDIN, 1));
                if ($c == 27 || $c == 91) continue;
                if (isset($this->_keyMap[$c]) && in_array($this->_keyMap[$c], $quit)) {
                    return false;
                } elseif (isset($this->_keyMap[$c])) {
                    return $this->_keyMap[$c];
                }
//                 echo "Char read: $c\n";
            }
        }
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
    
    /**
     *
     * @param integer $done
     * @param integer $total
     * @param string $msg
     * @param number $size
     */
    function show_status($done, $total, $msg='', $size=30) {
        static $start_time;
        
        // if we go over our bound, just ignore it
        if($done > $total) return;
        
        if(empty($start_time)) {
            $start_time=time();
        }
        $now = time();
        
        $perc=(double)($done/$total);
        
        $bar=floor($perc*$size);
        
        $status_bar="\r[".\Quik\CommandAbstract::GREEN;
        $status_bar.=str_repeat("=", $bar);
        if($bar<$size){
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size-$bar);
        } else {
            $status_bar.="=";
        }
        
        $disp=number_format($perc*100, 0);
        
        $status_bar.=\Quik\CommandAbstract::NC."] $disp%";
        
        $rate = ($now-$start_time)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);
        
        $elapsed = $now - $start_time;
        
        
        $status_bar.= " ".number_format($elapsed)."s $msg";
        
        $this->_shell->clearTerminalLine();
        $this->echo($status_bar, SELF::NC, false, true);
        
        flush();
        
        // when done, send a newline
        if($done == $total) {
            echo "\n";
        }
    }
    
    /**
     *
     * @return string
     */
    protected function _getBaseDir()
    {
        if (is_null($this->_base_dir)) {
            if ($this->getParameters()->getDeployDir()) {
                $this->_base_dir = rtrim($this->getParameters()->getDeployDir(), '/').DIRECTORY_SEPARATOR;
            } else {
                
                $test = dirname($this->_app->getWebrootDir()).DIRECTORY_SEPARATOR.SELF::RELEASES_JSON;
                $test2 = dirname(dirname($this->_app->getWebrootDir())).DIRECTORY_SEPARATOR.SELF::RELEASES_JSON;
                $test3 = dirname(dirname(dirname($this->_app->getWebrootDir()))).DIRECTORY_SEPARATOR.SELF::RELEASES_JSON;
                if (file_exists($test)) {
                    $this->_base_dir = dirname($test).DIRECTORY_SEPARATOR;
                } elseif (file_exists($test2)) {
                    $this->_base_dir = dirname($test2).DIRECTORY_SEPARATOR;
                } elseif (file_exists($test3)) {
                    $this->_base_dir = dirname($test3).DIRECTORY_SEPARATOR;
                } else{
                    $this->echo('Please specify a deploy directory with --deploy-dir', SELF::YELLOW);
                    exit(0);
                }
            }
        }
        return $this->_base_dir;
    }
}
