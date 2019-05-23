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

class Build extends \Quik\CommandAbstract
{
    /**
     * @see https://devdocs.magento.com/guides/v2.3/howdoi/php/php_clear-dirs.html
     */
    public function execute()
    {
        $this->showInfo();
        if (!$this->confirm("Continue with the build?"))
        {
            $this->echo("Aborting...", SELF::YELLOW);
            exit(0);
        }
        
        $userGroup = '';
        if ($this->getUser() || $this->getGroup()) {
            $userGroup = $this->getUser().':'.$this->getGroup();
        }
        
        $this->echo('Updating Permissions...');
        $this->run("permissions -y -q $userGroup");
        $this->echo('Done Updating Permissions!');
        
        $response = $this->_shell->execute($this->getBinMagento().' maintenance:enable');
        $this->echo($response->output);
        $this->run("clear -y");
        $response = $this->_shell->execute($this->getBinMagento().' app:config:import');
        $this->echo($response->output);
        $response = $this->_shell->execute($this->getBinMagento().' setup:upgrade');
        $this->echo($response->output);
        $response = $this->_shell->execute($this->getBinMagento().' setup:di:compile');
        $this->echo($response->output);
        $response = $this->_shell->execute($this->getBinMagento().' setup:static-content:deploy');
        $this->echo($response->output);
        $response = $this->_shell->execute($this->getBinMagento().' indexer:reindex');
        $this->echo($response->output);
        
        $this->echo('Updating Permissions...');
        $this->run("permissions -y -q $userGroup");
        $this->echo('Done Updating Permissions!');
        
        $response = $this->_shell->execute($this->getBinMagento().' maintenance:disable');
        $this->echo($response->output);
        $this->echo("Build Has Completed Successfully!", SELF::GREEN);
    }
}
