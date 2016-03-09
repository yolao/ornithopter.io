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
 * A PHP Crawler for scraping and analyzing web pages
 *
 * @method io::library('crawler')->crawl();
 * @method io::library('crawler')->wait();
 */
namespace ornithopter\libraries;

class crawler
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

    /**
     * Internal class variables.
     *
     * @var array
     */
    private static $data;

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
     * Initialize the crawler class.
     *
     * @return void
     */
    public function __construct()
    {
        // Create a new web agent
        self::$data['crawler'] = \io::libraries('agent');

        // Crawler should be faster
        self::$data['timeout'] = 2500;

        // Set timeout within the user agent
        self::$data['crawler']->timeout(self::$data['timeout']);

        // Crawler disregards SSL verification
        self::$data['crawler']->secure(false);

        // Register shortcut aliases using io::method();
        \io::alias(__CLASS__, ['crawler', 'crawl', 'wait']);
    }

    /**
     * Creates a shortcut for io::crawler().
     *
     * @return object
     */
    public static function crawler()
    {
        // Shortcut for io::crawler()
        return self::$instance;
    }

    /**
     * Wrapper for Agent GET Request.
     *
     * @param string
     *
     * @return mixed
     */
    public static function crawl($path)
    {
        // Crawl the path
        self::$data['crawler']->get($path);

        // Check for an unsuccessful crawl (usually a timeout)
        if (!self::$data['crawler']->status()) {

            // Failed to crawl
            return false;
        }

        // Digest the crawler information
        return self::digest();
    }

    /**
     * For slower servers perform a long (indefinite by default) crawl.
     *
     * @param string
     * @param int
     *
     * @return mixed
     */
    public static function wait($path, $timeout = 0)
    {
        // Check for indefinite crawls
        if ($timeout == 0) {

            // Wait indefinitely for this page to load
            $timeout = PHP_INT_MAX;
        }

        // Set timeout within the user agent
        self::$data['crawler']->timeout($timeout);

        // Perform crawl
        $crawlDigest = self::crawl($path);

        // Reset timeout to the default value
        self::$data['crawler']->timeout(self::$data['timeout']);

        // Crawl digest
        return $crawlDigest;
    }

    /**
     * Return the crawler digest.
     *
     * @return mixed
     */
    private static function digest()
    {
        // Special DOM processing
        self::domprocess(self::$data['crawler']->body());

        // Digest crawler data
        return array(
            'Status'         => self::$data['crawler']->status(),
            'Path'           => self::$data['crawler']->path(),
            'Protocol'       => self::$data['crawler']->protocol(),
            'Root'           => self::$data['crawler']->root(),
            'Domain'         => self::$data['crawler']->domain(),
            'TLD'            => self::$data['crawler']->tld(),
            'Headers'        => self::$data['crawler']->headers(),
            'Title'          => self::title(),
            'Meta'           => self::meta(),
            'Body'           => self::$data['crawler']->body(),
            'BodyCompressed' => preg_replace('~>\s+<~', '> <', self::$data['crawler']->body()),
            'Content'        => self::content(self::$data['crawler']->body()),
            'Redirects'      => self::$data['crawler']->redirects(),
            'Details'        => self::$data['crawler']->details(),
            'Links'          => self::links(),
            'Frames'         => self::frames(),
        );
    }

    /**
     * Prepares a DOM object.
     *
     * @return void
     */
    private static function domprocess($html)
    {
        // DOM: http://php.net/manual/en/class.domdocument.php
        self::$data['dom'] = new \domDocument();

        // Suppress errors and load HTML from crawled page
        @self::$data['dom']->loadHTML($html);

        // Removing whitespace
        self::$data['dom']->preserveWhiteSpace = false;
    }

    /**
     * Returns the documents title if it exists on the crawled page.
     *
     * @return mixed
     */
    private static function title()
    {
        // Grab the Title tag from the DOM
        $title = self::$data['dom']->getElementsByTagName('title')[0];

        // Return the Title tag if it exists
        return (!isset($title))?false:$title->nodeValue;
    }

    /**
     * Returns an array of the meta tags from the crawled page.
     *
     * @return array
     */
    private static function meta()
    {
        // Search for <meta> tags within the document; using regex so we dont have to make another request, e.g., get_meta_tags()
        preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', self::$data['crawler']->body(), $match);

        // Digest the meta tags
        return array(

            // Full HTML tags
            'Raw'  => $match[0],

            // Meta tags as an key / value pair (with normalized keys)
            'Tags' => array_combine(array_map('strtolower', $match[1]), $match[2])
        );
    }

    /**
     * Parses the content of the page.
     *
     * @return array
     */
    private static function content($content)
    {
        // Remove any JavaScript from the Content
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', ' ', $content);

        // Remove any in-line CSS from the Content
        $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', ' ', $content);

        // Ensure there are spaces between tags
        $content = str_replace('><', '> <', $content);

        // Remove the HTML tags from the Content while cleaning up whitespace
        $content = preg_replace('/\s+/', ' ', strip_tags($content));

        // Remove special HTML characters
        $content = preg_replace('/&#?[a-z0-9]{2,8};/i', '', $content);

        // Get the list of SEO stop words
        $stopArr = \io::helpers('web')->stop_words();

        // Iterate through content and generate keyword usage
        foreach (explode(' ', $content) as $word) {

            /*
             * Normalize words (remove commas, colons, semi-colons, etc.) This also
             * casts integers as strings and ignores casing for words for SEO purposes.
             */
            $word = (string) strtolower(preg_replace("/[^A-Za-z0-9]/", '', $word));

            // Remove short words
            if (!isset($word[2])) {

                // Words 2 characters or less are excluded
                continue;
            }

            // Check if a word exists
            if (isset($keywords['Occurrence'][$word])) {

                // Increment the keyword count
                $keywords['Occurrence'][$word]++;
            } else {

                // Add the word
                $keywords['Occurrence'][$word] = 1;
            }

            // Check against SEO stop words
            if (!in_array($word, $stopArr)) {

                // Add to the SEO relevant keywords
                $keywords['SEO'][$word] = $keywords['Occurrence'][$word];
            }
        }

        // Check for content
        if (isset($keywords)) {

            // Sort Keywords by Occurrence
            arsort($keywords['Occurrence']);
            arsort($keywords['SEO']);

            // Top 10 keywords in order
            $keywords['Top'] = array_keys(array_slice($keywords['SEO'], 0, 10, true));
        } else {

            // No content
            $keywords = array(
                'Occurrence' => array(),
                'SEO'        => array(),
                'Top'        => array(),
            );
        }

        // Normalize content
        $content = trim($content);

        // Check for empty content
        if ($content == '') {

            // Make developer friendly variable
            $content = false;
        }

        // Digest content
        return array(
            'Content'  => $content,
            'Words'    => str_word_count($content),
            'Unique'   => count($keywords['Occurrence']),
            'Keywords' => $keywords,
        );
    }

    /**
     * Return an array of links from the crawled page.
     *
     * @return array
     */
    private static function links()
    {
        // Links discovered
        $links = array();

        /*
         * Tracking array to remove duplicate links. By design this crawler also
         * removes references to root domain and anchors linking to tops of pages.
         */
        $tracking = array('/', '//', '#');

        // Search for <a> tags within the DOM
        $tags = self::$data['dom']->getElementsByTagName('a');

        // Iterate over tags
        foreach ($tags as $tag) {

            // Get the HREF and TITLE attributes from the tags into an array
            @$links[] = [trim($tag->getAttribute('href')), trim($tag->getAttribute('title')), trim($tag->childNodes->item(0)->nodeValue)];
        }

        // Iterate through each link
        foreach ($links as $key => list($href, $title, $text)) {
            // Check for empty HREFs, duplicate HREFs
            if (!isset(trim($href)[0]) or in_array($href, $tracking)) {

                // Remove empty links
                unset($links[$key]);
            } else {
                // Add the absolute path
                $links[$key] = array(
                    'crawl' => self::link_crawlable($href),
                    'href'  => $href,
                    'title' => (isset($title[0])) ? $title : false,
                    'text'  => (isset($text[0])) ? $text : false,
                    'type'  => self::link_type($href),
                    'querystring' => (strpos($href, '?')) ? true : false,
                );
            }

            // Add to tracking array
            $tracking[] = $href;
        }

        // Check for no links
        if (count($links) == 0) {

            // Make developer friendly variable
            return false;
        }

        // Renumber the $links array and return
        return array_values($links);
    }

    /**
     * Returns an absolute link path if crawlable or false if not crawlable.
     *
     * @return mixed
     */
    private static function link_type($href)
    {
        // Check for anchor links
        if (substr($href, 0, 1) == '#') {

            // Anchor, in-page link
            return 'anchor';
        }

        // Check for protocol-relative links
        elseif (substr($href, 0, 2) == '//') {

            // Link does not specify HTTP or HTTPS
            return 'protocol-relative';
        }

        // Check for absolute paths
        elseif (substr($href, 0, 4) == 'http') {

            // Absolute link
            return 'absolute';
        }

        // Relative path link
        return 'relative';
    }

    /**
     * Returns a link classification for based on the href.
     *
     * @return mixed
     */
    private static function link_crawlable($href)
    {
        // Check for anchor links
        if (substr($href, 0, 1) == '#') {

            // Cannot crawl
            return false;
        }

        // Check for protocol-relative links
        elseif (substr($href, 0, 2) == '//') {

            // Make crawlable using same protocol
            return filter_var(self::$data['crawler']->protocol().$href, FILTER_VALIDATE_URL);
        }

        // Check for absolute paths
        elseif (substr($href, 0, 4) == 'http') {

            // Already crawlable
            return filter_var($href, FILTER_VALIDATE_URL);
        }

        // Check for relative links (from root)
        elseif (substr($href, 0, 1) == '/') {

            // Transform the relative path into an absolute (crawlable) link path
            return filter_var(self::$data['crawler']->protocol().'://'.self::$data['crawler']->domain().$href, FILTER_VALIDATE_URL);
        }

        // Handle relative links based on path crawled
        elseif (substr(self::$data['crawler']->path(), -1) == '/') {

            // Transform the relative path (missing slash) based on path
            return filter_var(self::$data['crawler']->path().'/'.$href, FILTER_VALIDATE_URL);
        }

        // Handle relative links based on path crawled
        elseif (substr(self::$data['crawler']->path(), -1) != '/') {

            // Transform the relative path (missing slash) based on path
            return filter_var(self::$data['crawler']->protocol().'://'.self::$data['crawler']->path().'/'.$href, FILTER_VALIDATE_URL);
        }

        // Relative path from a file (.html, .php; eg., not ending in a trailing slash)
        $pathArr = explode('/', self::$data['crawler']->path());

        // Remove the last path item
        array_pop($pathArr);

        // Transform the relative path
        return filter_var(implode('/', $pathArr).'/'.$href, FILTER_VALIDATE_URL);
    }

    /**
     * Detects legacy (not support in HTML5) frames and iframes.
     *
     * @return array
     */
    private static function frames()
    {
        // Initialize variables
        $frames = array(
            'Sources' => array(),
            'Content' => '',
        );

        // Create an Xpath based on the DOM
        $xpath = new \DOMXpath(self::$data['dom']);

        // Iterate over tags
        foreach ($xpath->query('//frame | //iframe') as $tag) {

            // Check for blank src
            if (trim($tag->getAttribute('src')) != '') {

                // Get the NAME and SRC attributes from the tags into an array
                @$frames['Sources'][] = self::link_crawlable(trim($tag->getAttribute('src')));
            }
        }

        // Combine content from each frame
        foreach ($frames['Sources'] as $src) {

            // Append frame content
            @$frames['Content'] .= file_get_contents($src);
        }

        // Change DOM to reprocess Links
        self::domprocess($frames['Content']);

        // Process links for sub frames and iframes
        $frames['Links'] = self::links();

        // Parse content of frames
        $frames['Content'] = self::content($frames['Content']);

        // Check for empty sources
        if (count($frames['Sources']) == 0) {

            // No sources
            return false;
        }

        // Combined frames content and sources
        return $frames;
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
            'crawl' => ['scrape'],
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
