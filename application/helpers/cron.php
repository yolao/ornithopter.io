<?php
/**
 * Ornithopter.io
 * ------------------------------------------------
 * A minimalist, high-speed open source PHP 5.6+ framework.
 *
 * @author      Corey Olson
 * @copyright   Copyright (c) 2011 - 2016 Corey Olson
 * @license     http://opensource.org/licenses/MIT (MIT License)
 *
 * @link        https://github.com/olscore/ornithopter.io
 *
 * // ########################################################################################
 *
 * A cron class for creating cron jobs (Linux based hosts only)
 *
 * @method io::helpers('cron')->tasks();
 * @method io::helpers('cron')->flush();
 * @method io::helpers('cron')->schedule();
 * @method io::helpers('cron')->unschedule();
 */
namespace ornithopter\helpers;

class cron
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

    /**
     * Internal class storage.
     *
     * @var array
     */
    private static $data = array();

    /**
     * Ornithopter.io looks for an instance() method when loading a library.
     *
     * @return object
     */
    public static function instance()
    {
        // Check for an instance
        if (!isset(self::$instance)) {

            // Create a new instance
            self::$instance = new self();
        }

        // Return existing instance
        return self::$instance;
    }

    /**
     * Initialize cron helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // Initialize defaults
        self::$data['jobs'] = array();
        self::$data['crontab'] = '/usr/bin/crontab';

        // Reads the crontab file into a string
        $crontab = stream_get_contents(popen(self::$data['crontab'].' -l', 'r'));

        // Iterates through all non-empty lines from crontab file
        foreach (array_filter(explode(PHP_EOL, $crontab)) as $line) {

            // Ignore comment lines
            if (trim($line)[0] != '#') {

                // Parse jobs into a developer friendly format
                self::$data['jobs'][md5($line)] = self::parse($line);
            }
        }

        // Register shortcut aliases using h::method();
        \io::alias(__CLASS__, ['tasks', 'flush', 'schedule', 'unschedule']);
    }

    /**
     * Creates a shortcut for io::cron().
     *
     * @return object
     */
    public static function cron()
    {
        // Shortcut for io::cron()
        return self::$instance;
    }

    /**
     * Get the crontab (all cron jobs) as an array.
     *
     * @return array
     */
    public static function tasks()
    {
        // Scheduled cron jobs
        return self::$data['jobs'];
    }

    /**
     * Clear the crontab by removing all cron jobs.
     */
    public static function flush()
    {
        // Create a blank array
        self::$data['jobs'] = array();

        // Save to crontab
        self::write();
    }

    /**
     * Create a new cron job and insert the task into the crontab.
     *
     * @param string
     *
     * @return array
     */
    public static function schedule($job)
    {
        // Creates a new cron job
        $new = self::$data['jobs'][md5($job)] = self::parse($job);

        // Save to crontab
        self::write();

        // Return a friendly cron job array
        return $new;
    }

    /**
     * Remove a cron job and remove the task from the crontab.
     *
     * @param string
     */
    public static function unschedule($job)
    {
        // Removes a scheduled cron job
        unset(self::$data['jobs'][$job]);

        // Save to crontab
        self::write();
    }

    /**
     * Writes the scheduled cron jobs back to the crontab.
     *
     * @param mixed
     *
     * @return mixed
     */
    private static function write($crons = array())
    {
        // Creates a temporary file for crontab preparation
        if (!is_writable($tmp = tempnam(sys_get_temp_dir(), 'cron'))) {

            // For some reason the system cannot write a temporary file
            throw new \Exception('Unable to prepare crontab because temporary file is not writable.');
        }

        // Iterate through the cron jobs
        foreach (self::$data['jobs'] as $jobs) {

            // Get cron commands
            $crons[] = $jobs['cron'];
        }

        // Prepare temporary file for copying to crontab
        file_put_contents($tmp, implode(PHP_EOL, $crons).PHP_EOL);

        // Returns false if writing to crontab fails
        return stream_get_contents(popen(self::$data['crontab'].' '.$tmp, 'r'));
    }

    /**
     * Parses crontab lines into developer friendly arrays.
     *
     * @param string
     *
     * @return array
     */
    private static function parse($job)
    {
        // Splits cron intervals and validated the cron schedule
        if (count($piece = preg_split('@ @', $job, null, PREG_SPLIT_NO_EMPTY)) < 5) {

            // Invalid cron schedule or failure in parsing
            throw new \Exception('Invalid cron schedule provided: '.implode(' ', $piece));
        }

        // Prepare variables
        $lastRunTime = $logFile = $logSize = $errorFile = $errorSize = $comment = null;

        // Cron command without the time schedule
        $cmd = implode(' ', array_slice($piece, 5));

        // Check for comments
        if (strpos($cmd, '#')) {

            // Separates the command and comment
            list($cmd, $comment) = explode('#', $cmd);
            $comment = trim($comment);
        }

        // Check for error file
        if (strpos($cmd, '2>>')) {

            // Separates the command and error file
            list($cmd, $errorFile) = explode('2>>', $cmd);
            $errorFile = trim($errorFile);
        }

        // Check for log file
        if (strpos($cmd, '>>')) {

            // Separates the command and log file
            list($cmd, $logPart) = explode('>>', $cmd);
            $logPart = explode(' ', trim($logPart));
            $logFile = trim($logPart[0]);
        }

        // Last run time checking (1)
        if (isset($logFile) && file_exists($logFile)) {
            $lastRunTime = filemtime($logFile);
            $logSize = filesize($logFile);
        }

        // Last run time checking (2)
        if (isset($errorFile) && file_exists($errorFile)) {
            $lastRunTime = max($lastRunTime ?: 0, filemtime($errorFile));
            $errorSize = filesize($errorFile);
        }

        // Default status
        $status = 'error';

        // Try to determine the status
        if ($logSize === null && $errorSize === null) {
            $status = 'unknown';

        // Status can be determined
        } elseif ($errorSize === null || $errorSize == 0) {
            $status = 'success';
        }

        // Developer friendly cron job array
        return array(
            'id'         => md5($job),
            'cron'       => $job,
            'minute'     => $piece[0],
            'hour'       => $piece[1],
            'dayOfMonth' => $piece[2],
            'month'      => $piece[3],
            'dayOfWeek'  => $piece[4],
            'command'    => trim($cmd),
            'comments'   => $comment,
            'logFile'    => $logFile,
            'logSize'    => $logSize,
            'errorFile'  => $errorFile,
            'errorSize'  => $errorSize,
            'status'     => $status,
        );
    }

    /**
     * Method aliases and function wrappers for coders who like to use alternative
     * names for these methods. Slight performance impact when using method aliases.
     *
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public function __call($called, $args = array())
    {
        $aliases = array(
            'schedule'   => ['job', 'new_job', 'add_job', 'new_cron', 'create_cron', 'add_cron', 'register'],
            'unschedule' => ['unjob', 'remove_job', 'remove_cron', 'delete_job', 'delete_cron', 'unregister'],
        );

        // Iterate through methods
        foreach ($aliases as $method => $list) {

            // Check called against accepted alias list
            if (in_array($called, $list)) {

                // Dynamic method (alias) call with arbitrary arguments
                return call_user_func_array(array(__CLASS__, $method), $args);
            }
        }

        // No alias found
        return false;
    }
}
