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

class Permissions extends \Quik\CommandAbstract
{
    public static $commands = ['permi'];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo 'Usage: '.\Quik\CommandAbstract::GREEN.'quik permi [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' Update the file permissions for your magento installation. This command will'.PHP_EOL;
        echo ' run an optimized script to only locate those files which do not already have the'.PHP_EOL;
        echo ' proper permissions. For large codebases this will significantly speed up this'.PHP_EOL;
        echo ' operation.'.PHP_EOL;
        echo PHP_EOL;
        echo 'Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -y                  Disable the maintenance page.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * 
     */
    public function execute()
    {
        $this->showInfo();
        if (!$this->confirm("Continue?"))
        {
            $this->echo("Aborting...", SELF::YELLOW);
            exit(0);
        }
        $this->echo('Entering Directory '.$this->_app->getWebrootDir(), SELF::GREEN);
        $response = $this->_shell->execute("cd %s", [$this->_app->getWebrootDir()]);
        
        $this->echo('Resetting all files to 664 and directories to 775', SELF::GREEN);
        $response = $this->_shell->execute('find %s -type f -not -perm 664 -exec chmod 664 {} \;',[$this->_app->getWebrootDir()]);
        $response = $this->_shell->execute('find %s -type d -not -perm 775 -exec chmod 775 {} \;', [$this->_app->getWebrootDir()]);
        
        $this->echo('Updating required writable files and directories', SELF::GREEN);
        
        $response = $this->_shell->execute("find {$this->_app->getWebrootDir()}var {$this->_app->getWebrootDir()}generated "
        ."{$this->_app->getWebrootDir()}vendor {$this->_app->getWebrootDir()}pub/static {$this->_app->getWebrootDir()}pub/media "
        ."{$this->_app->getWebrootDir()}app/etc -type f -exec chmod g+w {} +");
        
        $response = $this->_shell->execute("find {$this->_app->getWebrootDir()}var {$this->_app->getWebrootDir()}generated "
        ."{$this->_app->getWebrootDir()}vendor {$this->_app->getWebrootDir()}pub/static {$this->_app->getWebrootDir()}pub/media "
        ."{$this->_app->getWebrootDir()}app/etc -type d -exec chmod g+ws {} +");

        $response = $this->_shell->execute("getenforce", [], false, false);
        if (strpos($response->output, 'enforcing') !==false) {
            $this->_shell->execute("sudo chcon -R -h -t unconfined_u:object_r:httpd_sys_rw_content_t:s0  {$this->_app->getWebrootDir()}");
            $this->_shell->execute("sudo chcon -R -h -t unconfined_u:object_r:httpd_sys_rw_content_t:s0 {$this->_app->getWebrootDir()}generated");
            $this->_shell->execute("sudo chcon -R -h -t unconfined_u:object_r:httpd_sys_rw_content_t:s0 {$this->_app->getWebrootDir()}var");
            $this->_shell->execute("sudo chcon -R -h -t unconfined_u:object_r:httpd_sys_rw_content_t:s0 {$this->_app->getWebrootDir()}pub/static");
            $this->_shell->execute("sudo chcon -R -h -t unconfined_u:object_r:httpd_sys_rw_content_t:s0 {$this->_app->getWebrootDir()}pub/media");
            $this->_shell->execute("sudo chcon -R -h -t unconfined_u:object_r:httpd_sys_rw_content_t:s0 {$this->_app->getWebrootDir()}app/etc");
        }

        $this->echo('Updating specific files', SELF::GREEN);
        $response = $this->_shell->execute("mkdir -p {$this->_app->getWebrootDir()}pub/static");
        $response = $this->_shell->execute("chmod 775 {$this->_app->getWebrootDir()}pub/static");
        $response = $this->_shell->execute("chmod 664 {$this->_app->getWebrootDir()}app/etc/*.xml");
        $response = $this->_shell->execute("chmod u+x {$this->_app->getWebrootDir()}bin/magento");
        $response = $this->_shell->execute("chmod u+x {$this->_app->getWebrootDir()}vendor/bin/quik");
        $response = $this->_shell->execute("chmod u+x {$this->_app->getWebrootDir()}vendor/merchantprotocol/quik/src/Quik/n98-magerun2.phar");

        $response = $this->_shell->execute('sudo find %s -not -user %s -exec chown '.$this->getUser().':'.$this->getGroup().' {} \;',
            [$this->_app->getWebrootDir(),$this->getUser(),$this->getUser()]);

        $response = $this->_shell->execute('sudo find %s -not -group %s -execdir chgrp %s {} \+',
            [$this->_app->getWebrootDir(),$this->getGroup(),$this->getGroup()]);

        $this->echo('Permissions Updated Successfully!', SELF::GREEN);
    }
}
