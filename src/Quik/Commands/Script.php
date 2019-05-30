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

class Script extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'script:save:last',
        'script:history',
        'script:view',
        'script:edit'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik script<subcall> [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' When developing there are always commands that need to be run on production. This script'.PHP_EOL;
        echo ' functionality allows you to create deploy scripts that are specific to a version.'.PHP_EOL;
        echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  script                   show help'.PHP_EOL;
        echo '  script:save:last         Saves the last command that you ran to the next deploy script'.PHP_EOL;
        echo '  script:edit              Opens nano of the script file'.PHP_EOL;
        echo '  script:clear             Clear the working command file'.PHP_EOL;
        echo '  script:history           Displays the last run command, press up and down to cycle commands'.PHP_EOL;
        echo '  script:history:clear     Displays the last run command, press up and down to cycle commands'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -y                  Preapprove the confirmation prompts'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Location where scripts are stored
     * @var string
     */
    CONST SCRIPT_DIR = 'patches/vendor/merchantprotocol/quik/';
    
    /**
     * Working scriptfile name
     * @var unknown
     */
    CONST WORKING_SCRIPTNAME = 'working';
    
    /**
     * Save location of the commands you run
     * @var string
     */
    CONST HISTORY_SCRIPTNAME = 'quikScriptHistory.log';
    
    /**
     * 
     */
    public function execute()
    {
        $this->showUsage();
    }
    
    /**
     * Display the scriptfile
     */
    public function executeView()
    {
        echo $this->_getContents($this->_getScriptFilePath());
    }
    
    /**
     * Display the scriptfile
     */
    public function executeClear()
    {
        if ($this->confirm("Are you sure you want to clear the working script?")) {
            echo $this->_eraseContents($this->_getScriptFilePath());
        }
    }
    
    /**
     * Display the scriptfile
     */
    public function executeClearHistory()
    {
        if ($this->confirm("Are you sure you want to clear the working script?")) {
            echo $this->_eraseContents($this->_getHistoryFilePath());
        }
    }
    
    /**
     * Display the scriptfile
     */
    public function executeEdit()
    {
        $this->_shell->execute('nano %s', [$this->_getScriptFilePath()], true);
    }
    
    /**
     * Display the history
     */
    public function executeHistory()
    {
        $cmds = $this->_getHistory();
        $position = \Quik\Helper::array_key_last($cmds);
        $this->echo($position.' '.$cmds[$position], SELF::YELLOW, false, true);
        while ($pressed = $this->listen(['up','down','s','ENTER'], 'q'))
        {
            switch($pressed) {
                case 'up':
                    $this->_shell->clearTerminalLine();
                    $position--;
                    if ($position < 0) $position = 0;
                    break;
                case 'down':
                    $this->_shell->clearTerminalLine();
                    $position++;
                    if ($position >= count($cmds)) $position = count($cmds)-1;
                    break;
                case 's':
                case 'ENTER':
                    $this->echo(' (Saved)', SELF::GREEN);
                    $this->_putContents($this->_getScriptFilePath(), $cmds[$position].PHP_EOL);
                    break 2;
            }
            $this->echo($position.' '.$cmds[$position], SELF::YELLOW, false, true);
        }
    }
    
    /**
     *
     */
    public function executeSaveLast()
    {
        $cmds = $this->_getHistory();
        $position = \Quik\Helper::array_key_last($cmds) - 1;
        $this->echo($cmds[$position], SELF::YELLOW, false, true);
        $this->_putContents($this->_getScriptFilePath(), $cmds[$position].PHP_EOL);
        $this->echo(' (Saved)', SELF::GREEN);
    }
    
    /**
     * Return an array of the command history
     * 
     * @return array
     */
    protected function _getHistory()
    {
        $all = $this->_getContents($this->_getHistoryFilePath());
        $cmds = [];
        foreach(explode(PHP_EOL, $all) as $line) {
            if (!strlen($line)) continue;
            $cmds[] = $line;
        }
        return $cmds;
    }
    
    /**
     * 
     * @param unknown $path
     * @param unknown $data
     * @return number
     */
    protected function _putContents( $path, $data )
    {
        if (!file_exists(dirname($path))) {
            $this->_shell->execute('mkdir -p %s', [dirname($path)]);
        }
        return file_put_contents($path, $data, FILE_APPEND);
    }
    
    /**
     * Erase the contents of this file
     * @param unknown $path
     * @return number
     */
    protected function _eraseContents( $path )
    {
        return file_put_contents($path, '');
    }
    
    /**
     * Return the contents of the scriptfile
     * @return string
     */
    protected function _getContents( $path )
    {
        return file_get_contents($path);
    }
    
    /**
     * Return the absolute path to working scriptfile
     * @return string
     */
    protected function _getHistoryFilePath()
    {
        return $this->_app->getWebrootDir().'var'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.SELF::HISTORY_SCRIPTNAME;
    }
    
    /**
     * Return the absolute path to working scriptfile
     * @return string
     */
    protected function _getScriptFilePath()
    {
        return $this->_app->getWebrootDir().SELF::SCRIPT_DIR.SELF::WORKING_SCRIPTNAME;
    }
    
    /**
     * 
     * @param array $options
     */
    public function saveHistory()
    {
        $cmd = implode(' ', $_SERVER['argv']).PHP_EOL;
        $this->_putContents($this->_getHistoryFilePath(), $cmd);
    }
}
