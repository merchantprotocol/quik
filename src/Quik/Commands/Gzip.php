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

class Gzip extends \Quik\CommandAbstract
{
    /**
     * 
     * @var array
     */
    protected $_dirs = [
        'pub/static/'
    ];
    
    /**
     * This command creates a gzip copy of all files recursively in a folder
     * @var string
     */
    const GZIP_FILES = 'find %s -type f ! -iname "*.gz" | while read file; do gzip -c -- "$file" > "$file.gz"; done';
    
    /**
     * 
     */
    public function execute()
    {
        foreach ($this->_patterns as $pattern) {
            foreach(glob($pattern) as $filepath) {
                $this->echo($filepath);
            }
        }
        
        // precompress all files in these directories
        foreach ($this->_dirs as $dir) {
            $this->_shell->execute(SELF::GZIP_FILES, [$this->_app->getWebrootDir().DIRECTORY_SEPARATOR.$dir]);
        }
        
        // tell the user about the htaccess changes
        $this->echo("All files have been precompressed with gzip!", SELF::GREEN);
        $this->echo("To take advantage of this optimization you'll need to update your webserver. "
            ."If you're on Apache add the following code to your .htaccess file:");
        $this->echo(".htaccess ------------------------------------------------");
        echo "".PHP_EOL;
        echo "".PHP_EOL;
        echo "# AddEncoding allows you to have certain browsers uncompress information on the fly.".PHP_EOL;
        echo "AddEncoding gzip .gz".PHP_EOL;
        echo "".PHP_EOL;
        echo "#Serve gzip compressed CSS files if they exist and the client accepts gzip.".PHP_EOL;
        echo "RewriteCond %{HTTP:Accept-encoding} gzip".PHP_EOL;
        echo "RewriteCond %{REQUEST_FILENAME}\.gz -s".PHP_EOL;
        echo "RewriteRule ^(.*)\.css $1\.css\.gz [QSA]".PHP_EOL;
        echo "".PHP_EOL;
        echo "# Serve gzip compressed JS files if they exist and the client accepts gzip.".PHP_EOL;
        echo "RewriteCond %{HTTP:Accept-encoding} gzip".PHP_EOL;
        echo "RewriteCond %{REQUEST_FILENAME}\.gz -s".PHP_EOL;
        echo "RewriteRule ^(.*)\.js $1\.js\.gz [QSA]".PHP_EOL;
        echo "".PHP_EOL;
        echo "# Serve correct content types, and prevent mod_deflate double gzip.".PHP_EOL;
        echo "RewriteRule \.css\.gz$ - [T=text/css,E=no-gzip:1]".PHP_EOL;
        echo "RewriteRule \.js\.gz$ - [T=text/javascript,E=no-gzip:1]".PHP_EOL;
        echo "".PHP_EOL;
        echo "".PHP_EOL;
        $this->echo("END .htaccess --------------------------------------------");
    }
}
