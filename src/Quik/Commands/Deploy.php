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

class Deploy extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'deploy',
        'deploy:clone'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik deploy [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' '.PHP_EOL;
        echo ' '.PHP_EOL;
        echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  deploy              '.PHP_EOL;
        echo '  deploy:clone <tag> <repo>      Creates a clone in a sister directory to this repository. Only <tag> is required'.PHP_EOL;
        echo '  deploy:              '.PHP_EOL;
        echo '  deploy              '.PHP_EOL;
        echo '  deploy              '.PHP_EOL;
        echo '  deploy              '.PHP_EOL;
        echo '  deploy              '.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -y                  Preapprove the confirmation prompt.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * 
     */
    public function execute()
    {
        $this->showUsage();
    }
    
    
    public function executeClone()
    {
        @list($tag, $repo) = $this->getArgs();
        if (!$tag) {
            $this->echo('Please include the tag or commit to clone', \Quik\CommandAbstract::YELLOW);
            exit(0);
        }
        
        $url = $this->getUrl();
        $sistDir = dirname($this->_app->getWebrootDir()).DIRECTORY_SEPARATOR.$tag;
        
        $this->echo("git clone --depth=1 $url $sistDir", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("git clone --depth=1 $url $sistDir",[],true);
        
        $this->echo("git --git-dir=$sistDir/.git --work-tree=$sistDir fetch --tags", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("git --git-dir=$sistDir/.git --work-tree=$sistDir fetch --tags",[],true);
        
        $this->echo("git --git-dir=$sistDir/.git --work-tree=$sistDir checkout tags/$tag -b $tag", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("git --git-dir=$sistDir/.git --work-tree=$sistDir checkout tags/$tag -b $tag");
        
        $modulesDir = $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'modules';
        $sisModulesDir = $sistDir.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR;
        $this->echo("cp -r $modulesDir $sisModulesDir", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("cp -r $modulesDir $sisModulesDir");
        
        $this->echo("git --git-dir=$sistDir/.git --work-tree=$sistDir submodule update --init --recursive", \Quik\CommandAbstract::YELLOW);
        $response = $this->_shell->execute("git --git-dir=$sistDir/.git --work-tree=$sistDir submodule update --init --recursive");
        if (strpos($response->output, 'fatal: /usr/lib/git-core/git-submodule')!==false) {
            $this->_shell->execute("cd $sistDir && git submodule update --init --recursive",[],true);
        }
        
        $envPhp = $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.'env.php';
        $sisEnvPhp = $sistDir.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'etc';
        $this->echo("cp $envPhp $sisEnvPhp", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("cp $envPhp $sisEnvPhp");
        
        $this->echo("Composer Install", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("composer install -d $sistDir --no-dev");
        $this->_shell->execute("composer dump-autoload -d $sistDir --no-dev --optimize");
        $this->_shell->execute("composer update -d $sistDir --no-dev");

        $this->run("prod:build --dir=$sistDir -y");
    }
    
    
    public function executeInstall()
    {
        $this->getShell()->execute('git submodule update --init --recursive');
        $this->run('dev:build -y');
    }
    
    /**
     * 
     * @return boolean|string
     */
    protected function getUrl()
    {
        @list($tag, $repo) = $this->getArgs();
        if (!$repo) {
            $repo = $this->_getRepoUrl();
        }
        return $repo;
    }
    
    /**
     * 
     * @return string|boolean
     */
    protected function _getRepoUrl()
    {
        $gitConfigString = file_get_contents($this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'config');
        $options = [];
        foreach(explode(PHP_EOL, $gitConfigString) as $line) {
            $new = '';
            if (strpos($line, '[') !== false) {
                $new = $line;
                
                if (isset($option['_remote']) && $option['_remote']) {
                    $options[] = $option;
                    return $option['url'];
                }
            }
            
            ##############  Build New  ###############
            if ($new) {
                $option = [];
                $option['title'] = $new;
                if (strpos($new,'[remote ')!==false) {
                    $option['_remote'] = true;
                }
                continue;
            }
            list($key, $value) = explode('=', trim($line));
            $option[trim($key)] = trim($value);
        }
        return false;
    }
}
