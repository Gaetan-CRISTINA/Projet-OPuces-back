<?php

namespace OPuces;

use WP_REST_Request;
class Api {

    protected $baseURI;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'initialize']);
    }    
    
    public function initialize()
    {
        // on récupère la baseURI avec $_SERVER qui est une super globale
        $this->baseURI = dirname($_SERVER['SCRIPT_NAME']);

        register_rest_route(
            'opuces/v1', // nom de l'API
            'create-classified',
            [
                'methods' => 'post',
                'callback' => [$this, 'createClassified']
            ]
        );
    }

    public function createClassified(WP_REST_Request $request)
    {
        $title = $request->get_param('title');
        $description = $request->get_param('content');
        $author = $request->get_param('author');
        $price = $request->get_param('price');

        // récupération de l'utilisateur ayant envoyé la requête
        // $user = wp_get_current_user();

        $classifiedCreateResult = wp_insert_post(
            [
                    'post_title' => $title,
                    'post_content' => $description,
                    'post_author'  =>  $author,
                    'post_status' => 'publish',
                    'post_type' => 'classified',
                ]
        );
        if (is_int($classifiedCreateResult)) {
            if ($price > 0)
            {
                $keyMeta ='classifiedPrice';
                add_post_meta($classifiedCreateResult, $keyMeta, $price ,$unique = true);
            }
            return [
                    'success' => true,
                    'title' => $title
                    
                ];
        }
        return
            [
                'success' => false
            ];
    } 

}