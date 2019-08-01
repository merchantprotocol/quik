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

class Mysql extends \Quik\CommandAbstract
{
    /**
     *
     * @var array
     */
    public static $commands = [
        'mysql',
        'mysql:table:size',
        'mysql:cleanup',
        'mysql:dump',
//        'mysql:dump:schema',
//        'mysql:dump:limit',
        'mysql:import',
        'mysql:sanitize',
        'mysql:tunnel:kill'
    ];
    
    /**
     * Display the help information for this command
     */
    public function showUsage()
    {
        echo \Quik\CommandAbstract::YELLOW;
        echo ' Usage: '.'quik mysql [options]'.PHP_EOL;
        echo \Quik\CommandAbstract::GREEN;
        echo '  mysql                 View all of the accessible databases'.PHP_EOL;
        echo '  mysql:cleanup         Will trim quote tables and truncate log tables removing the db bloat.'.PHP_EOL;
        echo '  mysql:dump            Dump a database without locking up a production environment. '.PHP_EOL;
        echo '  mysql:dump:schema     You only get the db schema dumped '.PHP_EOL;
        echo '  mysql:dump:limit <x>  Dump the db, but limit sales and customers to x'.PHP_EOL;
        echo '  mysql:import          Import a database into approved connections.'.PHP_EOL;
        echo '  mysql:table:size      Show all table sizes, largest first'.PHP_EOL;
        echo \Quik\CommandAbstract::NC;
        echo PHP_EOL;
        echo ' Options:'.PHP_EOL;
        echo '  -h, --help         Show this message'.PHP_EOL;
        echo '  -y                 Preapprove the confirmation prompt.'.PHP_EOL;
        echo '  --no-rate-limit    Disables the rate limiting'.PHP_EOL;
        echo '  --host=             '.PHP_EOL;
        echo '  --port=             '.PHP_EOL;
        echo '  --database=         '.PHP_EOL;
        echo '  --user=             '.PHP_EOL;
        echo '  --password=         '.PHP_EOL;
        $this->showConnection();
        echo PHP_EOL;
    }
    
    public function showConnection()
    {
        echo PHP_EOL;
        echo ' Database:'.PHP_EOL;
        echo '  Proxy:            '.$this->getParameters()->getMysqlProxy().PHP_EOL;
        echo '  Host:             '.$this->_getHost().PHP_EOL;
        echo '  Port:             '.$this->_getPort().PHP_EOL;
        echo '  Database:         '.$this->_getDbname().PHP_EOL;
        echo '  Username:         '.$this->_getUsername().PHP_EOL;
        
        echo PHP_EOL;
    }
    
    protected $truncateAll = [
        'adminnotification_inbox',
        'admin_system_messages',
        'cache',
        'cache_tag',
        'captcha_log',
        'catalog_compare_item',
        'catalog_product_index_eav',
        'catalog_product_index_eav_decimal',
        'catalog_product_index_eav_decimal_idx',
        'catalog_product_index_eav_decimal_replica',
        'catalog_product_index_eav_decimal_tmp',
        'catalog_product_index_price',
        'catalog_product_index_price_bundle_idx',
        'catalog_product_index_price_bundle_opt_idx',
        'catalog_product_index_price_bundle_opt_tmp',
        'catalog_product_index_price_bundle_sel_idx',
        'catalog_product_index_price_bundle_sel_tmp',
        'catalog_product_index_price_bundle_tmp',
        'catalog_product_index_price_cfg_opt_agr_idx',
        'catalog_product_index_price_cfg_opt_agr_tmp',
        'catalog_product_index_price_cfg_opt_idx',
        'catalog_product_index_price_cfg_opt_tmp',
        'catalog_product_index_price_download_idx',
        'catalog_product_index_price_download_tmp',
        'catalog_product_index_price_final_idx',
        'catalog_product_index_price_final_tmp',
        'catalog_product_index_price_idx',
        'catalog_product_index_price_opt_agr_idx',
        'catalog_product_index_price_opt_agr_tmp',
        'catalog_product_index_price_opt_idx',
        'catalog_product_index_price_opt_tmp',
        'catalog_product_index_price_replica',
        'catalog_product_index_price_tmp',
        'catalog_product_index_tier_price',
        'catalog_product_index_website',
        'catalogsearch_fulltext_scope1',
        'catalogsearch_recommendations',
        'cron_schedule',
        'customer_log',
        'customer_visitor',
        'importexport_importdata',
        'import_history',
        'sendfriend_log'
    ];
    
    protected $trimExpired = [
        'admin_user_session',
        'session',
        'quote',
        'quote_address',
        'quote_address_item',
        'quote_id_mask',
        'quote_item',
        'quote_item_option',
        'quote_payment',
        'quote_shipping_rate',
    ];
    
    protected $dropFlat = [
        'customer_grid_flat',
        'design_config_grid_flat',
        
        'catalog_category_flat_store_1',
        'catalog_category_flat_store_2',
        'catalog_category_flat_store_3',
        'catalog_category_flat_store_4',
        'catalog_category_flat_store_5',
        'catalog_category_flat_store_6',
        'catalog_category_flat_store_7',
        'catalog_product_flat_1',
        'catalog_product_flat_2',
        'catalog_product_flat_3',
        'catalog_product_flat_4',
        'catalog_product_flat_5',
        'catalog_product_flat_6',
        'catalog_product_flat_7'
    ];
    
    
    protected $sanitize = [
        'email' => [
            'customer_entity',
            'customer_grid_flat',
        ],
        'subscriber_email' => [
            'newsletter_subscriber'
        ]
    ];
    
    protected $_salesTables = [
        'sales_order',
        'sales_order_grid',
        'sales_invoice_grid',
        'sales_shipment_grid',
        'sales_order_item',
        'sales_invoice',
        'sales_order_status_history',
        'sales_shipment',
        'sales_shipment_track',
        'sales_creditmemo_grid',
        'sales_creditmemo',
        'sales_payment_transaction'
    ];
    
    public function __construct( $app )
    {
        parent::__construct($app);
        if ($this->_setupProxy()) {
            register_shutdown_function(array($this, 'executeTunnelKill'));
        }
    }
    
    
    
    public function executeSanitize()
    {
        
    }
    
    public function executeImport()
    {
        $response = $this->_shell->execute($this->getBinMagento().' deploy:mode:show', [], false, false);
        $mode = substr($response->output, strlen('Current application mode: '),9);
        if ($mode == 'productio') {
            $this->echo("Cannot import when in production mode. Aborting...", SELF::YELLOW);
            exit(0);
        } 
        
        $this->showConnection();
        if (!$this->confirm('You\'re about to overwrite this database, continue?')) {
            exit(0);
        }
        
        $folder = $this->_app->getWebrootDir().'var'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.'quik_sql';
        
        $result = $this->_shell->execute('ls %s', [$folder], false,false);
        if (strpos(strtolower($result->output), 'no such file')!==false) {
            $this->echo('You have no sql dump files.');
            return;
        }
        
        $this->_ls($folder);
        
        $this->echo('Choose a sql dump sequence:',\Quik\CommandAbstract::YELLOW, false);
        $choice = $this->listen(array_keys($this->_dumpPrompt));
        if (!$choice || !isset($this->_dumpPrompt[$choice])) {
            exit(0);
        }
        $import = $this->_dumpPrompt[$choice];
        // Now we have a folder series to import
        
        // unzip all of the files
        $files = $this->_shell->execute('ls %s', [$import], false, false);
        $files = explode("\n",$files->output);
        foreach($files as $file) {
            if (strpos($file, '.gz')!==false && !$this->_hasGunzip()) {
                $this->echo('You need to install GUNZIP to import these files');
                exit;
            }
            if (strpos($file, '.gz')!==false) {
                $contains = $this->_shell->execute('gunzip %s', [$import.DIRECTORY_SEPARATOR.$file], false, false);
                if (strpos($contains->output, 'unexpected end of file')!==false) {
                    $this->echo($file.' is corrupt and cannot be imported');
                    exit;
                }
            }
        }
        
        // Now we have .sql files we can import
        $files = $this->_shell->execute('ls %s', [$import], false, false);
        $files = explode("\n",$files->output);
        
        $status = 100/count($files);
        $first = 0;
        
        foreach($files as $file) {
            $this->show_status($status*$first,100,"Importing $file");
            $this->_mysqlImport($import.DIRECTORY_SEPARATOR.$file);
            $first++;
        }
        $this->show_status(100,100,"Import Completed");
    }
    
    /**
     *
     */
    public function executeCleanup()
    {
        if ($this->confirm('Do you want to trim the quote/session tables?')) {
            foreach ($this->trimExpired as $table) {
                $this->show_status(0,100, 'Triming Quote Tables');
                $query = "DELETE FROM $table WHERE updated_at < DATE_SUB(Now(), INTERVAL -7 DAY)";
                
                $this->_query($query);
            }
        }
        
        if ($this->confirm('Do you want to drop your flat tables? This will require a reindex.')) {
            foreach ($this->dropFlat as $table) {
                $this->show_status(0,100, 'Dropping Flat Tables');
                $query = "drop table $table";
                $this->_query($query);
            }
        }
        
        foreach ($this->truncateAll as $table) {
            $this->show_status(0,100, 'Truncating Temporary Tables');
            $query = "truncate $table";
            $this->_query($query);
        }
        
    }
    
    
    public function executeTableSize()
    {
        $query = "SELECT 
                    table_schema as 'Database', 
                    round(((data_length + index_length) / 1024 / 1024), 2) 'Size in MB' , 
                    table_name AS 'Table'
                FROM information_schema.TABLES 
                WHERE table_schema = '{$this->_getDbname()}'
                ORDER BY (data_length + index_length) DESC
                LIMIT 100";
        $result = $this->_query($query);
        $this->echo($result);
    }
    
    
    public function executeDump()
    {
        if (!$this->_hasCstream()) {
            if ($this->confirm('Do you want to install cstream to reduce resource usage in a production environment?')) {
                return;
            }
        }
    
        if (!$this->_hasGzip()) {
            if ($this->confirm('Do you want to install gzip to reduce the filesizes?')) {
                return;
            }
        }
        
    
        $connectParams = $this->_buildConnection();
        $path = $this->_getBackupPath();
        // Restarting last download
        $completedTables = $this->_hasCompleted();
        if (!empty($completedTables)) {
            $this->show_status(1,100,'Restarting Last Export');
        }
        
        if (!file_exists($path)) {
            $this->_shell->execute('mkdir -p %s', [$path]);
        }
        
        // Get All Views
        $query = "select TABLE_NAME from information_schema.views where table_schema = '{$this->_getDbname()}'";
        $viewResult = $this->_query($query);
        
        $lines = explode(PHP_EOL,$viewResult);
        $views = [];
        foreach($lines as $key => $line) {
            if (strpos($line,'[Warning]')!==false) continue;
            if (strpos($line,'TABLE_NAME')!==false) continue;
            
            $views[] = $line;
        }
        
        // Get All Tables
        $query = "SELECT TABLE_NAME,round(((data_length + index_length) / 1024 / 1024),2) 'Size'
            FROM information_schema.TABLES WHERE table_schema = '{$this->_getDbname()}'";
        $show = $this->_query($query);
        
        $lines = explode(PHP_EOL,$show);
        $tables = [];
        $total = 0;
        foreach($lines as $key => $line) {
            if (strpos($line,'[Warning]')!==false) continue;
            if (strpos($line,'TABLE_NAME')!==false) continue;
            
            list($name, $size) = explode("\t",$line);
            if (in_array($name, $views)) continue;
            
            $tables[$key]['name'] = $name;
            $tables[$key]['size'] = $size;
            
            $tables[$key]['size'] = (int)ceil($tables[$key]['size']);
            $total += $tables[$key]['size'];
        }
        
        // dump all tables
        foreach ($tables as $key => $data) {
            $this->show_status($key,count($tables),'Exporting Table '.$tables[$key]['name']);
            $table = $tables[$key]['name'];
            $filename = '1_'.$table.'.sql';
            if (!in_array($filename, $completedTables)) {
                $this->_filePut("SET FOREIGN_KEY_CHECKS=0;SET autocommit = 0;", $path.$filename);
                $this->_mysqlDump($connectParams, $this->_getDbname(), 'sales', $path.$filename, ['tables' => $table]);
                $this->_filePut("SET FOREIGN_KEY_CHECKS=1;COMMIT;SET autocommit = 1;", $path.$filename);
            }
        }
        
        // dump all views
        foreach ($views as $table) {
            $this->show_status($key,count($views),'Exporting View '.$table);
            $filename = '2_'.$table.'.sql';
            if (!in_array($filename, $completedTables)) {
                $this->_filePut("SET FOREIGN_KEY_CHECKS=0;SET autocommit = 0;", $path.$filename);
                $this->_mysqlDump($connectParams, $this->_getDbname(), 'sales', $path.$filename, ['tables' => $table]);
                $this->_filePut("SET FOREIGN_KEY_CHECKS=1;COMMIT;SET autocommit = 1;", $path.$filename);
            }
        }
        $this->show_status(100,100, 'Done with export'.PHP_EOL);
        
        
        $this->show_status(0,100, 'Starting Export Validation');
        $test = $this->_shell->execute('ls %s', [$path], false, false);
        
        $testFor = ['Lost connection', 'Warning]', 'mysqldump'];
        $rerun = false;
        
        $files = explode(PHP_EOL, $test->output);
        foreach($files as $file) {
            if (strpos($file, '.sql')===false) {
                continue;
            }
            if (strlen($file)<3) {
                continue;
            }
            foreach ($testFor as $key => $grep) {
                $test = $this->_shell->execute("cat %s | grep '%s'", [$path.$file, $grep], false, false);
                if (strpos($test->output, $grep)===false) {
                    continue;
                }
                $rerun = true;
                $this->show_status($key,count($files), "Deleting corrupt file $file");
                $this->_shell->execute("rm -f %s", [$path.$file], false, false);
            }
        }
        
        $msg = 'Export is stable!';
        if ($rerun) {
            $msg = 'Export needs to be rerun, '.count($files).' files were corrupt.';
        }
        $this->show_status(100,100, $msg);
    }
    
    protected function _hasCompleted()
    {
        $path = $this->_getBackupPath();
        $result = $this->_shell->execute('ls %s', [$path], false, false);
        if (strpos(strtolower($result->output), 'cannot access')!==false) {
            return [];
        }
        
        $files = explode("\n", $result->output);
        $tables = [];
        foreach($files as $file) {
            if (strpos($file, $this->_app->getWebrootDir())!==false) {
                continue;
            }
            $_result = $this->_shell->execute('tail -n3 %s', [$path.$file], false, false);
            if (strpos($_result->output, 'SET FOREIGN_KEY_CHECKS=1')===false
            || substr($file,-1)=='1') {
                $this->_shell->execute("rm -f {$file}");
                continue;
            }
            $tables[] = $file;
        }
        return $tables;
    }
    
    protected function _filePut( $contents, $file, $string = false, $clean = false )
    {
        if ($string) {
            $cmd = "$contents ";
        } else {
            $cmd = "echo '$contents' ";
        }
        $cmd .= " 2>/dev/null 1> {$file}1";
        
        $output = $this->_shell->execute($cmd);
        
        if ($clean) {
            $o = $this->_shell->execute("head -1 {$file}1");
            while (strpos($o->output,'/*')===false)
            {
                $a = $this->_shell->execute("sed -i '1 d' {$file}1");
                $o = $this->_shell->execute("head -1 {$file}1");
            }
        }
        
        // Now append to the original file
        $this->_shell->execute("cat {$file}1 >> $file");
        $this->_shell->execute("rm -f {$file}1");
        
        return $output;
    }
    
    /**
     *
     */
    protected function _query( $query )
    {
        $connectParams = $this->_buildConnection();
        $dbname = $this->_getDbname();
        
        $cmd = "MYSQL_PWD={$this->_getPassword()} mysql $connectParams $dbname -e\"$query\" ";
        $output = $this->_shell->execute($cmd, [], false, false);
        
        return $output->output;
    }
    
    protected function _mysqlImport( $file )
    {
        $connectParams = $this->_buildConnection();
        $dbname = $this->_getDbname();
        
        $cmd = "MYSQL_PWD={$this->_getPassword()} mysql $connectParams $dbname < $file";
        $output = $this->_shell->execute($cmd);
        
        return $output->output;
    }
    
    /**
     * 
     */
    protected function _mysqlDump( $connectParams, $dbname, $schema = 'all', $file = false, $options = [] )
    {
        if (!$file) {
            $file = $this->_getBackupPath().'_full_backup.sql';
        }
        
        $breadth = '';
        switch($schema) {
            case 'all':
            $breadth = '';
            break;
            case 'data':
            $breadth = ' --single-transaction --no-create-info ';
            break;
            case 'sales':
            $breadth = '';
            $breadth .= " {$options['tables']} ";
            $breadth .= ' --single-transaction ';
            break;
            case 'schema':
            $breadth = ' --no-data ';
            break;
        }
        
        if (in_array($schema, ['sales'])) {
            if (isset($options['limit'])) {
                $breadth .= "--where=\"created_at > DATE_SUB(Now(), INTERVAL -{$options['limit']} DAY)\"";
            }
        }
        
        // --single-transaction 
        $cmd = "MYSQL_PWD={$this->_getPassword()} mysqldump $connectParams $dbname $breadth --no-create-db --routines --triggers --add-drop-trigger --events --skip-comments ";
        
        if ($this->_hasCstream() && $this->getParameters()->getMysqlRateLimit()) {
            $cmd .= " | cstream -t 1000000 ";
        }
        
        //  Enhanced regex that removes DEFINER from views, triggers, procedures and functions
        $cmd .= " | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | sed -e 's/DEFINER[ ]*=[ ]*[^*]*PROCEDURE/PROCEDURE/' | sed -e 's/DEFINER[ ]*=[ ]*[^*]*FUNCTION/FUNCTION/'";
        
        return $this->_filePut($cmd, $file, true, true);
    }
    
    /**
     * The import connection is provided in the command line, this will
     * rebuild the connection string for mysql
     */
    protected function _buildConnection()
    {
        $user = $this->_getUsername();
        $password = $this->_getPassword();
        $host = $this->_getHost();
        $port = $this->_getPort();
        
        return "-u$user -h$host --port=$port";
    }
    
    
    protected $_proxy = null;
    
    protected $_tunnel_port = '3307';
    
    protected function _setupProxy()
    {
        $proxy = $this->getParameters()->getMysqlProxy();
        if (!$proxy) return false;
        
        $this->show_status(0,100,"Establishing SSH Tunnel");
        $this->_port = $this->_tunnel_port;
        $this->_host = '127.0.0.1';
        
        if (!$this->_hasTunnel()) {
            $cmd = "ssh -L {$this->_port}:{$this->_getMysqlHost()}:{$this->_getMysqlPort()} $proxy -NnT";
            $this->_shell->daemon($cmd);
            
            while(!$this->_hasTunnel())
            {
                $this->show_status(0,100,"Establishing SSH Tunnel");
            }
            $this->show_status(1,100,'Tunnel Established on port '.$this->_tunnel_port);
        }
        $this->_MySQLConnect();
        return true;
    }
    
    protected function _MySQLConnect()
    {
        $connectParams = $this->_buildConnection();
        $this->show_status(1,100,"Establishing MySQL Connection");
        $timer = 0;
        
        $output = $this->_shell->execute("mysql $connectParams -e'show databases'", [], false, false);
        while(strpos($output->output,"Can't connect to MySQL")!==false) {
            sleep(1);
            $timer++;
            $this->show_status(1,100,"Establishing MySQL Connection");
            if ($timer>60) {
                $this->show_status(0,100,"Could not connect to MySQL");
                exit();
            }
            $output = $this->_shell->execute("mysql $connectParams -e'show databases'", [], false, false);
        }
        
        if (strpos($output->output,$this->_getDbname())!==false) {
            $this->show_status(4,100,"Connection to MySQL Established");
        }
    }
    
    public function executeTunnelKill()
    {
        $pids = $this->_getTunnelPids();
        if (empty($pids)) {
            $this->echo('No Tunnels to kill');
            exit();
        }
        foreach($pids as $pid => $comm) {
            $this->echo('killing '.$pid.' '.$comm);
            $this->_shell->execute("kill $pid");
        }
    }

    protected function _getTunnelPids()
    {
        $output = $this->_shell->execute("ps -aux | grep {$this->_tunnel_port}", [], false, false);
        $lines = explode(PHP_EOL, $output->output);
        $pids = [];
        foreach ($lines as $line)
        {
            $line = str_replace('  ',' ',$line);
            $array = explode(' ', $line);
            if (!isset($array[1])) continue;
            
            $array2 = explode('ssh -L', $line);
            if (!isset($array2[1])) continue;
            
            $pids[$array[1]] = 'ssh -L'.$array2[1];
        }
        return $pids;
    }
    
    protected function _hasTunnel()
    {
        $pids = $this->_getTunnelPids();
        return count($pids) >= 1;
    }
    
    protected $_host = null;
    
    protected function _getMysqlHost()
    {
        if ($this->getParameters()->getMysqlHost()) {
            return $this->getParameters()->getMysqlHost();
        }
        $env = $this->_getEnvData();
        $parts = explode(':', $env['db']['connection']['default']['host']);
        return $parts[0];
    }
    
    protected function _getHost()
    {
        $_host = $this->_getMysqlHost();
        if (!is_null($this->_host)) {
            $_host = $this->_host;
        }
        return $_host;
    }
    
    
    protected $_port = null;
    
    protected function _getMysqlPort()
    {
        if ($this->getParameters()->getMysqlPort()) {
            return $this->getParameters()->getMysqlPort();
        }
        $env = $this->_getEnvData();
        $parts = explode(':', $env['db']['connection']['default']['host']);
        if (!isset($parts[1])) {
            $port = '3306';
        } else {
            $port = $parts[1];
        }
        return $port;
    }
    
    protected function _getPort()
    {
        $port = $this->_getMysqlPort();
        if (!is_null($this->_port)) {
            $port = $this->_port;
        }
        return $port;
    }
    
    protected function _getDbname()
    {
        if ($this->getParameters()->getMysqlDatabase()) {
            return $this->getParameters()->getMysqlDatabase();
        }
        $env = $this->_getEnvData();
        return $env['db']['connection']['default']['dbname'];
    }
    
    protected function _getUsername()
    {
        if ($this->getParameters()->getMysqlUser()) {
            return $this->getParameters()->getMysqlUser();
        }
        $env = $this->_getEnvData();
        return $env['db']['connection']['default']['username'];
    }
    
    protected function _getPassword()
    {
        if ($this->getParameters()->getMysqlPassword()) {
            return $this->getParameters()->getMysqlPassword();
        }
        $env = $this->_getEnvData();
        return $env['db']['connection']['default']['password'];
    }
    
    protected $_colors = [
        1 => \Quik\CommandAbstract::GREEN,
        2 => \Quik\CommandAbstract::YELLOW,
        3 => \Quik\CommandAbstract::NC
    ];
    
    protected $_dumpPrompt = [];
    
    protected function _ls( $path, $depth = 0, &$count = 0 )
    {
        $depth++;
        $tab = '';
        for($i=0;$i<$depth;$i++) {
            $tab .= '  ';
        }
        
        $result = $this->_shell->execute('ls %s', [$path], false, false);
        if (strpos(strtolower($result->output), 'cannot access')!==false) {
            return;
        }
        
        $folders = explode("\n", $result->output);
        if ($depth==3) {
            $this->echo($tab.count($folders).' database tables', $this->_colors[$depth]);
            
        } else {
            foreach($folders as $folder) {
                $_count = '';
                if (strpos($folder, $this->_app->getWebrootDir())!==false) {
                    continue;
                }
                if ($depth==2) {
                    $_count = ++$count;
                    $this->_dumpPrompt[$_count] = $path.DIRECTORY_SEPARATOR.$folder;
                }
                $this->echo($tab.$_count.' '.$folder, $this->_colors[$depth]);
                $this->_ls($path.DIRECTORY_SEPARATOR.$folder, $depth, $count);
            }
        }
    }
    
    /**
     *
     * @return string
     */
    protected function _getEnvData()
    {
        return require $this->_app->getWebrootDir().'app'.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.'env.php';
    }
    
    protected function _getBackupPath()
    {
        return $this->_app->getWebrootDir().'var'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.'quik_sql'.DIRECTORY_SEPARATOR.$this->_createSlug($this->_getMysqlHost()).DIRECTORY_SEPARATOR.$this->_getDbname().DIRECTORY_SEPARATOR;
    }
    
    protected function _createSlug($str, $delimiter = '-'){

        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;

    } 
    
    protected function _hasCstream()
    {
        $output = $this->_shell->execute('cstream -v', [], false, false);
        if (strpos($output->output, 'not found')!==false) {
            return false;
        }
        return true;
    }
    
    protected function _hasGzip()
    {
        $output = $this->_shell->execute('gzip --version', [], false, false);
        if (strpos($output->output, 'not found')!==false) {
            return false;
        }
        return true;
    }
    
    protected function _hasGunzip()
    {
        $output = $this->_shell->execute('gunzip --version', [], false, false);
        if (strpos($output->output, 'not found')!==false) {
            return false;
        }
        return true;
    }
}
