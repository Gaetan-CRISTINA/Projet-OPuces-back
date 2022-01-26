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
                'callback' => [$this, 'createUser'] ,
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'opuces/v1',
            'user-table',
            [
                'methods' => 'post',
                'callback' => [$this, 'crudUserTable'] ,
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'opuces/v1',
            'user-table',
            [
                'methods' => 'get',
                'callback' => [$this, 'getUserTable'] ,
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
            }   else{
                     $where = 
                     ['userID' => $userID];
                     $wpdb->update('user_table', $data, $where);
                     $succes ='update';
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
        if($password === $confirmPassword){
            $createUserResult = wp_insert_user($user_data);
        } else {
            echo "Passwords don't Match";
        }
        if(is_int($createUserResult)) {
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
            return [ 'success' => false,
                'error' => $createUserResult
            ];
            }
     }
      /**
       * saveClassified
       *  create & update post classified
       * $request: {post_id,"content","title",author,price,[ProductCategory],[DeliveryMethod],"ProductState"
       * return: succes = creation/modif
       * 
	   */  




    public function createCustomTaxonomy(WP_REST_Request $request)
    {
        // retrieving what has been sent to the api on the endpoint /opuces/v1/create-custom-taxonomy in POST
        $idcategory = $request->get_param('idcategory');
        $name = $request->get_param('name');
        $parentCategory = $request->get_param('parentCategory');
        $description = $request->get_param('description');

        //adding parentcategory
        $term_id = get_term_by('name',$parentCategory, 'ProductCategory');
        $categoryIdParent = $term_id->term_id;

        $args = [
            'description' => $description,
            'slug' => '',
            'parent' => $categoryIdParent
        ];
        //inserting the new custom taxonomy in database
        $createCustomTaxonomyResult = wp_insert_term( 
            $name, 
            $idcategory,
            $args
        );

        //verification if taxonomy already exist
        if(is_int($createCustomTaxonomyResult)){

        };
        


    public function saveClassified(WP_REST_Request $request)
    {
        $idProduct =[];
        $idDelivery =[];
        $title = $request->get_param('title');
        $description = $request->get_param('content');
        $author = $request->get_param('author'); //! Modifier pour supprimer le user forcé et mettre wp_current_user
        $price = $request->get_param('price');
        $idProduct = $request->get_param('ProductCategorie');
        $idDelivery = $request->get_param('DeliveryMethod');
        $idState = $request->get_param('ProductState');
        $post_id = $request->get_param('post_id');
        $classifiedBuyerId = $request->get_param('classifiedBuyerId');
        $imageId = $request->get_param('imageId');
        
        // récupération de l'utilisateur ayant envoyé la requête

        // on regarde si c'est pour une creation ou une modification
        $postStatus = get_post_status($post_id);
        $argsPost = 
        [
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $description,
            'post_author'  =>  $author,
            'post_status' => 'publish',
            'post_type' => 'classified',
        ];
        $user = wp_get_current_user();
        if (!$postStatus) 
        {
            $classifiedSaveResult = wp_insert_post
            (
                $argsPost
            );
        }
        else 
        {
            $user = wp_get_current_user();

            $classifiedSaveResult = wp_update_post
            (
                $argsPost
            );
        }
            // si pas d erreur je met a jout taxo & custum field
            if (!is_wp_error($classifiedSaveResult)) {
                $success = true;
            // les 3 premiers wp_set_object servent a effacer les taxo existantes si elles existent
                wp_set_object_terms($classifiedSaveResult, null, 'ProductCategory');
                wp_set_object_terms($classifiedSaveResult, null, 'DeliveryMethod');
                wp_set_object_terms($classifiedSaveResult, null, 'ProductState');
                wp_set_object_terms($classifiedSaveResult, $idProduct, 'ProductCategory');
                wp_set_object_terms($classifiedSaveResult, $idDelivery, 'DeliveryMethod');
                wp_set_object_terms($classifiedSaveResult, $idState, 'ProductState');
    
                if ($price > 0) 
                {
                    $keyMeta ='classifiedPrice';
                    // test si une meta existe
                    if (get_post_meta($classifiedSaveResult, $keyMeta, true ) ) {
                        update_post_meta($classifiedSaveResult, $keyMeta, $price);
                    }
                    else {
                        add_post_meta($classifiedSaveResult, $keyMeta, $price, $unique = true);
                    }
                }
                // si objet achete
                if ($classifiedBuyerId > 0) 
                {
                    $keyMeta ='classifiedBuyerId';
                    // test si une meta existe
                    if (get_post_meta($classifiedSaveResult, $keyMeta, true ) ) {
                        update_post_meta($classifiedSaveResult, $keyMeta, $classifiedBuyerId);
                    }
                    else {
                        add_post_meta($classifiedSaveResult, $keyMeta, $classifiedBuyerId, $unique = true);
                    }
                }

            }
            else {
            $success = false;
            }

            return 
            [
            'success' => $success,
            'postid' => $classifiedSaveResult,
            'poststatus' => $postStatus
            ];
    }
  
    // demande de token pour nouveau mot de passe
    public function askNewPassword(WP_REST_Request $request)
    {
        $email = $request->get_param('email');
        if($email >0){
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
            in_array ('user', (array) $user->roles) ||
            in_array ('administrator', (array) $user->roles) ||
            in_array ('moderateur', (array) $user->roles)
        ) {
            $saveCommentResult = wp_insert_comment(
                [
                    'user_id' => $user->ID,
                    'comment_post_ID' => $userId,
                    'comment_content' => $comment,
                ]
                );
                if(is_int($saveCommentResult)){
                    return [
                        'success' => true,
                        'userId' => $userId,
                        'comment' => $comment,
                        'user' => $user,
                        'comment-id' => $saveCommentResult
                    ];
                } else {
                    return [ 'success' => false];
                }
        } else {
            return [ 'success' => false];
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

        if($success) {
            // on récupère des infos dont wordpress a besoin pour identifier le type de fichier
            $imageType =  wp_check_filetype( $imageDestination, null);
            // préparation des informations nécessaires pour créer le media
            $attachment = array(
                'post_mime_type' => $imageType['type'],
                'post_title' => $imageName,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            // on enregistre l'image dans wordpress
            $attachmentId = wp_insert_attachment( $attachment, $imageDestination );
            if(is_int($attachmentId)) {
                require_once( ABSPATH . 'wp-admin/includes/image.php');
                $metadata = wp_generate_attachment_metadata( $attachmentId, $imageDestination ); // on génère les métadonnées
                // on met à jour les metadata du media
                wp_update_attachment_metadata( $attachmentId, $metadata );

                return [
                    'status' => 'success',
                    'image' => [
                        'url' => $destination['url'] . '/' . $imageName,
                        'id' => $attachmentId
                    ]
                ];
             }else {
                 return ['status' => 'failed'];  
                 }
            }

            return ['status' => 'failed'];
        

    }




}