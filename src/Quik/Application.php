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

class Application
{
    /**
     * Current version of this library
     * 
     * @var string
     */
    CONST VERSION = '0.0.5';
    
    /**
     * Common used line break
     * @var string
     */
    CONST LB = PHP_EOL."============================================================================= ".PHP_EOL;
    
    /**
     *
     * @var \Quik\Parameters
     */
    protected $_parameters = null;
    
    /**
     * Path to Magento root
     * @var string
     */
    protected $_webroot = null;
    
    /**
     * The class object of the command to run
     * @var \Quik\CommandAbstract
     */
    protected $_command = null;
    
    /**
     * The class name of the command to run
     * @var string
     */
    protected $_commandClass = null;
    
    /**
     * @see https://devdocs.magento.com/guides/v2.3/howdoi/php/php_clear-dirs.html
     */
    public function __construct($webroot, $parameters)
    {
        $this->_webroot = $webroot;
        $this->_parameters = $parameters;
        $this->_commandClass = $this->getParameters()->getCommand();
        $command = $this->getParameters()->getCommand();
        
        // display version
        if ($this->_parameters->getVersion()) {
            echo 'Quik Version '.SELF::VERSION.' by Merchant Protocol'.PHP_EOL;
            exit(0);
        }
        // Display help for command or more
        if ($this->_parameters->getHelp()) {
            if ($this->_commandClass && is_callable(array($this->getCommand(), 'showUsage'))) {
                $this->getCommand()->showUsage();
            } else {
                $this->showUsage();
            }
            exit(0);
        }
        // No command error
        if (!$this->_commandClass) {
            if ($this->_parameters->getCalledCommand()) {
                $this->showError();
            }
            $this->showUsage();
            $commands = $this->_parameters->getCommands();
            foreach($commands as $class) {
                $classname = '\Quik\Commands\\'.$class;
                $_command = new $classname($this);
                if (is_callable(array($_command, 'showUsage'))) {
                    echo SELF::LB.\Quik\CommandAbstract::YELLOW." $class".\Quik\CommandAbstract::NC.SELF::LB;
                    $_command->showUsage();
                    
                    echo \Quik\CommandAbstract::YELLOW.' More.. '.\Quik\CommandAbstract::NC;
                    $handle = fopen ("php://stdin","r");
                    $input = trim(fgets($handle));
                    if ($input == 'q' || $input == 'c' || $input == 'w') {
                        exit(0);
                    }
                    
                }
            }
            echo SELF::LB." Command Prompt".SELF::LB;
            
            foreach($commands as $key => $class) {
                echo \Quik\CommandAbstract::YELLOW."    $key) $class".\Quik\CommandAbstract::NC.PHP_EOL;
            }
            echo PHP_EOL;
            
            echo \Quik\CommandAbstract::YELLOW.' Choose a command to run: '.\Quik\CommandAbstract::NC;
            $handle = fopen ("php://stdin","r");
            $input = trim(fgets($handle));
            if (!strlen($input)) {
                exit(0);
            }
            if (isset($commands[$input])) {
                $command = $commands[$input];
            } elseif (in_array($input, $commands)) {
                $command = $input;
            } else {
                exit(0);
            }
        }
        $this->run( $command );
    }
    
    /**
     *
     * @return \Quik\CommandAbstract
     */
    public function getCommand()
    {
        if (is_null($this->_command)) {
            $classname = '\Quik\Commands\\'.$this->_commandClass;
            $this->_command = new $classname($this);
        }
        return $this->_command;
    }
    
    /**
     * 
     * @param unknown $command
     */
    public function run( $command )
    {
        $classname = '\Quik\Commands\\'.$command;
        $this->_command = new $classname($this);
        $this->_command->execute();
    }
    
    /**
     *
     * @return string
     */
    public function getWebrootDir()
    {
        return $this->_webroot;
    }
    
    /**
     *
     * @return string
     */
    public function getQuikRootDir()
    {
        return $this->_webroot.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'merchantprotocol'.DIRECTORY_SEPARATOR.'quik';
    }
    
    /**
     *
     * @return string
     */
    public function getQuikDir()
    {
        return $this->getQuikRootDir().DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Quik';
    }
    
    /**
     * .git dir
     * @return string
     */
    public function getGitDir()
    {
        return $this->getWebrootDir().DIRECTORY_SEPARATOR.'.git';
    }
    
    /**
     *
     * @return \Quik\Parameters
     */
    public function getParameters()
    {
        return $this->_parameters;
    }
    
    /**
     * Display the help information
     *
     */
    public function showUsage()
    {
        echo PHP_EOL;
        echo \Quik\CommandAbstract::GREEN.PHP_EOL;
        echo "                            __  __               _                 _".PHP_EOL;
        echo "             ZZZZZ         |  \/  |             | |               | |".PHP_EOL;
        echo "        ZZZZZZZZZZZZZZZ    | \  / | ___ _ __ ___| |__   __ _ _ __ | |_".PHP_EOL;
        echo "        ZZZZZ ZZZZZZZZZ    | |\/| |/ _ \ '__/ __| '_ \ / _` | '_ \| __|".PHP_EOL;
        echo "        ZZZZ ZZZZZZZZZZ    | |  | |  __/ | | (__| | | | (_| | | | | |_".PHP_EOL;
        echo "        ZZI          ZZ    |_|  |_|\___|_|  \___|_| |_|\__,_|_| |_|\__|".PHP_EOL;
        echo "        ZZZZZZZZZZZZZZZ".PHP_EOL;
        echo "        ZZZ          ZZ     _____           _                  _".PHP_EOL;
        echo "         ZZZZZZZZZZ ZZZ    |  __ \         | |                | |".PHP_EOL;
        echo "         ZZZZZZZZZ ZZZ     | |__) | __ ___ | |_ ___   ___ ___ | |".PHP_EOL;
        echo "           ZZZZZZZZZZ      |  ___/ '__/ _ \| __/ _ \ / __/ _ \| |".PHP_EOL;
        echo "            ZZZZZZZZ       | |   | | | (_) | || (_) | (_| (_) | |".PHP_EOL;
        echo "              ZZZZ         |_|   |_|  \___/ \__\___/ \___\___/|_|".PHP_EOL;
        echo \Quik\CommandAbstract::NC.PHP_EOL;
        echo ' Homepage: '.\Quik\CommandAbstract::GREEN.'https://merchantprotocol.com/'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' Author: Jack <jonathon@merchantprotocol.com>'.PHP_EOL;
        echo ' Version: Quik v'.SELF::VERSION.PHP_EOL;
        echo ' '.PHP_EOL;
        echo ' '.PHP_EOL;
        echo PHP_EOL;
        echo ' Usage: quik <command> [options] [args]'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help      Show this message'.PHP_EOL;
        echo '  -q, --quiet     Causes quik to return no output'.PHP_EOL;
        //         echo '  -v, --verbose   Display the verbose of every command'.PHP_EOL;
        echo '  -y              Automatically confirm all prompts'.PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Display the help information
     *
     */
    public function showError()
    {
        echo PHP_EOL;
        echo PHP_EOL;
        echo \Quik\CommandAbstract::RED.'The command you entered does not exist. @see quik --help.'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo PHP_EOL;
    }
}
