<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
*Helper functions for Bystrom Functions
*/
/**
 * Remove Class Filter Without Access to Class Object
 *
 * In order to use the core WordPress remove_filter() on a filter added with the callback
 * to a class, you either have to have access to that class object, or it has to be a call
 * to a static method.  This method allows you to remove filters with a callback to a class
 * you don't have access to.
 *
 * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
 * Updated 2-27-2017 to use internal WordPress removal for 4.7+ (to prevent PHP warnings output)
 *
 * @param string $tag         Filter to remove
 * @param string $class_name  Class name for the filter's callback
 * @param string $method_name Method name for the filter's callback
 * @param int    $priority    Priority of the filter (default 10)
 *
 * @return bool Whether the function is removed.
 */
function remove_class_action($tag, $class = '', $method, $priority = null) : bool {
    global $wp_filter;
    if (isset($wp_filter[$tag])) {
        $len = strlen($method);

        foreach($wp_filter[$tag] as $_priority => $actions) {

            if ($actions) {
                foreach($actions as $function_key => $data) {

                    if ($data) {
                        if (substr($function_key, -$len) == $method) {

                            if ($class !== '') {
                                $_class = '';
                                if (is_string($data['function'][0])) {
                                    $_class = $data['function'][0];
                                }
                                elseif (is_object($data['function'][0])) {
                                    $_class = get_class($data['function'][0]);
                                }
                                else {
                                    return false;
                                }

                                if ($_class !== '' && $_class == $class) {
                                    if (is_numeric($priority)) {
                                        if ($_priority == $priority) {
                                            //if (isset( $wp_filter->callbacks[$_priority][$function_key])) {}
                                            return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                        }
                                    }
                                    else {
                                        return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                    }
                                }
                            }
                            else {
                                if (is_numeric($priority)) {
                                    if ($_priority == $priority) {
                                        return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                    }
                                }
                                else {
                                    return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                }
                            }

                        }
                    }
                }
            }
        }

    }

    return false;
}

/*
* Check if current user is certified to see/do/buy specialist stuff
*/

function esh_is_current_user($allowed_roles) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $current_user_roles = (array) $user->roles;
        foreach ($allowed_roles as $role) {
            if (in_array($role, $current_user_roles)) {
                return true;
            }
        }
    }
    return false;
}