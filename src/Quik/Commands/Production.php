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

class Production extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'prod',
        'prod:set',
        'prod:build'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik prod [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' '.PHP_EOL;
//         echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  prod                Show help'.PHP_EOL;
        echo '  prod:set            '.PHP_EOL;
        echo '  prod:build          '.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
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
     * Build Magento
     */
    public function executeBuild()
    {
        // check mode
        $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:show');
        $mode = substr($response->output, strlen('Current application mode: '),9);
        if ($mode !== 'productio') {
            if (!$this->confirm("The current mode is not set to production. Would you like to update it?"))
            {
                $this->echo("We suggest that you use `quik dev:build` instead. Aborting...", SELF::YELLOW);
                exit(0);
            }
        }
        
        $userGroup = '';
        if ($this->getUser() || $this->getGroup()) {
            $userGroup = $this->getUser().':'.$this->getGroup();
        }
        
        // confirm values
        if (!$this->getUser() || !$this->getGroup()) {
            $this->showInfo();
            if (!$this->confirm("Continue with the build?"))
            {
                $this->echo("Aborting...", SELF::YELLOW);
                exit(0);
            }
        }
        
        if ($mode == 'developer') {
            $this->show_status(0,100, 'Setting Production Mode');
            $this->_shell->execute($this->getBinMagento().' deploy:mode:set production --skip-compilation');
        }
        
        $this->show_status(5,100, 'Updating Permissions');
        $this->run("permissions -y -q $userGroup");
        
        $this->show_status(10,100, 'Enabling Maintenance Page');
        $this->run('splash -q');
        
        $this->show_status(15,100, 'Clearing Directories');
        $this->run("clear -y -q");
        
        $this->show_status(25,100, 'Running Composer');
        $this->_shell->execute("composer install -d {$this->getParameters()->getDirectory()} --no-dev");
        $this->_shell->execute("composer dump-autoload -d {$this->getParameters()->getDirectory()} --no-dev --optimize");
        $this->_shell->execute("composer update -d {$this->getParameters()->getDirectory()} --no-dev");
        
        $this->show_status(35,100, "Importing Configurations");
        $this->_shell->execute($this->getBinMagento().' app:config:import');
        
        $this->show_status(40,100, "Enabling Merged Assets");
        $this->run("n -q config:store:set dev/css/merge_css_files 1");
        $this->run("n -q config:store:set dev/css/minify_files 1");
        $this->run("n -q config:store:set dev/js/merge_files 1");
        $this->run("n -q config:store:set dev/js/enable_js_bundling 0");
        $this->run("n -q config:store:set dev/js/minify_files 1");
        
        $this->show_status(45,100, 'Running setup:upgrade');
        $this->_shell->execute($this->getBinMagento().' setup:upgrade');
        
        $this->show_status(55,100, 'Running setup:di:compile');
        $this->_shell->execute($this->getBinMagento().' setup:di:compile');
        
        $this->show_status(65,100, 'Running setup:static-content:deploy');
        $this->_shell->execute($this->getBinMagento().' setup:static-content:deploy');
        
        
        $this->show_status(75,100, 'Gzipping pub/static/*');
        $this->run("gzip -y -q");
        
        $this->show_status(85,100, 'Updating Permissions');
        $this->run("permissions -y -q $userGroup");
        
        $this->show_status(99,100, 'Disabling Maintenance Page');
        $this->_shell->execute($this->getBinMagento().' maintenance:disable');
        
        $this->show_status(100,100, 'Build Has Completed!');
    }
    
    /**
     *
     */
    public function executeSet()
    {
        $response = $this->_shell->execute($this->getBinMagento()." deploy:mode:set production --skip-compilation");
        $this->echo($response->output);
    }
}
