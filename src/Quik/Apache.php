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
namespace Quik;

class Apache
{
    protected $_shell = null;
    protected $_apacheuser = null;
    protected $_apachegroup = null;
    
    protected $_user = null;
    protected $_groups = [];
    protected $_enabled = false;
    protected $_currentUser = null;
    
    /**
     * Get's the apache user
     *
     * @var string
     */
    CONST GETUSER = "ps -axo user:19,comm | egrep '(httpd|apache2|apache)' | grep -v `whoami` | grep -v root | head -n1 | awk '{print $1}'";
    
    /**
     * Add the username and this will return the groups of that user.
     *
     * @var string
     */
    CONST GETGROUP = "groups %s";
    
    /**
     *
     */
    public function __construct()
    {
        $this->_shell = new \Quik\Shell;
        $currentUser = new \Quik\User;
        
        // Is the current user in the apache group?
        if ($currentUser->getUser() !== 'root') {
            if (in_array($currentUser->getUser(), $this->_getGroups())) {
                $this->_apacheuser = $currentUser->getUser();
                $this->_apachegroup = $this->_getUser();
                return;
            }
        }
        
        $this->_apacheuser = $this->_getUser();
        // @todo should make this more intelligent to determine the exact apache group to use
        $this->_apachegroup = $this->_getUser();
    }
    
    /**
     * If we can find a username then it's enabled
     *
     * @return string|boolean
     */
    public function isEnabled()
    {
        return ($this->getUser());
    }
    
    /**
     *
     * @return NULL|string
     */
    public function getUser()
    {
        return $this->_apacheuser;
    }
    
    /**
     *
     * @return NULL|string
     */
    public function getGroup()
    {
        return $this->_apachegroup;
    }
    
    /**
     *
     * @return string|false
     */
    protected function _getUser()
    {
        if (is_null($this->_user))
        {
            $response = $this->_shell->execute(SELF::GETUSER);
            $user = trim($response->output);
            $this->_user = strlen($user) ?$user :null;
        }
        return !is_null($this->_user) ?$this->_user :false;
    }
    
    /**
     * 
     * @return array|false
     */
    protected function _getGroups()
    {
        if (empty($this->_groups))
        {
            $user = $this->_getUser();
            $response = $this->_shell->execute(SELF::GETGROUP, [$user]);
            $responseParts = explode(':', $response->output);
            if (!isset($responseParts[1])) {
                return false;
            }
            
            $responseParts = explode(' ', trim($responseParts[1]));
            $this->_groups = $responseParts;
        }
        return $this->_groups ?$this->_groups :false;
    }
}
