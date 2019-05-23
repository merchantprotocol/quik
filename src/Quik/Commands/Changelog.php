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

class Changelog extends \Quik\CommandAbstract
{
    /**
     * Build Magento
     */
    public function execute()
    {
        $this->_shell->execute("cd ".$this->_app->getWebrootDir());
        $status = $this->_shell->execute("git status");
        
        // get tag information
        $this->_shell->execute("git fetch --tags");
        $tagsString = $this->_shell->execute("git tag --list");
        $tagsArray = explode(PHP_EOL, $tagsString->output);
        $lastTag = end($tagsArray);
        // git log
        if ($lastTag) {
            $cmd = "git log --no-merges -1 --format=%ai $lastTag";
            $lastTagDate = $this->_shell->execute($cmd);
            $logString = $this->_shell->execute("git log --no-merges --since='$lastTagDate->output'");
        } else {
            $logString = $this->_shell->execute("git log --no-merges --full-history");
        }
        
        // format the log
        $keywords = preg_split("/[\s,]+/", "hypertext language, programming");
        
        
        
        var_dump($logString->output);
    }
    
    public function gitLog()
    {
        
        $response = $this->_shell->execute("git log");
        var_dump($response->output);
    }
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo 'Usage: quik dev [options]'.PHP_EOL;
        echo PHP_EOL;
        echo 'Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -, --          Place this project into developer mode.'.PHP_EOL;
        echo PHP_EOL;
    }
}
