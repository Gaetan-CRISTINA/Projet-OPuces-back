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
            'Mobilier',
            'Décoration',
            'Livres',
            'Vacances',
            'Immobilier',
            'Vêtement',
            'Higt Tech',
            'Service à la personne'
        ];
        $taxonomy = 'ProductCategory';
        foreach ($addTaxos as $term) {
           wp_insert_term($term, $taxonomy, $args = array()); //$args sera utilisé lors de l'utilisation de sous catégories
            if($term === 'Auto-Moto'){
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
            if($term === 'Ameublement'){
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
            if($term === 'Electroménager'){
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
            if($term === 'Mobilier'){

            }
            if($term === 'Décoration'){

            }
            if($term === 'Livres'){

            }
            if($term === 'Vacances'){

            }
            if($term === 'Immobilier'){

            }
            if($term === 'Vêtement'){

            }
            if($term === 'High Tech'){

            }
            if ($term === 'Service à la personne'){

            }   
        
        }

    }

    public function activate()
    {
      
        $this->addCapAdmin(['classified']);

    }

    public function deactivate()
    {

    }

    public function addCapAdmin($customCapArray)
    {
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