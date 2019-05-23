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

class Parameters
{
    protected $_quiet = false;
    protected $_yes = false;
    protected $_verbose = false;
    protected $_help = false;
    
    protected $_cert_csr = false;
    
    protected $_command = false;
    protected $_commands = [];
    protected $_args = [];
    
    /**
     *
     */
    public function __construct( $options = [] )
    {
        $start = 0;
        $count = 0;
        if (!empty($options)) {
            $count = count($options);
        } elseif (isset($_SERVER['argc'])) {
            $start = 1;
            $count = $_SERVER['argc'];
            $options =  $_SERVER['argv'];
        }
        
        $commands = $this->getAvailableCommands();
        
        if ($count > $start) {
            for ($i = $start; $i < $count; $i++) {
                $arg = $options[$i];
                
                if (!strlen($arg)) {
                    continue;
                }
                
                if ($arg == '-h' || $arg == '--help') {
                    $this->_help = true;
                } elseif ($arg == '-y') {
                    $this->_yes = true;
                } elseif ($arg == '-q' || $arg == '--quiet') {
                    $this->_quiet = true;
                } elseif ($arg == '-v' || $arg == '--verbose') {
                    $this->_verbose = true;
                    
                } elseif ($arg == '--csr') {
                    $this->_cert_csr = true;
                    
                } elseif (!$this->_command && $command = $this->in_array_r($arg, $commands)) {
                    $this->_command = $command;
                } else {
                    $this->_args[] = $arg;
                }
            }
        } else {
            $this->_help = true;
        }
    }
    
    /**
     * Compile the commands and the shorthand commands which trigger them
     * 
     * @return array
     */
    public function getAvailableCommands()
    {
        if (empty($this->_commands)) {
            foreach (glob(__DIR__.DIRECTORY_SEPARATOR.'Commands'.DIRECTORY_SEPARATOR."*.php") as $filename) {
                $class = basename($filename, '.php');
                $this->_commands[$class][] = strtolower($class);
                
                $commands = call_user_func("\Quik\Commands\\".$class.'::getShorthands');
                if (!empty($commands)) {
                    $this->_commands[$class] = array_merge($this->_commands[$class], $commands);
                }
            }
        }
        return $this->_commands;
    }
    
    /**
     *
     * @return boolean
     */
    public function getHelp()
    {
        return $this->_help;
    }
    
    /**
     *
     * @return boolean
     */
    public function getQuiet()
    {
        return $this->_quiet;
    }
    
    /**
     *
     * @return boolean
     */
    public function getVerbose()
    {
        return $this->_verbose;
    }
    
    /**
     * Used to gain access to the protected variable values
     * 
     * @return unknown
     */
    public function get( $method )
    {
        return $this->$method;
    }
    
    /**
     *
     * @return boolean|unknown
     */
    public function getCommand()
    {
        return $this->_command;
    }
    
    /**
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->_args;
    }
    
    /**
     *
     * @return array
     */
    public function getYes()
    {
        return $this->_yes;
    }
    
    /**
     * 
     * @param unknown $command
     * @param unknown $haystack
     * @param boolean $strict
     * @return boolean
     */
    private function in_array_r($command, $haystack, $strict = false) {
        foreach ($haystack as $class => $item) {
            if (($strict ? $item === $command : $item == $command) || (is_array($item) && in_array($command, $item, $strict))) {
                return $class;
            }
        }
        return false;
    }
}
