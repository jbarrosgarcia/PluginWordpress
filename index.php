<?php
/*
Plugin Name: miPlugin
Plugin URI:  http://miPlugin.com
Description: Plugin de wordpress
Version:     1.0
Author:      Jose
Author URI:  http://jbarrosgarcia.com
License:     GPL2 etc
License URI: http://miPluginLicencia.com

-------------------------------------------------------------------------------- */

#Creamos una tabla cuando se activa el plugin
function createTable() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'miPlugin';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        palabra tinytext NOT NULL,
        sustituta tinytext NOT NULL,
        postId mediumint(100) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    //always return
    return $content;
}
add_action('activated_plugin', 'createTable');

/*-----------------------------------------------------------------------------*/

//CREAMOS EL SHORTCODE

function miPlugin_shortcodes_init()
{
    function miPlugin_shortcode($atts = [], $content = null)
    {
        //Añadimos a la tabla la palabra a tener en cuenta
        $atributos = shortcode_atts( array(
		    'palabra' => ' ',
		    'sustituta' => ' ',
	    ), $atts );

        global $wpdb;
        $table_name = $wpdb->prefix . 'miPlugin';
        
        $post = get_post();
        $idPost =$post->ID;
        $palabra=$atributos['palabra'];
	    $sustituir=$atributos['sustituta'];
	    
	    $numero = $wpdb->get_results( "SELECT COUNT(*) AS numero FROM wp5_miPlugin WHERE postId=".$idPost);
        
        if ($numero[0]->numero==0){
            $wpdb->insert( 
		        $table_name, 
		        array( 
			        'palabra' => $palabra, 
			        'sustituta' =>  $sustituir,
			        'postId' => $idPost
		        )    
	        );
           
        }else{
            $wpdb->update( 
                $table_name,
                array( 
                    'palabra' => $palabra, 
                    'sustituta' =>  $sustituir,
                ),
                array( 'postId' => $idPost )
            ); 
        }
    
        return $content;
    }
    add_shortcode('miPlugin', 'miPlugin_shortcode');
}
add_action('init', 'miPlugin_shortcodes_init');

/*-----------------------------------------------------------------------------*/

//FILTRO EL CONTENIDO

function actualizarPost( $text ) {
    global $wpdb;
    
    $post = get_post();
    $idPost =$post->ID;
    
    $registros = $wpdb->get_results( "SELECT palabra,sustituta FROM wp_miPlugin WHERE postId=".$idPost);

    //return $text."<h1>".$registros[0]->palabra."</h1> -- <h1>".$registros[0]->sustituta."</h1>";
    return str_replace($registros[0]->palabra,  $registros[0]->sustituta, $text );
}

add_filter( 'the_content', 'actualizarPost' );

?>