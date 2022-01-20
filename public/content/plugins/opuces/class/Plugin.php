<?php

namespace OPuces;

class Plugin 
{
    public function __construct()
    {
        add_action(
            'init',
            [$this, 'createClassifiedPostType']
        );
        add_action(
            'init',
            [$this,  'createProductStateCustomTaxonomy']
        );
        add_action(
            'init',
            [$this, 'createDeliveryMethodCustomTaxonomy']
        );
        add_action(
            'init',
            [$this, 'createProductCategoryCustomTaxonomy']
        );
        add_action(
            'init',
            [$this, 'createSellerRateCustomTaxonomy']
        );
    }

    //create Classified
    public function createClassifiedPostType()
    {
        register_post_type(
            'classified', //nom du custom post type
            [
                'label' => 'Annonce',
                'public' => true,
                'hierarchical' => false,
                'menu_icon' => 'dashicons-admin-page', //icone visible dans la dashboard
                'supports' => [
                    'title',
                    'thumbnail',
                    'editor',
                    'author',
                    'excerpt',
                    'comments'
                ],
                'capability_type' => 'classified',
                'map_meta_cap' => true,
                'show_in_rest' => true //rendre accessible avec API Wordpress
            ]
        ); 
    }
       
    // taxonomie pour l'état du produit à la vente
    public function createProductStateCustomTaxonomy()
    {
        register_taxonomy(
            'ProductState',
            ['classified'],
            [
                'label' => 'Etat du produit', 
                'hierarchical' => true,
                'public' => true,
                'show_in_rest' => true
            ]
        );
        //on crée les états possibles d'un produit qui seront intégrés dans WP
        $addTaxos = [
            'Jamais utilisé',
            'peu utilisé',
            'usé',
            'tres usé'
        ];
        $taxonomy = 'ProductState';
        foreach ($addTaxos as $term) {
            wp_insert_term($term, $taxonomy, $args = array()); //$args sera utilisé lors de l'utilisation de sous catégories
        }  
    }

    // taxonomie pour les méthodes de livraison
    public function createDeliveryMethodCustomTaxonomy()
    {
        register_taxonomy(
            'DeliveryMethod',
            ['classified'],
            [
                'label' => 'Méthode de livraison',
                'hierarchical' => true,
                'public' => true,
                'show_in_rest' => true
            ]
        );
        //on crée les méthodes de livraison possible qui seront intégrées dans WP
        $addTaxos = [
            'UPS',
            'Fedex',
            'En main propre',
            'LaPoste (colissimo)'
        ];
        $taxonomy = 'DeliveryMethod';
        foreach ($addTaxos as $term) {
            wp_insert_term($term, $taxonomy, $args = array()); //$args sera utilisé lors de l'utilisation de sous catégories
        }
    }

    //taxonomie pour les catégories
    public function createProductCategoryCustomTaxonomy()
    {
        register_taxonomy(
            'ProductCategory',
            ['classified'],
            [
                'label' => 'Catégorie',
                'hierarchical' => true,
                'public' => true,
                'show_in_rest' => true
            ]
        );
        $addTaxos = [
            'Auto-Moto',
            'Ameublement',
            'Electroménager',
            'Décoration',
            'Livres',
            'Vacances',
            'Immobilier',
            'Vêtement',
            'High Tech',
            'Service à la personne'
        ];
        $taxonomy = 'ProductCategory';
        foreach ($addTaxos as $term) {
           wp_insert_term($term, $taxonomy, $args = array()); //$args sera utilisé lors de l'utilisation de sous catégories
            if($term === 'Auto-Moto')
            {
                // si je viens de creer la categorie auto je veux creer la ss categ constructeur

                $addSousTaxos = [
                        'Peugeot',
                        'Renault',
                        'Citroen',
                        'Ford',
                        'Tesla',
                        'BMW'
                        ];
                        
                       $term_id = get_term_by('name','Auto-Moto','ProductCategory');
                       $categoryIdParent = $term_id->term_id;
                       
                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent             
                ];               
                foreach ($addSousTaxos as $sousTerm) {
                wp_insert_term($sousTerm, $taxonomy, $args );    
                }   
            }
            if($term === 'Ameublement')
            {
                $addSousTaxos = [
                        'Bureau',
                        'Lampe',
                        'Canapé',
                        'Table',
                        'Chaise',
                        'Cadre'
                    ];

                    $term_id = get_term_by('name','Ameublement', 'ProductCategory');
                    $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent             
                    ];               
                foreach ($addSousTaxos as $sousTerm) {
                wp_insert_term($sousTerm, $taxonomy, $args );    
                }     

            }
            if($term === 'Electroménager')
            {
                $addSousTaxos = [
                        'Micro-Onde',
                        'Four',
                        'Réfrigérateur',
                        'Machine à café',
                        'Mixeur',
                        'Aide Culinaire'
                    ];

                    $term_id = get_term_by('name','Electroménager','ProductCategory');
                    $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                    ];
                foreach ($addSousTaxos as $sousTerm) {
                wp_insert_term($sousTerm, $taxonomy, $args);    
                }

            }
            if($term === 'Décoration')
            {
                $addSousTaxos = [
                        'Rideaux',
                        'Statue',
                        'Horloge',
                        'Tapis',
                        'Décoration en bois',
                        'Verrerie'
                ];

                    $term_id = get_term_by('name','Décoration','ProductCategory');
                    $categoryIdParent = $term_id->term_id;
                
                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                wp_insert_term($sousTerm, $taxonomy, $args);    
                }
            }
            if($term === 'Livres')
            {
                $addSousTaxos = [
                        'Roman',
                        'Bande Dessinée',
                        'Nouvelle',
                        'Livre de collection',
                        'Manga',
                        'Poésie'
                ];

                $term_id = get_term_by('name','Livres','ProductCategory');
                $categoryIdParent = $term_id->term_id;
            
                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                wp_insert_term($sousTerm, $taxonomy, $args);    
                }
            }
            if($term === 'Vacances')
            {
                $addSousTaxos = [
                    'Au Soleil',
                    'A la Montagne',
                    'Week-end',
                    'Camping',
                    'Ski',
                    'Les Capitales'
                ];

                $term_id = get_term_by('name','Vacances','ProductCategory');
                $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                    wp_insert_term($sousTerm, $taxonomy, $args);
                }

            }
            if($term === 'Immobilier')
            {
                $addSousTaxos = [
                    'Maison',
                    'Appartement',
                    'Mobile-home'
                ];

                $term_id = get_term_by('name', 'Immobilier','ProductCategory');
                $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                    wp_insert_term($sousTerm, $taxonomy, $args);
                }

            }
            if($term === 'Vêtement')
            {
                $addSousTaxos = [
                    'Tee-shirt',
                    'Pull',
                    'Robe',
                    'Jupe',
                    'Echarpe',
                    'Pantalon'
                ];

                $term_id = get_term_by('name','Vêtement','ProductCategory');
                $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                    wp_insert_term($sousTerm, $taxonomy, $args);
                }

            }
            if($term === 'High Tech')
            {
                $addSousTaxos = [
                    'Image',
                    'Son',
                    'Domotique',
                    'Informatique',
                    'Console de jeux'
                ];

                $term_id = get_term_by('name','High Tech','ProductCategory');
                $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                    wp_insert_term($sousTerm, $taxonomy, $args);
                }

            }
            if ($term === 'Service à la personne')
            {
                $addSousTaxos = [
                    'Aide au ménage',
                    'Aide personnes âgées',
                    'Pet-Sitter',
                    'Aide aux devoirs'
                ];

                $term_id = get_term_by('name','Service à la personne','ProductCategory');
                $categoryIdParent = $term_id->term_id;

                $args = [
                    'description' => '',
                    'slug' => '',
                    'parent' => $categoryIdParent
                ];
                foreach ($addSousTaxos as $sousTerm) {
                    wp_insert_term($sousTerm, $taxonomy, $args);
                }
            }   
        }
    }

    public function createSellerRateCustomTaxonomy()
    {
        register_taxonomy(
            'SellerRate',
            ['users'],
            [
                'label' => 'Note du Vendeur',
                'hierarchical' => true,
                'public' => true,
                'show_in_rest' => true
            ]
        );
        //on crée les méthodes de livraison possible qui seront intégrées dans WP
        $addTaxos = [
            '1',
            '2',
            '3',
            '4',
            '5'
        ];
        $taxonomy = 'SellerRate';
        foreach ($addTaxos as $term) {
            wp_insert_term($term, $taxonomy, $args = array()); //$args sera utilisé lors de l'utilisation de sous catégories
        }
    }


    /**
     * Activation Plugin
     * add capabilities to Administrator
     * 
     */
    public function activate()
    {
        $this->addCapAdmin(['classified']);
          

    }
    
    /**
     * Method to desactivate Plugin
     * 
     */
    public function deactivate()
    {

    }

    public function addCapAdmin($customCapArray)
    {
        // methode qui nous permet d'ajouter les droits sur le CPT recipe pour le role administrateur
        //! Attention, sans cette opération le CPT recipe va disparaire
        //! en effet, nous avons définis un "capability_type" pour ce dernier
        //! et l'adminstrateur ne vas pas avoir automatiquement les droits 

        $role = get_role('administrator');
        foreach ($customCapArray as $customCap) {
            $role->add_cap('delete_others_' . $customCap . 's');
            $role->add_cap('delete_private_' . $customCap . 's');
            $role->add_cap('delete_' . $customCap . 's');
            $role->add_cap('delete_published_' . $customCap . 's');
            $role->add_cap('edit_others_' . $customCap . 's');
            $role->add_cap('edit_private_' . $customCap . 's');
            $role->add_cap('edit_' . $customCap . 's');
            $role->add_cap('edit_published_' . $customCap . 's');
            $role->add_cap('publish_' . $customCap . 's');
            $role->add_cap('read_private_' . $customCap . 's');
        }
    }


    
}