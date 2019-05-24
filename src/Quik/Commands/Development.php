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

class Development extends \Quik\CommandAbstract
{
    /**
     * 
     * @var array
     */
    public static $commands = ['dev', 'dev:build'];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo 'Usage: '.\Quik\CommandAbstract::GREEN.'quik dev [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo PHP_EOL;
        echo 'Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -m, --mode          Place this project into developer mode.'.PHP_EOL;
        echo '  -g, --gitignore     Deploy the server specific gitignore file for this project.'.PHP_EOL;
        echo '  -t, --tail          Tail all of the error logs at once.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * Build Magento
     */
    public function execute()
    {
        if ($this->getParameters()->getCalledCommand()=='dev:build') {
            $this->executeDevBuild();
            return;
        }
        if ($this->getParameters()->getCount() == 1) {
            $this->showUsage();
            exit(0);
        }
        if ($this->getMode()) {
            $this->executeMode();
        }
        if ($this->getGitignore()) {
            $this->executeGitignore();
        }
        if ($this->getTail()) {
            $this->executeTail();
        }
    }
    
    /**
     * 
     */
    public function executeDevBuild()
    {
        // check mode
        $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:show');
        $mode = substr($response->output, strlen('Current application mode: '),9);
        if ($mode == 'productio') {
            if (!$this->confirm("The current mode is not set to developer. Would you like to update it?"))
            {
                $this->echo("For production environments we suggest that you use `quik build`. Aborting...", SELF::YELLOW);
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
        
        if ($mode == 'productio') {
            $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:set developer');
            $this->echo($response->output);
        }
        
        $this->echo('Updating Permissions...');
        $this->run("permissions -y -q $userGroup");
        $this->echo('Done Updating Permissions!');
        
        $this->run("clear -y");
        $response = $this->_shell->execute($this->getBinMagento().' app:config:import');
        $this->echo($response->output);
        $response = $this->_shell->execute($this->getBinMagento().' setup:upgrade');
        $this->echo($response->output);
        $response = $this->_shell->execute($this->getBinMagento().' indexer:reindex');
        $this->echo($response->output);
        
        $this->echo('Updating Permissions...');
        $this->run("permissions -y -q $userGroup");
        $this->echo('Done Updating Permissions!');
    }
    
    /**
     * 
     */
    public function executeMode()
    {
        $response = $this->_shell->execute($this->getBinMagento()." deploy:mode:set developer");
        $this->echo($response->output);
        $this->run('clear -y');
    }
    
    /**
     * 
     */
    public function executeGitignore()
    {
        if (file_exists(dirname($this->getExcludePath()))) {
            $this->echo('Installing local gitignore file at '.$this->getExcludePath());
            file_put_contents($this->getExcludePath(), $this->getGitSource(), FILE_APPEND);
            // test
            $source = file_get_contents($this->getExcludePath());
            if (!strlen($source)) {
                $this->echo('Could not write to the local gitignore location...', SELF::YELLOW);
            }
        } else {
            $this->echo('This does not seem to be a valid git repository:'.PHP_EOL
                .$this->_app->getGitDir(), SELF::YELLOW);
        }
    }
    
    
    public function getExcludePath()
    {
        return $this->_app->getGitDir().DIRECTORY_SEPARATOR.'info'.DIRECTORY_SEPARATOR.'exclude';
    }
    
    /**
     * Return the contents of the development gitignore file
     * @return string
     */
    public function getGitSource()
    {
        $filename = $this->_app->getQuikDir().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'development_gitignore';
        return file_get_contents($filename);
    }
    
    /**
     * 
     */
    public function executeTail()
    {
        $patterns = [
            $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'*.log'
        ];
        $files = [];
        foreach ($patterns as $pattern) {
            foreach(glob($pattern) as $file) {
                $files[] = $file;
            }
        }
        
        $handle = popen("tail -f ".implode(' ', $files)." 2>&1", 'r');
        while(!feof($handle)) {
            $buffer = fgets($handle);
            echo "$buffer<br/>\n";
            ob_flush();
            flush();
        }
//         pclose($handle);
    }
    
    /**
     * 
     * @return boolean
     */
    public function getMode()
    {
        return $this->getParameters()->get('__development_m');
    }
    
    /**
     *
     * @return boolean
     */
    public function getGitignore()
    {
        return $this->getParameters()->get('__development_g');
    }
    
    /**
     *
     * @return boolean
     */
    public function getTail()
    {
        return $this->getParameters()->get('__development_t');
    }
}
