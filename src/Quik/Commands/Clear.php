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
     * @see https://devdocs.magento.com/guides/v2.3/howdoi/php/php_clear-dirs.html
     */
    public function execute()
    {
        $dirs = [
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'var/page_cache',
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'var/cache',
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'var/composer_home',
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'var/view_preprocessed',
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'generated/code',
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'generated/metadata',
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'pub/static',
        ];
        
        echo 'Directories to be cleared:'.PHP_EOL;
        echo '------------------------------------------------'.PHP_EOL;
        foreach ($dirs as $dir) {
            $this->echo('|  '.$dir);
        }
        echo '------------------------------------------------'.PHP_EOL;
        
        // change directory to webroot
        if (!$this->confirm("Shall we clear these directories?"))
        {
            $this->echo("ABORTING", SELF::YELLOW);
            exit(0);
        }
        
        $command = $this->_shell->execute('rm -rf '.implode($dirs, ' '));
        $this->echo("Successfully Cleared All tmp Directories!", SELF::GREEN);
    }
}
