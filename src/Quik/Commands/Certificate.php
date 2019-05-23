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

class Certificate extends \Quik\CommandAbstract
{
    /**
     * 
     * @var array
     */
    public static $commands = ['cert'];
    
    /**
     * 
     * @var string
     */
    CONST FQDN_PATTERN = '/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/m';
    
    /**
     * 
     * @var string
     */
    protected $_fqdn = null;
    
    /**
     * 
     */
    public function execute()
    {
        if (!$this->isValid()) {
            $this->showError();
            $this->showUsage();
            exit(0);
        }
        
        $params = [
            "/etc/ssl/private/{$this->getFqdn()}.key",
            "/etc/ssl/certs/{$this->getFqdn()}.crt"
            ];
        
        $this->echo('Generating a RSA private key'.PHP_EOL.'....................................+++++');
        $this->echo("writing new private key to {$params[0]}".PHP_EOL.'-----');
        $this->echo('You are about to be asked to enter information that will be incorporated'.PHP_EOL
        .'into your certificate request.'.PHP_EOL
        .'What you are about to enter is what is called a Distinguished Name or a DN.'.PHP_EOL
        .'There are quite a few fields but you can leave some blank'.PHP_EOL
        .'For some fields there will be a default value,'.PHP_EOL
        ."If you enter '.', the field will be left blank.".PHP_EOL
        .'-----');
        
        $country    = $this->prompt('Country Name (2 letter code) [US]:');
        $state      = $this->prompt('State or Province Name (full name) [Idaho]:');
        $city       = $this->prompt("Locality Name (eg, city) [Coeur d'Alene]:");
        $company    = $this->prompt('Organization Name (eg, company) [Merchant Protocol, LLC]:');
        $unit       = $this->prompt('Organizational Unit Name (eg, section) [DevOps]:');
        $email      = $this->prompt('Email Address [...]:');
        
        $subject = "/C=$country/ST=$state/L=$city/O=$company/OU=$unit/CN={$this->getFqdn()}/emailAddress=$email";
        $params[] = $subject;
        
        $this->echo('Creating local ssl certificate', SELF::YELLOW);
        $this->_shell->execute("mkdir -p /etc/ssl/private/");
        $this->_shell->execute("mkdir -p /etc/ssl/certs/");
        
        $this->_shell->execute("sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout %s -out %s -subj %s", $params);
        // show the results
        $keyfile = $this->_shell->execute("sudo cat %s", [$params[0]]);
        if (strpos($keyfile->output, 'No such file or directory') === false) {
            echo $params[0].PHP_EOL;
            echo PHP_EOL;
            echo $keyfile->output.PHP_EOL;
            echo PHP_EOL;
            echo PHP_EOL;
        } else {
            $this->echo("The file {$params[0]} was not created.", SELF::YELLOW);
        }
        if (file_exists($params[1])) {
            $this->echoFile( $params[1] );
        } else {
            $this->echo("The file {$params[1]} was not created.", SELF::YELLOW);
        }
        
        $this->echo("Your Certificates are ready to use!", SELF::GREEN);
    }
    
    /**
     * 
     */
    public function echoFile( $path )
    {
        $response = $this->_shell->execute("cat $path");
        echo $path.PHP_EOL;
        echo PHP_EOL;
        echo $response->output.PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo 'Usage: quik cert [options] <FQDN.domain.name>'.PHP_EOL;
        echo PHP_EOL;
        echo 'Options:'.PHP_EOL;
        echo '  -h, --help      Show this message'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Display the help information
     *
     */
    public function showError()
    {
        echo PHP_EOL;
        echo \Quik\CommandAbstract::RED.'You did not submit a Fully Qualified Domain Name (FQDN) @see quik cert --help.'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * 
     * @return string
     */
    public function getFqdn()
    {
        if (is_null($this->_fqdn))
        {
            $args = $this->getArgs();
            if (isset($args[0])) {
                $this->_fqdn = $args[0];
            }
        }
        return $this->_fqdn;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isValid()
    {
        return preg_match_all(SELF::FQDN_PATTERN, $this->getFqdn(), $matches, PREG_SET_ORDER, 0);
    }
}
