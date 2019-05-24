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
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: quik build [options]'.PHP_EOL;
        echo ' This command was designed for your build server. We completely build your '.PHP_EOL;
        echo ' Magento codebase and commit it to your repository in preparation for a '.PHP_EOL;
        echo ' comprehensive manual quality control test. This will allow for a zero'.PHP_EOL;
        echo ' downtime rollout.'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo PHP_EOL;
        echo ' Run `quik build` and we\'ll run the following command sequence:'.PHP_EOL;
        echo ' 1)  Check and set Magento Mode'.PHP_EOL;
        echo ' 2)  Set proper file permissions'.PHP_EOL;
        echo ' 3)  Enable maintenance mode and whitelist your IP'.PHP_EOL;
        echo ' 4)  app:config:import'.PHP_EOL;
        echo ' 5)  setup:upgrade'.PHP_EOL;
        echo ' 6)  setup:di:compile'.PHP_EOL;
        echo ' 7)  setup:static-content:deploy'.PHP_EOL;
        echo ' 8)  Precompress pub/static with gzip'.PHP_EOL;
        echo ' 9)  Set proper file permissions'.PHP_EOL;
        echo ' 10) Disable maintenance window'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Build Magento
     */
    public function execute()
    {
        // check mode
        $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:show');
        $mode = substr($response->output, strlen('Current application mode: '),9);
        if ($mode == 'developer') {
            if (!$this->confirm("The current mode is not set to production. Would you like to update it?"))
            {
                $this->echo("We suggest that you use `quik dev-build` instead. Aborting...", SELF::YELLOW);
                exit(0);
            }
        }
        
        // confirm values
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
        
        if ($mode == 'developer') {
            $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:set production');
            $this->echo($response->output);
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
        
        $this->echo('Updating Permissions...');
        $this->run("gzip -y");
        $this->echo('Done Updating Permissions!');
        
        $this->echo('Updating Permissions...');
        $this->run("permissions -y -q $userGroup");
        $this->echo('Done Updating Permissions!');
        
        $response = $this->_shell->execute($this->getBinMagento().' maintenance:disable');
        $this->echo($response->output);
        $this->echo("Build Has Completed Successfully!", SELF::GREEN);
    }
}
