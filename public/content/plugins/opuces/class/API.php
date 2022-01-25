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
            'opuces/v1', // nom de l'API
            'create-classified',
            [
                'methods' => 'post',
                'callback' => [$this, 'createClassified']
            ]
        );

        register_rest_route(
            'opuces/v1',
            'lost-password',
            [
                'methods' => 'post',
                'callback' => [$this, 'askNewPassword']
            ]
        );

        register_rest_route(
            'opuces/v1',
            'create-new-password',
            [
                'methods' => 'patch',
                'callback' => [$this, 'sendLinkCreateNewPassword']
            ]
        );

        register_rest_route(
            'opuces/v1',
            'save-comment',
            [
                'methods' => 'post',
                'callback' => [$this, 'saveComment']
            ]
        );

        register_rest_route(
            'opuces/v1',
            'upload-image',
            [
                'methods' => 'post',
                'callback' => [$this, 'uploadImage']
            ]
        );

        register_rest_route(
            'opuces/v1',
            'create-user',
            [
                'methods' => 'post',
                'callback' => [$this, 'createUser'] 
            ]
        );


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

    public function createClassified(WP_REST_Request $request)
    {
        $title = $request->get_param('title');
        $description = $request->get_param('content');
        $author = $request->get_param('author'); //! Modifier pour supprimer le user forcé et mettre wp_current_user
        $price = $request->get_param('price');
        $type = $request->get_param('type');

        $imageId = $request->get_param('imageId');

        // récupération de l'utilisateur ayant envoyé la requête
        $user = wp_get_current_user();

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
                    'title' => $title,
                    'description' => $description,
                    'type' => $type,
                    'author' => $author,
                    'price' => $price,
                    'user' => $user,
                    'image' => $imageId
                    
                ];
        }
        return
            [
                'success' => false
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