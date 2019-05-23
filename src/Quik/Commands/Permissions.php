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
        $response = $this->_shell->execute('find . -type f -not -perm 664 -exec chmod 664 {} \;');
        $response = $this->_shell->execute('find . -type d -not -perm 775 -exec chmod 775 {} \;');
        
        $this->echo('Updating required writable files and directories', SELF::GREEN);
        $response = $this->_shell->execute('find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +');
        $response = $this->_shell->execute('find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +');
        
        $this->echo('Updating specific files', SELF::GREEN);
        $response = $this->_shell->execute('mkdir pub/static');
        $response = $this->_shell->execute('chmod 775 pub/static');
        $response = $this->_shell->execute('chmod 664 ./app/etc/*.xml');
        $response = $this->_shell->execute('chmod u+x bin/magento');
        $response = $this->_shell->execute('chmod u+x vendor/bin/quik');

        $response = $this->_shell->execute('find . -not -user %s -execdir chown %s:%s {} \+', [$this->getUser(),$this->getUser(),$this->getGroup()]);
        $this->echo('Permissions Updated Successfully!', SELF::GREEN);
    }
}
