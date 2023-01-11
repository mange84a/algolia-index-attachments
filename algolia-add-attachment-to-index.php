<?php
/*
    Plugin Name: Algolia Index Attachments 
    Plugin URI: https://github.com/mange84a/algolia-index-attachments
    Description: Add attachments to Algolia Index (https://github.com/helsingborg-stad/algolia-index)
    Version: 1.0.0
    Author: Magnus Andersson
    Author URI: https://github.com/mange84a
    License: MIT
    License URI: https://opensource.org/licenses/MIT 
 */

namespace AlgoliaIndexAddAttachmentsToIndex;

use \AlgoliaIndex\Helper\Index as Instance;
use \AlgoliaIndex\Helper\Id as Id;

class AlgoliaIndexAddAttachmentsToIndex
{
    public function __construct()
    {
        //Add attachments to the list of indexable posttypes
        add_filter( 'AlgoliaIndex/IndexablePostTypes', [$this, 'add_attachments_to_algolia_index']);
        
        //Set permalink to the attachment url
        add_filter( 'AlgoliaIndex/Record', [$this, 'add_attachment_details'], 10, 2);
        
        //Filter mime types?
        add_filter( 'AlgoliaIndex/ShouldIndex', [$this, 'check_attachment_should_index'], 10, 2);

        //Attachments dont trigger save_post, update_post and delete_post
        
        //Add delete attachment
        add_action('delete_attachment', [$this, 'delete_attachment']);

        //Add attachment
        add_action('add_attachment', [$this, 'add_attachment']);
        
        //Update        
        add_action('attachment_updated', [$this, 'update_attachment'], 1, 3);
    }

    //@TODO: Check for hook?
    function delete_attachment($postId) {
        Instance::getIndex()->deleteObject(Id::getId($postId));
    }
     
    function add_attachment($postId) { 
        do_action('AlgoliaIndex/IndexPostId', $postId);
    }
    
    function update_attachment($postId, $before, $after) {
        do_action('AlgoliaIndex/IndexPostId', $postId);
    }


    //Add attachments to the list of indexable posttypes
    function add_attachments_to_algolia_index( $postTypes ) {
        array_push($postTypes, 'attachment');
        return $postTypes;
    }
    
    //Set permalink to the attachment url
    function add_attachment_details($result, $postId) {
        if($result['post_type'] == 'attachment') {
            $result['permalink'] = wp_get_attachment_url($postId);
        }
        return $result; 
    }
    
    //Filter mime types?
    function check_attachment_should_index($index, $post) {
        if(get_post_type($post) == 'attachment') {

            //List of accepted mime types
            $whitelist = [
                'application/pdf'
            ];

            //If in array, return true, else false
            $mime = get_post_mime_type($post);
            if($mime && in_array($mime, $whitelist)) {
                return true;
            } 
            return false;
        }
        return true;
    } 
}

//Start plugin
new \AlgoliaIndexAddAttachmentsToIndex\AlgoliaIndexAddAttachmentsToIndex();


