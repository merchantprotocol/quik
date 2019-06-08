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

use Quik\CommandAbstract;

class Deploy extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'deploy',
        'deploy:clone',
        'deploy:install',
        'deploy:view',
        'deploy:test',
        'deploy:rollout',
        'deploy:rollback'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik deploy [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' Deploying using this tool allows you to eliminate downtime when releasing a new version.'.PHP_EOL;
        echo ' Completely build a release in it\'s own contained directory for testing, then symlink it'.PHP_EOL;
        echo ' to the public production directory for instantaneous rollouts and rollbacks'.PHP_EOL;
        echo PHP_EOL;
        echo '      ./production/magento2/ (symlink target)'.PHP_EOL;
        echo '      ./releases/ v2.0.0'.PHP_EOL;
        echo '                  v2.0.1'.PHP_EOL;
        echo '                  v2.0.2'.PHP_EOL;
        echo '      ./test/magento2/ (symlink target)'.PHP_EOL;
        echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  deploy                       Show help'.PHP_EOL;
        echo '  deploy:clone <tag> <repo>    Creates a clone in a sister directory to this repository. Only <tag> is required'.PHP_EOL;
        echo '  deploy:install               If you\'re installing manually this will populate all of your files from remote locations'.PHP_EOL;
        echo '  deploy:view                  Show the rollouts in sequence'.PHP_EOL;
        echo '  deploy:test                  Symlink this release to the public testing directory'.PHP_EOL;
        echo '  deploy:rollout               Symlink this release to the public production directory'.PHP_EOL;
        echo '  deploy:rollback              Remove the existing symlink and rollback to a previous version'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  --deploy-dir           The base deploy directory to begin the rollout strategy.'.PHP_EOL;
        echo '  -h, --help             Show this message'.PHP_EOL;
        echo '  -y                     Preapprove the confirmation prompt.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * /releases/base/
     * 
     * @var string
     */
    CONST TEST_DIR = 'test'.DIRECTORY_SEPARATOR.'magento2';
    
    /**
     *
     * @var string
     */
    CONST PRODUCTION_DIR = 'production'.DIRECTORY_SEPARATOR.'magento2';
    
    /**
     *
     * @var string
     */
    CONST RELEASES_DIR = 'releases'.DIRECTORY_SEPARATOR;
    
    /**
     * Common used line break
     * @var string
     */
    CONST LB = "============================================================================= ";
    
    /**
     * 
     * @var unknown
     */
    protected $_base_dir = null;
    
    /**
     * 
     * @param \Quik\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->_loadReleasesJsonData();
    }
    
    /**
     * 
     */
    public function execute()
    {
        $baseDir = $this->_getBaseDir();
        if (!$this->confim("Create the deploy folder architecture at {$baseDir}")) {
            $this->echo('Aborted');
            exit(0);
        }
        
        $this->echo("Creating deploy file structure at {$baseDir}", SELF::YELLOW);
        $this->_shell->execute('mkdir -p %s', [$baseDir]);
        $this->_shell->execute('mkdir -p %s', [$this->_getProductionDir()]);
        $this->_shell->execute('mkdir -p %s', [$this->_getTestDir()]);
        $this->_shell->execute('mkdir -p %s', [$this->_getReleasesDir()]);
        $this->_saveReleasesJsonData();
    }
    
    /**
     * 
     */
    public function executeTest()
    {
        $this->show_status(0,100, 'Starting TEST Rollout');
        
        $tag = $this->_getGitTag();
        $release = $this->_getReleasesDir().$tag;
        
        if (!$this->_createSymlink($release, $this->_getTestDir())) {
            $this->echo(PHP_EOL."Could not deploy $tag to test location. ({$this->_getTestDir()})", SELF::RED);
            exit(0);
        }
        
        $this->show_status(25,100, 'Clearing Cache');
        $this->run(" --dir={$release} n -q cache:clean");
        $this->run(" --dir={$release} n -q cache:flush");
        
        $this->show_status(100,100, 'Done');
        $this->echo("$tag was rolled out to the test location.");
    }
    
    /**
     *
     */
    public function executeRollout()
    {
        $this->echo(SELF::LB.PHP_EOL.PHP_EOL,SELF::YELLOW,false);
        $this->show_status(0,100, 'Starting PRODUCTION Rollout');
        
        $tag = $this->_getGitTag();
        $release = $this->_getReleasesDir().$tag;
        $this->_saveNewReleaseItem($tag);
        
        if (!$this->_createSymlink($release, $this->_getProductionDir())) {
            $this->echo(PHP_EOL."Could not deploy $tag to production location. ({$this->_getProductionDir()})", SELF::RED);
            exit(0);
        }
        
        $this->show_status(25,100, 'Clearing Cache');
        $this->run(" --dir={$release} n -q cache:clean");
        $this->run(" --dir={$release} n -q cache:flush");
        
        $this->show_status(100,100, 'Done');
        $this->echo("$tag was rolled out to the production location.");
        $this->echo(PHP_EOL.SELF::LB,SELF::YELLOW);
    }
    
    /**
     *
     */
    public function executeRollback()
    {
        $this->echo(SELF::LB.PHP_EOL.PHP_EOL,SELF::YELLOW,false);
        
        @list($tag, $repo) = $this->getArgs();
        if (!$tag) {
            $found = false;
            foreach($this->_releases_json as $key => $item) {
                if ($found) {                
                    $previous = $item;
                    break;
                }
                if ($item['tag']==$this->_getCurrentRelease()) {
                    $found = true;
                }
            }
            $tag = $previous['tag'];
        }
        $release = $this->_getReleasesDir().$tag;
        if (!file_exists($release)) {
            $this->echo("$tag does not exist. Please use an existing tag.");
            $this->echo(PHP_EOL.SELF::LB,SELF::YELLOW);
            exit(0);
        }
        
        $this->echo("Rolling back to version $tag");
        $this->show_status(0,100, "Swapping codebase");
        if (!$this->_createSymlink($release, $this->_getProductionDir())) {
            $this->echo(PHP_EOL."Could not deploy $tag to production location. ({$this->_getProductionDir()})", SELF::RED);
            exit(0);
        }
        
        $this->show_status(25,100, 'Clearing Cache');
        $this->run(" --dir={$release} n -q cache:clean");
        $this->run(" --dir={$release} n -q cache:flush");
        
        $this->show_status(100,100, 'Done');
        $this->echo("$tag was rolled out to the production location.");
        $this->echo(PHP_EOL.SELF::LB,SELF::YELLOW);
    }
    
    /**
     *
     */
    public function executeView()
    {
        $this->echo('History of Rollouts');
        $first = true;
        foreach($this->_releases_json as $key => $item) {
            if ($first && $item['tag']==$this->_getCurrentRelease()) {
                $this->echo(SELF::LB.PHP_EOL);
            }
            
            $this->echo(" $key) ".$item['tag'], $first&&$item['tag']==$this->_getCurrentRelease() ?SELF::GREEN :SELF::YELLOW, false);
            $this->echo("   Rolled out on ".$item['date'], $first&&$item['tag']==$this->_getCurrentRelease() ?SELF::GREEN :SELF::NC);
            
            if ($first && $item['tag']==$this->_getCurrentRelease()) {
                $first = false;
                $this->echo(PHP_EOL.SELF::LB);
            }
        }
    }
    
    /**
     * 
     */
    public function executeClone()
    {
        $tag = $this->_getGitTag();
        $url = $this->getUrl();
        $sistDir = $this->_getReleasesDir().$tag;
        if (file_exists($sistDir) && strlen($sistDir) > 3) {
            $this->_shell->execute("rm -rf %s", [$sistDir]);
        }
        
        $this->echo("git clone --depth=1 $url $sistDir", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("git clone --depth=1 $url $sistDir",[],true);
        
        $this->echo("git --git-dir=$sistDir/.git --work-tree=$sistDir fetch --tags", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("git --git-dir=$sistDir/.git --work-tree=$sistDir fetch --tags",[],true);
        
        $this->echo("git --git-dir=$sistDir/.git --work-tree=$sistDir checkout tags/$tag -b $tag", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("git --git-dir=$sistDir/.git --work-tree=$sistDir checkout tags/$tag -b $tag");
        
        $modulesDir = $this->_app->getWebrootDir().'.git'.DIRECTORY_SEPARATOR.'modules';
        $sisModulesDir = $sistDir.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR;
        $this->echo("cp -r $modulesDir $sisModulesDir", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("cp -r $modulesDir $sisModulesDir");
        
        $envPhp = $this->_app->getWebrootDir().'app'.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.'env.php';
        $sisEnvPhp = $sistDir.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'etc';
        $this->echo("cp $envPhp $sisEnvPhp", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("cp $envPhp $sisEnvPhp");
        
        $this->echo("Composer Install", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("composer install -d $sistDir --no-dev");
        $this->_shell->execute("composer dump-autoload -d $sistDir --no-dev --optimize");
        
        $this->echo("git --git-dir=$sistDir/.git --work-tree=$sistDir submodule update --init --recursive", \Quik\CommandAbstract::YELLOW);
        $response = $this->_shell->execute("git --git-dir=$sistDir/.git --work-tree=$sistDir submodule update --init --recursive");
        if (strpos($response->output, 'fatal: /usr/lib/git-core/git-submodule')!==false) {
            $this->_shell->execute("cd $sistDir && git submodule update --init --recursive",[],true);
        }
        
        $this->run("media --dir=$sistDir -y");
        $this->run("prod:build --dir=$sistDir -y");
    }
    
    /**
     * 
     */
    public function executeInstall()
    {
        $tag = $this->_getGitTag();
        $sistDir = dirname($this->_app->getWebrootDir()).DIRECTORY_SEPARATOR.$tag;
        
        $this->echo("Composer Install", \Quik\CommandAbstract::YELLOW);
        $this->_shell->execute("composer install -d $sistDir --no-dev");
        $this->_shell->execute("composer dump-autoload -d $sistDir --no-dev --optimize");
        
        $this->echo("Git Submodule Update", \Quik\CommandAbstract::YELLOW);
        $response = $this->getShell()->execute('git submodule update --init --recursive');
    }
    
    /**
     * 
     * @var string
     */
    protected $_tag = null;
    
    /**
     *
     * @return boolean
     */
    protected function _getGitTag()
    {
        if (is_null($this->_tag)) {
            $tag = false;
            @list($tag, $repo) = $this->getArgs();
            if (!$tag) {
                $this->echo('Please include the release tag.', \Quik\CommandAbstract::YELLOW);
                if (empty($this->_getTags())) {
                    exit(0);
                } else {
                    $this->echo('The following releases have already been pulled down.');
                    foreach($this->_getTags() as $tag) {
                        $this->echo('     - '.$tag);
                    }
                    $this->echo('');
                    if ($tag = $this->prompt("Enter the release/tag you want to use:")) {
                        if (!$tag) {
                            exit(0);
                        }
                    }
                }
            }
            $this->_tag = $tag;
        }
        return $this->_tag;
    }
    
    /**
     *
     * @return array
     */
    protected function _getTags()
    {
        return array_column($this->_releases_json, 'tag');
    }
    
    /**
     *
     * @return boolean|string
     */
    protected function _getCurrentRelease()
    {
        $response = $this->_shell->execute('ls -la %s | grep "\->"', [dirname($this->_getProductionDir())]);
        list($na, $link) = explode("->", $response->output);
        if (!$link) return false;
        return str_replace($this->_getReleasesDir(),'',trim($link));
    }
    
    /**
     * [
     *   'tag'  = 'v2.0.1',
     *   'date' = '2019-01-01 12:59:59',
     *   'dir'  = '/'
     * ]
     *
     * @var array
     */
    protected $_releases_json = [];
    
    /**
     *
     * @return string
     */
    protected function _saveNewReleaseItem($tag)
    {
        if (!$tag) return false;
        array_unshift($this->_releases_json, [
            'tag'  => $tag,
            'date' => date('Y/m/d H:i:s'),
            'dir'  => dirname($this->_app->getWebrootDir()).DIRECTORY_SEPARATOR.$tag
        ]);
        $this->_saveReleasesJsonData();
    }
    
    /**
     *
     */
    protected function _saveReleasesJsonData()
    {
        return file_put_contents($this->_getReleasesJsonPath(), json_encode($this->_releases_json));
    }
    
    /**
     *
     */
    protected function _loadReleasesJsonData()
    {
        $this->_releases_json = json_decode(file_get_contents($this->_getReleasesJsonPath()), true);
        if (!$this->_releases_json || empty($this->_releases_json)) {
            $this->_releases_json = [];
        }
    }
    
    /**
     *
     * @return string
     */
    protected function _getReleasesJsonPath()
    {
        return $this->_getBaseDir().self::RELEASES_JSON;
    }
    
    /**
     *
     * @return string
     */
    protected function _getProductionDir()
    {
        return $this->_getBaseDir().SELF::PRODUCTION_DIR;
    }
    
    /**
     *$repo
     * @return string
     */
    protected function _getReleasesDir()
    {
        return $this->_getBaseDir().SELF::RELEASES_DIR;
    }
    
    /**
     *
     * @return string
     */
    protected function _getTestDir()
    {
        return $this->_getBaseDir().SELF::TEST_DIR;
    }
    
    /**
     *
     * @param string $source
     * @param string $destination
     */
    protected function _createSymlink( $source, $destination )
    {
        if (strlen($source)<3) return false;
        if (!file_exists($source)) return false;
        if (strlen($destination)<3) return false;
        if (file_exists($destination)) {
            $this->_shell->execute("rm -rf %s", [$destination]);
        }
        $this->_shell->execute("ln -s %s %s", [$source,$destination]);
        return true;
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
        if (!$repo) {
            $this->echo('Please Specify a Repository URL to clone', SELF::YELLOW);
            exit(0);
        }
        return $repo;
    }
    
    /**
     * 
     * @return string|boolean
     */
    protected function _getRepoUrl()
    {
        $gitConfigString = file_get_contents($this->_app->getWebrootDir().'.git'.DIRECTORY_SEPARATOR.'config');
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
