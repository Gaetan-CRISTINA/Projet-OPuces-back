<?php

namespace OPuces;

use WP_REST_Request;
use WP_USER;
use WP_Query;

class Api
{

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
                'callback' => [$this, 'createCustomTaxonomy'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(

            'opuces/v1', // API Name
            'taxonomy', // name of route
            [
                'methods' => 'get',
                'callback' => [$this, 'getCustomTaxonomy'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(

            'opuces/v1', // API Name
            'update-taxonomy', // name of route
            [
                'methods' => 'put',
                'callback' => [$this, 'updateCustomTaxonomy'],
                'permission_callback' => '__return_true',

            ]
        );
        register_rest_route(
            'opuces/v1', // nom de l'API
            'save-classified',
            [
                'methods' => 'post',
                'callback' => [$this, 'saveClassified'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'lost-password',
            [
                'methods' => 'post',
                'callback' => [$this, 'askNewPassword'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'create-new-password',
            [
                'methods' => 'patch',
                'callback' => [$this, 'sendLinkCreateNewPassword'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'save-comment',
            [
                'methods' => 'post',
                'callback' => [$this, 'saveComment'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'upload-image',
            [
                'methods' => 'post',
                'callback' => [$this, 'uploadImage'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'create-user',
            [
                'methods' => 'post',
                'callback' => [$this, 'createUser'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'user-table',
            [
                'methods' => 'post',
                'callback' => [$this, 'crudUserTable'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'user-table',
            [
                'methods' => 'get',
                'callback' => [$this, 'getUserTable'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'opuces/v1',
            'test-query',
            [
                'methods' => 'get',
                'callback' => [$this, 'getQueryClassified'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * crudUserTable
     *  create & update user_table
     * $request: {"user_id","adress1","adress2","zipcode","city","country","latitude","longitude",
     * "rate", "phone_number", "crud" }
     * return: succes = creation/modif
     * 
     */
    public function crudUserTable(WP_REST_Request $request)
    {
        // $crud = $request->get_param('crud');
        $data = [
            'userID' => $request->get_param('userID'), //! Modifier pour supprimer le user forcé et mettre wp_current_user
            'adress1' => $request->get_param('adress1'),
            'adress2' => $request->get_param('adress2'),
            'zipcode' => $request->get_param('zipcode'),
            'city' => $request->get_param('city'),
            'country' => $request->get_param('country'),
            'phone_number' => $request->get_param('phone_number'),
            'latitude' => $request->get_param('latitude'),
            'longitude' => $request->get_param('longitude'),
            'rate' => $request->get_param('rate')
        ];

        global $wpdb;

        $userID = $request->get_param('userID');
        $table_name = 'user_table';
        // prepare <==> prepare  de pdo 
        // %s – string (value is escaped and wrapped in quotes)
        // %d – integer
        // %f – float
        // %% – % sign
        $user_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM `$table_name` WHERE userID = %d", $userID));

        if ($user_count == 0) {
            $wpdb->insert('user_table', $data);
            $succes = 'insert';
        } else {
            $where =
                ['userID' => $userID];
            $wpdb->update('user_table', $data, $where);
            $succes = 'update';
        }
        return $succes;
    }

    /**
     *  getUserTable
     *  get field of user_table
     * $request: {"user_id",}
     * return: succes = true/false
     * 
     */
    public function getUserTable(WP_REST_Request $request)
    {
        global $wpdb;

        $userID = $request->get_param('userID');
        $table_name = 'user_table';
        $user_count = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE userID = %d", $userID));

        return $user_count;
    }

    /**
     *  createUserr
     *  return success true or false
     * 
     */
    public function createUser(WP_REST_Request $request)
    {
        $userName = $request->get_param('userName');
        $email = $request->get_param('email');
        $password = $request->get_param('password');
        $confirmPassword = $request->get_param('confirmPassword');
        // $image = [$this, 'uploadImage'];
        $user_data = array(
            'ID' => '',
            'user_login' => $userName,
            'user_pass' => $password,
            'user_email' => $email,
            'role' => 'user'
        );
        if ($password === $confirmPassword) {
            $createUserResult = wp_insert_user($user_data);
        } else {
            echo "Passwords don't Match";
        }
        if (is_int($createUserResult)) {
            $user = new WP_USER($createUserResult);
            $user->remove_role('subscriber');
            $user->add_role('user');
            return [
                'success' => true,
                'userId' => $createUserResult,
                'userName' => $userName,
                'email' => $email,
                'role' => 'user',
                // 'image' => $image,
            ];
        } else {
            return [
                'success' => false,
                'error' => $createUserResult
            ];
        }
    }

    /**
     * getCustomTaxonomy
     *  read Taxo
     * $request: {"nomtaxo", idparent"}
     * si idparent = 0 toutes les categories sinon les sous categories de idparent
     * return: {["nomtaxo",idparent, "name"]}
     * 
     */
    public function getCustomTaxonomy(WP_REST_Request $request)
    {
        // retrieving what has been sent to the api on the endpoint /opuces/v1/taxonomy in get
        $taxonomy = $request->get_param('taxonomy');
        $idParent = $request->get_param('idParent');

        $termChildren = get_terms(
            [
                'taxonomy' => $taxonomy,
                'hide_empty' => 0,
                'parent' => $idParent
            ]
        );

        if (!is_wp_error($termChildren)) {
            $success = $termChildren;
        } else {
            $success = false;
        }

        return
            [
                'success' => $success,
            ];
    }


    public function getQueryClassified(WP_REST_Request $request)
    {
        $dateStart = $request->get_param('dateStart');
        $dateEnd = $request->get_param('DateEnd');


        global $wpdb;

        $sql = "
        SELECT wp_posts.* , wp_postmeta.* , wp_term_relationships.term_taxonomy_id ,  wp_terms.name, wp_term_taxonomy.taxonomy ,
        user_table.* , wp_termmeta.meta_value as metadeliveryprice
        FROM wp_posts 
        INNER JOIN wp_postmeta
        ON wp_postmeta.post_id = wp_posts.ID
        INNER JOIN user_table
        ON user_table.userID = wp_posts.post_author
        INNER JOIN wp_term_relationships
        ON wp_term_relationships.object_id = wp_posts.ID
        INNER JOIN wp_terms
        ON wp_term_relationships.term_taxonomy_id = wp_terms.term_id
        INNER JOIN wp_term_taxonomy
        ON wp_term_taxonomy.term_taxonomy_id = wp_terms.term_id
        LEFT JOIN wp_termmeta
        ON wp_termmeta.term_id = wp_terms.term_id
        WHERE wp_posts.post_type = 'classified' 
        AND wp_posts.post_status = 'publish' 
        AND post_date > $dateStart AND post_date < $dateEnd;
            ";

        $resultQueryClassified = $wpdb->get_results($wpdb->prepare($sql));

        return [$resultQueryClassified];
    }

    /**
     * createCustomTaxonomy
     *  create & update post classified
     * $request: {categoryId,"name","parentCategory","description","taxonomy"
     * return: succes = creation/modif
     * 
     */
    public function createCustomTaxonomy(WP_REST_Request $request)
    {
        // retrieving what has been sent to the api on the endpoint /opuces/v1/create-custom-taxonomy in POST
        $categoryId = $request->get_param('categoryId');
        $name = $request->get_param('name');
        $parentCategory = $request->get_param('parentCategory');
        $description = $request->get_param('description');
        $taxonomy = $request->get_param('taxonomy');


        //inserting parentcategory
        $term_id = get_term_by('name', $parentCategory, $taxonomy);
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

        /**
         * saveClassified
         *  create & update post classified
         * $request: {post_id,"content","title",author,price,[ProductCategory],[DeliveryMethod],"ProductState"
         * return: succes = creation/modif
         * 
         */


        //inserting parentcategory
        $term_id = get_term_by('name', $parentCategory, $taxonomy);
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
    }

    /**
     * Update a custom taxonomy
     * retrieving what has been sent to the api on the endpoint /opuces/v1/update-taxonomy in PUT
     */
    public function updateCustomTaxonomy(WP_REST_Request $request)
    {

        $categoryId = $request->get_param('categoryId');
        $parentCategory = $request->get_param('parentCategory');
        $description = $request->get_param('description');
        $taxonomy = $request->get_param('taxonomy');
        $name = $request->get_param('name');


        $updateTaxonomy = wp_update_term(
            $categoryId,
            $taxonomy,
            [
                'alias_of' => '',
                'description' => $description,
                'parent' => $parentCategory,
                'slug' => '',
                'name' => $name
            ]

        );

        if (!is_wp_error($updateTaxonomy)) {
            echo 'Success!';
        } else {
            return [
                'success' => false,
                'error' => $updateTaxonomy
            ];
        };
    }


    /**
     * saveClassified
     *  create & update post classified
     * $request: {post_id,"content","title",author,price,[ProductCategory],[DeliveryMethod],"ProductState"
     * return: succes = creation/modif
     * 
     */
    public function saveClassified(WP_REST_Request $request)
    {

        $idProduct = [];
        $idDelivery = [];
        $title = $request->get_param('title');
        $description = $request->get_param('description');
        $user = get_current_user_id();
        $price = $request->get_param('price');
        $idProduct = $request->get_param('ProductCategorie');
        $idDelivery = $request->get_param('DeliveryMethod');
        $idState = $request->get_param('ProductState');
        $post_id = $request->get_param('post_id');
        $classifiedBuyerId = $request->get_param('classifiedBuyerId');
        $content = $request->get_param('content');
        $imageId = $request->get_param('imageId');

        // récupération de l'utilisateur ayant envoyé la requête

        // on regarde si c'est pour une creation ou une modification
        $postStatus = get_post_status($post_id);
        $argsPost =
            [
                'ID' => $post_id,
                'post_title' => $title,
                'post_excerpt' => $description,
                'post_author'  =>  $user,
                'post_type' => 'classified',
                'post_content' => $content
            ];
        $user = wp_get_current_user();

        if ($classifiedBuyerId > 0) {
            $argsPost['post_status'] = 'vendu';
        } else {
            $argsPost['post_status'] = 'publish';
        }

        if (!$postStatus) {
            // $argsPost['post_status'] = 'notValided';

            $classifiedSaveResult = wp_insert_post(
                $argsPost
            );
        } else {
            $classifiedSaveResult = wp_update_post(
                $argsPost
            );
        }

        // si pas d erreur je met a jour taxo & custum field
        if (!is_wp_error($classifiedSaveResult)) {
            $success = true;
            // les 3 premiers wp_set_object servent a effacer les taxo existantes si elles existent
            wp_set_object_terms($classifiedSaveResult, null, 'ProductCategory');
            wp_set_object_terms($classifiedSaveResult, null, 'DeliveryMethod');
            wp_set_object_terms($classifiedSaveResult, null, 'ProductState');
            wp_set_object_terms($classifiedSaveResult, $idProduct, 'ProductCategory');
            wp_set_object_terms($classifiedSaveResult, $idDelivery, 'DeliveryMethod');
            wp_set_object_terms($classifiedSaveResult, $idState, 'ProductState');

            // recuperation des parents d une categorie
            $terms = get_the_terms($classifiedSaveResult, 'ProductCategory');
            $categorieparent_id = [];
            // pour chaque term récupéré 
            foreach ($terms as $term) { // pour chaque term récupéré
                // s'il n'a pas de parent
                if ($term->parent == 0) {
                    // et bien on le tient, c'est le parent
                    $categorieparent_id[] = $term->term_id;
                    // sinon
                } else {
                    // on va chercher ses parents
                    $categorieparent_id[] = $term->parent;
                }
            }
            // puis on prend le premier terme trouvé
            $term_categorieparent_id = $categorieparent_id[0];
            wp_set_post_terms($classifiedSaveResult, [$term_categorieparent_id], 'ProductCategory', $append = true);

            if ($price > 0) {
                $keyMeta = 'classifiedPrice';
                // test si une meta existe
                if (get_post_meta($classifiedSaveResult, $keyMeta, true)) {
                    update_post_meta($classifiedSaveResult, $keyMeta, $price);
                } else {
                    add_post_meta($classifiedSaveResult, $keyMeta, $price, $unique = true);
                }
            }
            // je met a jour l'image
            if ($imageId) {
                set_post_thumbnail(
                    $classifiedSaveResult,
                    $imageId
                );
            }
            // si objet achete
            if ($classifiedBuyerId > 0) {
                $keyMeta = 'classifiedBuyerId';
                // test si une meta existe
                if (get_post_meta($classifiedSaveResult, $keyMeta, true)) {
                    update_post_meta($classifiedSaveResult, $keyMeta, $classifiedBuyerId);
                } else {
                    add_post_meta($classifiedSaveResult, $keyMeta, $classifiedBuyerId, $unique = true);
                }
            }
        } else {
            $success = false;
        }
        return
            [
                'success' => $success,
                'post_id' => $classifiedSaveResult,
                'post_status' => $postStatus,
                'title' => $title,
                'auteur' => $user,
                'description' => $description,
                'content' => $content,
                'price' => $price,
                'ProductCategory' => $idProduct,
                'DeliveryMethod' => $idDelivery,
                'ProductState' => $idState,
                'classifiedBuyerId' => $classifiedBuyerId
            ];
    }


    // demande de token pour nouveau mot de passe
    public function askNewPassword(WP_REST_Request $request)
    {
        $email = $request->get_param('email');
        if ($email > 0) {
            [$this, 'sendLinkCreateNewPassword'];
        }
        return ['email' => $email];
    }

    public function sendLinkCreateNewPassword()
    {
        //TODO fonction pour réinitialiser son mot de passe
        // voir plugin pour gestion des envoies de mail avec WP
        //https://landing.sendinblue.com/fr/plugin-wordpress?utm_source=adwords&utm_medium=cpc&utm_content=Email&utm_extension=&utm_term=%2Bplugin%20%2Bemail%20%2Bwordpress&utm_matchtype=b&utm_campaign=228702562&utm_network=g&km_adid=514520855401&km_adposition=&km_device=c&utm_adgroupid=58276968658&gclid=Cj0KCQiAubmPBhCyARIsAJWNpiMeE-SyrytjoDN9kAkIqWT2EU2Id4BeBmhwYWS9KpmutBM1jY66FAkaAhdGEALw_wcB
    }

    public function saveComment(WP_REST_Request $request)
    {
        $comment = $request->get_param('comment'); //user ou seller ?
        $userId = $request->get_param('userId');
        $user = wp_get_current_user();

        if (
            in_array('user', (array) $user->roles) ||
            in_array('administrator', (array) $user->roles) ||
            in_array('moderateur', (array) $user->roles)
        ) {
            $saveCommentResult = wp_insert_comment(
                [
                    'user_id' => $user->ID,
                    'comment_post_ID' => $userId,
                    'comment_content' => $comment,
                ]
            );
            if (is_int($saveCommentResult)) {
                return [
                    'success' => true,
                    'userId' => $userId,
                    'comment' => $comment,
                    'user' => $user,
                    'comment-id' => $saveCommentResult
                ];
            } else {
                return ['success' => false];
            }
        } else {
            return ['success' => false];
        }
    }

    public function uploadImage()
    {
        //echo 'je suis dans uploadImage';
        $imageFileIndex = 'image'; // correspond au nom de la variable utilisée pour envoyer l'image
        $imageData = $_FILES[$imageFileIndex]; // récupération des informations concernant l'image uploadée
        $imageSource = $imageData['tmp_name']; // récupération du chemin fichier dans lequel est stockée l'image qui vient d'être uploadée
        $destination = wp_upload_dir(); // récupération es informations du dossier dans lequel wp stocke les fichiers uploadés
        $imageDestinationFolder = $destination['path']; // dossier worpdress dans lequel nous allons stocker l'image
        // nettoyage d'un nom de fichier avec wp https://developer.wordpress.org/reference/functions/sanitize_file_name/
        $imageName = sanitize_file_name(
            md5(uniqid()) . '-' . // génération d'une partie aléatoire pour ne pas écraser de fichier existant
                $imageData['name']
        );
        $imageDestination = $imageDestinationFolder . '/' . $imageName;
        $success = move_uploaded_file($imageSource, $imageDestination);  // on déplace le fichier uploadé dans le dossier de stokage de wp

        if ($success) {
            // on récupère des infos dont wordpress a besoin pour identifier le type de fichier
            $imageType =  wp_check_filetype($imageDestination, null);
            // préparation des informations nécessaires pour créer le media
            $attachment = array(
                'post_mime_type' => $imageType['type'],
                'post_title' => $imageName,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            // on enregistre l'image dans wordpress
            $attachmentId = wp_insert_attachment($attachment, $imageDestination);
            if (is_int($attachmentId)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $metadata = wp_generate_attachment_metadata($attachmentId, $imageDestination); // on génère les métadonnées
                // on met à jour les metadata du media
                wp_update_attachment_metadata($attachmentId, $metadata);

                return [
                    'status' => 'success',
                    'image' => [
                        'url' => $destination['url'] . '/' . $imageName,
                        'id' => $attachmentId
                    ]
                ];
            } else {
                return ['status' => 'failed'];
            }
        }

        return ['status' => 'failed'];
    }
}
