<?php

namespace OPuces;

use WP_REST_Request;
use WP_USER;

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
            'opuces/v1', // API Name
            'create-custom-taxonomy', // name of route
            [
                'methods' => 'post',
                'callback' => [$this, 'createCustomTaxonomy']
            ]
        );


    }



    public function createCustomTaxonomy(WP_REST_Request $request)
    {
        $name = $request->get_param('name');
        $slug = $request->get_param('slug');
        $parentCategory = $request->get_param('parentCategory');
        $description = $request->get_param('description');

    }




}