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

class Changelog extends \Quik\CommandAbstract
{
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo ' Usage: '.\Quik\CommandAbstract::GREEN.'quik changelog [options]'.\Quik\CommandAbstract::NC.PHP_EOL;
        echo ' This command should be run after a successful QC test has been completed. A'.PHP_EOL;
        echo ' list of your commits will be organized and your changelog will be updated.'.PHP_EOL;
        echo ' Use this in conjunction with `quik version` to create a proper release.'.PHP_EOL;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help          Show this message'.PHP_EOL;
        echo PHP_EOL;
        echo ' Run `quik changelog` and we\'ll use your local git repo to update '.SELF::CHANGLOG_FILENAME.PHP_EOL;
        echo ' since your last tag.'.PHP_EOL;
        echo PHP_EOL;
    }
    
    /**
     * 
     * @var string
     */
    CONST CHANGLOG_FILENAME = 'CHANGELOG.md';
    
    /**
     * Build Magento
     */
    public function execute()
    {
        // Check that the working directory is clean
        $this->_shell->execute("cd ".$this->_app->getWebrootDir());
        $status = $this->_shell->execute("git status");
        if (strpos($status->output, 'nothing to commit, working tree clean') === false) {
            if (!$this->confirm("Your working directory has unresolved changes. Do you want to continue?")) {
                $this->echo('Aborting...', SELF::RED);
                exit(0);
            }
        }
        
        $this->show_status(10,100);
        $filename = $this->_app->getWebrootDir().DIRECTORY_SEPARATOR.SELF::CHANGLOG_FILENAME;
        
        // building the changlog text
        $changlog = 'DEVELOPMENT'.PHP_EOL.'============='.PHP_EOL;
        $changlog .= $this->getFormattedCommits();
        $this->show_status(40,100);
        $changlog .= file_get_contents($filename);
        $this->show_status(80,100);
        
        // update the changelog
        file_put_contents( $filename, $changlog );
        $this->show_status(100,100);
        $this->echo('Changlog has been updated!', SELF::GREEN);
    }
    
    /**
     * 
     * @return \Quik\Commands\unknown
     */
    public function getFormattedCommits()
    {
        $logRaw = $this->_getCommitHistory();
        $logArray = $this->_parseLog($logRaw);
        return $this->_formatLog($logArray);
    }
    
    /**
     * 
     * @return string
     */
    protected function _getCommitHistory()
    {
        // get tag information
        $this->_shell->execute("git fetch --tags");
        $tagsString = $this->_shell->execute("git tag --list");
        $tagsArray = explode(PHP_EOL, $tagsString->output);
        $lastTag = end($tagsArray);
        // git log
        if ($lastTag) {
            $cmd = "git log --no-merges -1 --format=%ai $lastTag";
            $lastTagDate = $this->_shell->execute($cmd);
            $logString = $this->_shell->execute("git log --no-merges --since='$lastTagDate->output'");
        } else {
            $logString = $this->_shell->execute("git log --no-merges --full-history");
        }
        return $logString->output;
    }
    
    /**
     * Parse the git log history into an array
     * 
     * @param string $logString
     * @return array
     */
    protected function _parseLog( $logString )
    {
        $history = array();
        $logString .= PHP_EOL.'commit'; // A little hack to get the last message
        foreach(explode(PHP_EOL, $logString) as $line) {
            if(strpos($line, 'commit')===0) {
                if(!empty($commit)){
                    // do some last minute formatting
                    $commit['message'] = trim($commit['message']);
                    
                    $hashtags = [];
                    preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}-]+)/", $commit['message'], $matches);
                    if ($matches) {
                        $hashtagsArray = array_count_values($matches[0]);
                        $hashtags = array_keys($hashtagsArray);
                    }
                    $commit['issues'] = $hashtags;
                    
                    array_push($history, $commit);
                    $commit = [];
                }
                $commit['hash']   = trim(substr($line, strlen('commit')));
            }
            else if(strpos($line, 'Author')===0){
                $commit['author'] = trim(substr($line, strlen('Author:')));
            }
            else if(strpos($line, 'Date')===0){
                $commit['date']   = trim(substr($line, strlen('Date:')));
            }
            else{
                $commit['message']  .= $line;
            }
        }
        return $history;
    }
    
    /**
     *
     * @param unknown $logArray
     * @return unknown
     */
    protected function _formatLog( $logArray )
    {
        $log = '* Commits:'.PHP_EOL;
        foreach ($logArray as $commit) {
            $log .= '    * '.$commit['message'].PHP_EOL;
        }
        return $log.PHP_EOL;
    }
}
