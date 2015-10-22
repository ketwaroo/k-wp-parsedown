<?php
/*
  Plugin Name: K Parsedown Editor
  Description: Replaces the default rich text editor with a a simple markdown alternative with syntax hilighting.
  Version: 1.0.0
  Author: Yaasir Ketwaroo
  Author URI:
  License: MIT
 */

class kWpMarkdown
{

    const POST_META_KEY = 'k-md-content';

    const POST_TYPE_SUPPORT = 'k-markdown';

    /**
     *
     * @var \Parsedown
     */
    protected $parser = null;

    public function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    /**
     * Run at each request.
     * 
     * @global boolean $wp_rich_edit
     */
    public function init()
    {
        global $wp_rich_edit;
        
        // disable the default edior
        $wp_rich_edit = false;
		
        $this->clearGarbageFilters();

        add_post_type_support('post', static::POST_TYPE_SUPPORT);
        add_post_type_support('page', static::POST_TYPE_SUPPORT);

        add_filter('content_edit_pre', array($this, 'filterContentEditPre'), 10, 2);
        add_filter('wp_insert_post_data', array($this, 'filterWpInsertPostData'), 10, 2);

        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_script('hilightjs-js', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.8.0/highlight.min.js', array('jquery'));
            //wp_enqueue_style('hilightjs-css', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.8.0/styles/default.min.css', array('bootstrap-css'));
            wp_enqueue_style('hilightjs-css-github', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.8.0/styles/github.min.css', array('bootstrap-css'));
        });

        add_action('wp_head', function() {
            ?>
            <script>hljs.initHighlightingOnLoad();</script>
            <?php

        }, 100);
    }

    /**
     * Removes some of the less markdown friendly filters. Also speeds up rendering quite a bit.
     */
    public function clearGarbageFilters()
    {
        foreach (array('the_title', 'the_content', 'the_excerpt') as $tag)
        {
            foreach (array(
        'wptexturize',
        'convert_smilies',
        'convert_chars',
        'wpautop',
        'shortcode_unautop',
            ) as $filter)
            {
                remove_filter($tag, $filter);
            }
        }
    }

    /**
     * 
     * @return \ParseDown
     */
    protected function getParser()
    {
        if ($this->parser === null)
        {
            if (!class_exists('\ParseDown')) require_once __DIR__ . '/ParseDown.php';
            $this->parser = new \ParseDown();
        }
        return $this->parser;
    }

    /**
     * 
     * @param int $postId
     * @return boolean
     */
    protected function isPostMarkdown($postId)
    {
        return (bool) get_post_meta($postId, self::POST_META_KEY, true);
    }

    /**
     * 
     * @param int $postId
     * @return string
     */
    protected function getPostMarkdown($postId)
    {
        return get_post_meta($postId, self::POST_META_KEY, true);
    }

    /**
     * 
     * @param int $postId
     * @param mixed $content
     * @return int|boolean
     */
    protected function setPostMarkdown($postId, $content)
    {
        return update_post_meta($postId, self::POST_META_KEY, $content);
    }

    /**
     * Filter before populating editor textarea,
     * 
     * @param type $content
     * @param type $postId
     * @return type
     */
    public function filterContentEditPre($content, $postId)
    {

        if ($this->isPostMarkdown($postId))
        {
            return $this->getPostMarkdown($postId);
        }

        return $content;
    }

    /**
     * Filter before save of post data.
     * 
     * @param array $data
     * @param array $rawData
     * @return type
     */
    public function filterWpInsertPostData(array $data, array $rawData)
    {
        $postId = $rawData['ID'];

        // if revision or autosave, restore the md source.
        if (wp_is_post_autosave($postId) || 'revision' === $rawData['post_type'])
        {
            $data['post_content'] = $this->getPostMarkdown($rawData['post_parent']);
        }
        else // pre-process the content and keep a copy of source as post meta
        {
            $this->setPostMarkdown($postId, $data['post_content']);
            $data['post_content'] = $this->getParser()->parse($data['post_content']);
        }

        return $data;
    }

}

new kWpMarkdown();
