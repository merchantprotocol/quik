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

class Media extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'media',
        'media:move',
        'media:ln',
        'media:archive'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik deploy [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' Helps with managing a single media directory for all releases'.PHP_EOL;
        echo PHP_EOL;
        echo ' Commands:'.PHP_EOL;
        echo '  media              1. Move the local media to the base location 2. Take a backup 3. Remove the local media dir 4. Symlink from base to local.'.PHP_EOL;
        echo '  media:ls           Symlink the media directory to a release directory'.PHP_EOL;
        echo '  media:archive      Archive the media directory and back it up to Amazon S3'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help         Show this message'.PHP_EOL;
        echo '  -y                 Preapprove the confirmation prompt.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * /media/
     *
     * @var string
     */
    CONST MEDIA_DIR = 'media'.DIRECTORY_SEPARATOR;
    
    /**
     * /media-backups/
     *
     * @var string
     */
    CONST MEDIA_BACKUPS_DIR = 'media-backups'.DIRECTORY_SEPARATOR;
    
    /**
     * 
     * @var unknown
     */
    protected $_media_dir = null;
    
    /**
     * 
     * @param \Quik\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    /**
     * 1. Move the local media to the base location
     * 2. Take a backup
     * 3. Remove the local media dir
     * 4. Symlink from base to local
     */
    public function execute()
    {
        if (!file_exists($this->_getBaseDir())) {
            $this->echo('This installation is not configured for zero downtime deployments.');
            exit(0);
        }
        if (!file_exists($this->_getBaseMediaDir()) && !$this->confirm('Do you want to continue moving the media dir to '.$this->_getBaseMediaDir())) {
            $this->echo('Aborting',SELF::RED);
            exit(0);
        }
        if (!file_exists($this->_getActualMediaDir())) {
            $this->echo('Cannot determine Actual Media Directory, Aborting', SELF::RED);
            exit(0);
        }
        
        // if the actual is still local then lets move it to base
        if ($this->_getActualMediaDir() !== $this->_getBaseMediaDir()) {
            // if the local exists then copy it
            if (file_exists($this->_getActualMediaDir())) {
                
                $from = str_replace($this->_getBaseDir(), '', $this->_getActualMediaDir());
                $to = str_replace($this->_getBaseDir(), '', $this->_getBaseMediaDir());
                $this->show_status(0,100, "Copy from $from to $to");
                
                if (file_exists($this->_getBaseMediaDir())) {
                    $this->_shell->execute('cp -rLfpu '.$this->_getActualMediaDir().'* %s', [$this->_getBaseMediaDir()]);
                    if (!file_exists($this->_getBaseMediaDir().'.htaccess')) {
                        $this->_shell->execute('cp -rLfpu '.$this->_getActualMediaDir().'.htaccess %s', [$this->_getBaseMediaDir()]);
                    }
                } else {
                    $this->_shell->execute('cp -rLfpu '.rtrim($this->_getActualMediaDir(),DIRECTORY_SEPARATOR).' %s', [$this->_getBaseMediaDir()]);
                }
            }
            // take a backup of base
            $this->show_status(30,100, 'Starting Media Folder Backup');
            if ($this->executeArchive()) {
                // remove local
                if (file_exists($this->_getActualMediaDir()) && strlen($this->_getActualMediaDir())>3) {
                    $this->show_status(60,100, 'Removing original media directory');
                    $this->_shell->execute('rm -rf %s', [$this->_getActualMediaDir()]);
                }
            }
        }
        
        // reset the media_dir
        $this->_media_dir = null;
        // if actual is now base then create symlinks
        if ($this->_getActualMediaDir() == $this->_getBaseMediaDir()) {
            $this->show_status(100,100, 'Completing Symlink back to local media dir');
            $this->executeLn();
        }
    }
    
    /**
     * Archive the base media directory
     */
    public function executeArchive()
    {
        if (!file_exists($this->_getMediaBackupsDir())) {
            $this->_shell->execute('mkdir -p %s',[$this->_getMediaBackupsDir()]);
        }
        
        $archiveFilename = $this->_getMediaBackupsDir().date('Y_m_d__H_i_s').'.tar';
        $this->_shell->execute("tar -cf %s %s", [$archiveFilename, $this->_getBaseMediaDir()]);
        $archiveSize = $this->_shell->execute("du -sh %s",[$archiveFilename]);
        list($size, $path) = explode("\t", $archiveSize->output);
        
        if (!$size) {
            $this->echo(PHP_EOL.'Something prevented us from archiving the media directory', SELF::RED);
            return false;
        }
        return true;
    }
    
    /**
     * Execute a symlink from the actual media dir to this installation
     */
    public function executeLn()
    {
        $media = str_replace($this->_getBaseDir(), '', $this->_getActualMediaDir());
        if ($media !== SELF::MEDIA_DIR) {
            $this->echo('The actual media directory must be at the base location first. Run `quik media` to setup.');
            exit(0);
        }
        $this->_shell->execute("ln -s %s %s", [$this->_getActualMediaDir(), rtrim($this->_getLocalMediaDir(),DIRECTORY_SEPARATOR)]);
        $this->echo('Symlinked base to local media', SELF::GREEN);
    }
    
    /**
     *
     * @return boolean|string
     */
    protected function _getBaseMediaDir()
    {
        return $this->_getBaseDir().SELF::MEDIA_DIR;
    }
    
    /**
     *
     * @return boolean|string
     */
    protected function _getMediaBackupsDir()
    {
        return $this->_getBaseDir().SELF::MEDIA_BACKUPS_DIR;
    }
    
    /**
     *
     * @return string
     */
    protected function _getActualMediaDir()
    {
        if (is_null($this->_media_dir))
        {
            $response = $this->_shell->execute('ls -la %s | grep "\->"', [$this->_getLocalMediaDir()], false, false);
            list($na, $link) = array_pad(explode("->", $response->output), 2, null);
            if ($link) {
                $this->_media_dir = rtrim(trim($link),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            } elseif (file_exists($this->_getBaseMediaDir().'.htaccess')) {
                $this->_media_dir = $this->_getBaseMediaDir();
            } else {
                $this->_media_dir = $this->_getLocalMediaDir();
            }
        }
        return $this->_media_dir;
    }
    
    /**
     *
     * @return string
     */
    protected function _getLocalMediaDir()
    {
        return $this->_app->getWebrootDir().'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR;
    }
}
