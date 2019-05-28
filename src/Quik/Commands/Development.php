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
    public static $commands = [
        'dev', 
        'dev:build',
        'dev:tail',
        'dev:gitignore:local',
        'dev:gitignore',
        'dev:set'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo 'Usage: '.\Quik\CommandAbstract::GREEN.'quik dev [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  dev                 '.PHP_EOL;
        echo '  dev:set             Set the mode to developer'.PHP_EOL;
        echo '  dev:tail            tail -f var/log/*.log'.PHP_EOL;
        echo '  dev:gitignore       Set the production level git ignore file. All generated code gets placed in the repo'.PHP_EOL;
        echo '  dev:gitignore:local Set the local git ignore file. No generated code gets placed in the repo'.PHP_EOL;
        echo '  dev:build           Compiles the code for a development server'.PHP_EOL;
        echo PHP_EOL;
        echo 'Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo '  -y                  Preapprove the confirmation prompt.'.PHP_EOL;
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
     *
     */
    public function executeBuild()
    {
        // check mode
        $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:show');
        $mode = substr($response->output, strlen('Current application mode: '),9);
        if ($mode == 'productio') {
            if (!$this->confirm("The current mode is not set to developer. Would you like to update it?"))
            {
                $this->echo("For production environments we suggest that you use `quik prod:build`. Aborting...", SELF::YELLOW);
                exit(0);
            }
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
        
        $userGroup = '';
        if ($this->getUser() || $this->getGroup()) {
            $userGroup = $this->getUser().':'.$this->getGroup();
        }
        
        if ($mode == 'productio') {
            $this->show_status(0,100, 'Enabling Developer Mode');
            $this->_shell->execute($this->getBinMagento().' deploy:mode:set developer');
        }
        
        $this->show_status(5,100, 'Updating Permissions');
        $this->run("permissions -y -q $userGroup");
        
        $this->show_status(25,100, 'Clearing Directories');
        $this->run("clear -y -q");
        
        $this->show_status(35,100, 'Running Composer');
        $this->_shell->execute("composer install");
        $this->_shell->execute("composer update");
        
        $this->show_status(45,100, 'Inporting Configuration');
        $response = $this->_shell->execute($this->getBinMagento().' app:config:import');
        
        $this->show_status(50,100, "Disabling Merged Assets");
        $this->run("n -q config:store:set dev/css/merge_css_files 0");
        $this->run("n -q config:store:set dev/css/minify_files 0");
        $this->run("n -q config:store:set dev/js/merge_files 0");
        $this->run("n -q config:store:set dev/js/enable_js_bundling 0");
        $this->run("n -q config:store:set dev/js/minify_files 0");
        
        $this->show_status(60,100, 'Running setup:upgrade');
        $response = $this->_shell->execute($this->getBinMagento().' setup:upgrade');
        
        $this->show_status(80,100, 'Updating Permissions');
        $this->run("permissions -y -q $userGroup");
        $this->show_status(100,100, 'Done with dev:build');
    }
    
    /**
     *
     */
    public function executeSet()
    {
        $response = $this->_shell->execute($this->getBinMagento()." deploy:mode:set developer --skip-compilation");
        $this->echo($response->output);
        $this->run('clear -y');
    }
    
    /**
     *
     */
    public function executeGitignore()
    {
        $this->echo('Installing gitignore file at '.$this->getGitignorePath());
        file_put_contents($this->getGitignorePath(), $this->getGitSource(), FILE_APPEND);
        // test
        $source = file_get_contents($this->getGitignorePath());
        if (!strlen($source)) {
            $this->echo('Could not write to the gitignore location...', SELF::YELLOW);
        }
    }
    
    /**
     *
     */
    public function executeGitignoreLocal()
    {
        if (file_exists(dirname($this->getExcludePath()))) {
            $this->echo('Installing local gitignore file at '.$this->getExcludePath());
            file_put_contents($this->getExcludePath(), $this->getExcludeSource(), FILE_APPEND);
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
     * @return string
     */
    public function getExcludePath()
    {
        return $this->_app->getGitDir().DIRECTORY_SEPARATOR.'info'.DIRECTORY_SEPARATOR.'exclude';
    }
    
    /**
     * Return the contents of the development gitignore file
     * @return string
     */
    public function getExcludeSource()
    {
        $filename = $this->_app->getQuikDir().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'development_gitignore';
        return file_get_contents($filename);
    }
    
    /**
     * Return the contents of the development gitignore file
     * @return string
     */
    public function getGitSource()
    {
        $filename = $this->_app->getQuikDir().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'production_gitignore';
        return file_get_contents($filename);
    }
}
