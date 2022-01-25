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

        //route for new custom taxonomy
        register_rest_route(
            'opuces/v1', // API Name
            'create-custom-taxonomy', // name of route
            [
                'methods' => 'post',
                'callback' => [$this, 'createCustomTaxonomy']
            ]
        );

        //route for modification of a taxonomy
        register_rest_route(
            'opuces/v1', // API Name
            'modify-taxonomy', // name of route
            [
                'methods' => 'post',
                'callback' => [$this, 'modifyTaxonomy']
            ]
        );


    }


    public function createCustomTaxonomy(WP_REST_Request $request)
    {
        // retrieving what has been sent to the api on the endpoint /opuces/v1/create-custom-taxonomy in POST
        $categoryId = $request->get_param('categoryId');
        $name = $request->get_param('name');
        $parentCategory = $request->get_param('parentCategory');
        $description = $request->get_param('description');

        //inserting parentcategory
        $term_id = get_term_by('name', $parentCategory, 'ProductCategory');
        $categoryIdParent = $term_id->term_id;

        $args = [
            'description' => $description,
            'slug' => '',
            'parent' => $categoryIdParent
        ];
        //creating a new custom taxonomy
        $createCustomTaxonomyResult = wp_insert_term(
            $name,
            $categoryId,
            $args
        );

        //verification if custom taxonomy has been created
        if (is_int($createCustomTaxonomyResult)) {
            return [
                'success' => true,
            ];
        }
        if (is_wp_error($createCustomTaxonomyResult)) {
            return [
                'success' => false,
                'error' => $createCustomTaxonomyResult
                ];
        } else {
            return [
                'success' => true              
            ];
        };


    } //<-- end of public function createCustomTaxonomy




} // <-- end of class API