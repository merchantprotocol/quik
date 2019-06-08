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

class Staging extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'staging',
        'staging:tag'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik staging [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' After testing has completed on the staging server then we\'ll prepare the codebase for release.'.PHP_EOL;
        echo '   1. The changelog will be updated.'.PHP_EOL;
        echo '   2. The version number will be updated in the codebase.'.PHP_EOL;
        echo '   3. Git commit changes'.PHP_EOL;
        echo '   4. Git TAG codebase'.PHP_EOL;
        echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  staging                Show help'.PHP_EOL;
        echo '  staging:tag <tag>      Creates a clone in a sister directory to this repository. Only <tag> is required'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help             Show this message'.PHP_EOL;
        echo '  -y                     Preapprove the confirmation prompt.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Build Magento
     */
    public function execute()
    {
        $this->showUsage();
    }
    
    /**
     * 
     */
    public function executeTag()
    {
        $this->run("changelog -y");
        
    }
}
