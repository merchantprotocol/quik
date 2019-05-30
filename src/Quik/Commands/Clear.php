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

class Clear extends \Quik\CommandAbstract
{
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik clear [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' This command will clear out all of your temporary, static, and generated'.PHP_EOL;
        echo ' code directories.'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -y                  Preapprove the confirmation prompt.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * @see https://devdocs.magento.com/guides/v2.3/howdoi/php/php_clear-dirs.html
     */
    public function execute()
    {
        $dirs = [
            $this->_app->getWebrootDir().'var/page_cache/*',
            $this->_app->getWebrootDir().'var/cache/*',
            $this->_app->getWebrootDir().'var/composer_home/*',
            $this->_app->getWebrootDir().'var/view_preprocessed/*',
            $this->_app->getWebrootDir().'generated/code/*',
            $this->_app->getWebrootDir().'generated/metadata/*',
            $this->_app->getWebrootDir().'pub/static/*',
        ];
        
        $command = $this->_shell->execute('rm -rf '.implode($dirs, ' '));
        $this->run("n -q cache:clean");
        $this->run("n -q cache:flush");
        
        foreach ($dirs as $dir) {
            $this->echo($dir);
        }
        $this->echo("Successfully Cleared All tmp Directories!", SELF::GREEN);
    }
}
