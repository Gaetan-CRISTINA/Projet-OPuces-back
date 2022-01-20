<?php

namespace OPuces;

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

    public function createClassified()
    {
        echo "toto";
    }

    

}