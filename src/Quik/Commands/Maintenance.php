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

class Maintenance extends \Quik\CommandAbstract
{
    /**
     * 
     * @var array
     */
    public static $commands = ['splash'];
    
    /**
     * Build Magento
     */
    public function execute()
    {
        if ($this->getOff()) {
            $response = $this->_shell->execute($this->getBinMagento()." maintenance:enable --ip={$this->getCurrentUserIp()}");
            $this->echo($response->output);
            $this->echo("Splash page is up for visitors!", SELF::GREEN);
        } else {
            $response = $this->_shell->execute($this->getBinMagento()." maintenance:disable");
            $this->echo($response->output);
            $this->echo("Splash page has been turned off.", SELF::YELLOW);
        }
    }
    
    /**
     * 
     * @return boolean
     */
    public function getOff()
    {
        return !$this->getParameters()->get('__maintenance_off');
    }
    
    /**
     * 
     * @return string
     */
    public function getCurrentUserIp()
    {
        $currentUser = new \Quik\User;
        $response = 'none';
        if ($ip = $currentUser->getIp()) {
            $response = $ip;
        }
        return $response;
    }
}
