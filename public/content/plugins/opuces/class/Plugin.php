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
            [$this, 'createUserInfoCustomTable']
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
        add_action(
            'init',
            [$this, 'registerPostStatus']
        );
    }
   

    public function registerPostStatus()
    {
        register_post_status(
            // identifiant du status
            'notValided',
            [
            'label' => 'A valider',
            'exclude_from_search' => true,
            'public' => false,
            'publicly_queryable' => false,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list' => true,
            'label_count'=> _n_noop('A valider <span class="count">(%s)</span>', 'A validé <span class="count">(%s)</span>'),
            ]
        );
        register_post_status(

             // identifiant du status
             'vendu',
             [
             'label' => 'vendu',
             'exclude_from_search' => true,
             'public' => false,
             'publicly_queryable' => false,
             'show_in_admin_status_list' => true,
             'show_in_admin_all_list' => true,
             'label_count'=> _n_noop('A valider <span class="count">(%s)</span>', 'A validé <span class="count">(%s)</span>'),
             ]
            );

    }

    // create additionnal custome table  userInfo

    public function createUserInfoCustomTable(){
            //Todo foreignkey
        $sql = " CREATE TABLE user_table (
                userID bigint(20) unsigned NOT NULL PRIMARY KEY,
                adress1 varchar(50) ,
                adress2 varchar(50) ,
                zipcode int(10) NOT NULL,
                city varchar(50) NOT NULL, 
                country varchar(50) ,
                latitude varchar(24) ,
                longitude varchar(24) ,
                phone_number bigint(16) ,
                rate tinyint(1) ,
                created_at datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
                updated_at datetime NULL
                );
            ";
                // wp_users_id bigint(24),
                // FOREIGN KEY(wp_users_id) REFERENCES wp_users(id)
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
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
                'menu_icon' => 'dashicons-welcome-widgets-menus', //icone visible dans la dashboard
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
        add_post_meta(1 , "classifiedBuyerId" , 1 , $unique = true);
        add_post_meta(1 , "classifiedPrice" , 1 , $unique = true);
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
            'neuf',
            'très bon état',
            'bon état',
            'état correct',
            'pour pièces, ne fonctionne pas'
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
            wp_insert_term($term, $taxonomy); 
            
            /**
             * creation d'une meta donnée
             *  add_term_meta  a besoin de l'id de la taxo concerné, d'une clé (par defaut elle n est pas unique) et d'une valeur
             * 
             */

            // recuperation du term_id pour creer la metadonnée

            $term_id = get_term_by('name', $term, $taxonomy);
            $termMeta = $term_id->term_id;
            $keyMeta ='price';
            $valueMeta = 100;

            add_term_meta($termMeta, $keyMeta, $valueMeta ,$unique = true);
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
            'Mode',
            'High Tech',
            'Service à la personne'
        ];
        $taxonomy = 'ProductCategory';
        foreach ($addTaxos as $term) {
           wp_insert_term($term, $taxonomy);
               
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
        $addTaxos = [
            '1',
            '2',
            '3',
            '4',
            '5'
        ];
        $taxonomy = 'SellerRate';
        foreach ($addTaxos as $term) {
            wp_insert_term($term, $taxonomy);
        }
    }

    /**
     * Activation Plugin
     * Add capabilities to Administrator
     * 
     */
    public function activate()
    {
        $this->addCapAdmin(['classified']);
        $this->registerUserRole();
        $this->registerPostStatus();
    }
    public function registerUserRole()
    {
        add_role(
            // identifiant du role 
            'user',
            // libellé
            'Utilisateur',
            // liste des autorisatrions
            [
                'delete_user' => false,
                'delete_others_user' => false,
                'delete_private_user' => false,
                'delete_published_user' => false,
                'edit_user' => true,
                'edit_others_user' => false,
                'edit_private_user' => false,
                'edit_published_user' => true,
                'publish_user' => false,
                'read_private_user' => false,
            ]
        );
    }

    /**
     * Method to deactivate Plugin
     * 
     */
    public function deactivate()
    {
        // purge des taxo
        $arrayTaxos = [ "ProductState","SellerRate","ProductCategory","DeliveryMethod"];

        foreach ($arrayTaxos as $taxo) 
        {
            $term_args = array(
                'taxonomy' => $taxo,
                'hide_empty' => false,                
                'orderby' => 'name',                
                'order' => 'ASC'                
                );
                
            $terms = get_terms($term_args);

            foreach ($terms as $term) 
            {
                wp_delete_term($term->term_id, $taxo);
            }
        }
    }

    /**
     * Method that allows us to add the rights on the CPT (Custom Post Type) classified for the administrator role
     * 
     */

    public function addCapAdmin($customCapArray)
    {

        // methode qui nous permet d'ajouter les droits sur le CPT annonce pour le role administrateur
        //! Attention, sans cette opération le CPT Annonce va disparaire
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