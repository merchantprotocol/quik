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

class Shell
{
    /**
     * Render command with arguments
     *
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function render($command, array $arguments = [])
    {
        $command = preg_replace('/(\s+2>&1)*(\s*\|)|$/', ' 2>&1$2', $command);
        $arguments = array_map('escapeshellarg', $arguments);
        if (empty($arguments)) {
            return $command;
        }
        return vsprintf($command, $arguments);
    }
    
    /**
     * Execute a command through the command line, passing properly escaped arguments, and return its output
     *
     * @param string   $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @param boolean  $passthru returns no output, but triggers some command that is interactive
     * @return stdClass
     * @throws Exception
     */
    public function execute($command, $arguments = [], $passthru = false)
    {
        $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
        if (in_array('exec', $disabled)) {
            echo('The exec function is disabled.');
            return;
        }
        
        $command = $this->render($command, $arguments);
        if ($passthru) {
            $descriptorSpec = array(
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            );
            $pipes = array();
            $process = proc_open($command, $descriptorSpec, $pipes);
            if (is_resource($process)) {
                proc_close($process);
            }
        } else {
            exec($command, $output, $exitCode);
            $output = implode(PHP_EOL, $output);
        }
        return $this->response($output, $exitCode, $command);
    }
    
    /**
     * Returns object
     * 
     * @param unknown $output
     * @param unknown $exit_code
     * @param unknown $escaped_command
     * @return \stdClass
     */
    public function response($output, $exit_code, $escaped_command)
    {
        $std = new \stdClass();
        $std->output = $output;
        $std->exit_code = $exit_code;
        $std->escaped_command = $escaped_command;
        return $std;
    }
    
    /**
     * 
     * @return integer
     */
    public function getMaxLength()
    {
        $response = $this->execute('tput cols');
        return $response->output;
    }
    
    /**
     * Resets the cursor to the first position on the line.
     * 
     */
    public function clearTerminalLine()
    {
        echo chr(27)."[0G";
        echo str_repeat(' ', $this->getMaxLength());
        echo chr(27)."[0G";
    }
}
