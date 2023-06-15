# Bíblia Digital

## Tenha a Bíblia Sagrada em seu site para que seus visitantes acessem e conheçam mais sobre a palavra.

![]()

### biblia-digital.php

Code:

```php
<?php
/*
  Plugin Name: Bíblia Digital
  Plugin URI: estudobiblico.org
  Description: Tenha a Bíblia Sagrada em seu site para que seus visitantes<br/>
  acessem e conheçam mais sobre a palavra.
  Version: 1.0.0
  Author: leonardo José Nunes
  Author URI: https://wgetplugin.swaramadra.net
  URI: https://estudobiblico.org
  License: GNU General Public License
*/

if( ! defined('ABSPATH') ) exit;

require_once(plugin_dir_path( __FILE__ ) . 'includes/class-bible-plugin-config.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/class-bible-plugin-controller.php');

function biblia_digital_shortcode() {
  wp_enqueue_script( 'biblia_digital_plugin_js', plugin_dir_url( __FILE__  ) . "build/index.js", ["wp-element"], microtime(), true );
  wp_enqueue_style( 'biblia_digital_plugin_css', plugin_dir_url( __FILE__ ) . "build/index.css", null, microtime() );
	wp_localize_script( 'biblia_digital_plugin_js', 'estudobiblico', array(
    'root_url' => get_site_url()
  ));
  return "<div class='biblia-digital-plugin'></div>";
}

add_shortcode( 'biblia-digital', 'biblia_digital_shortcode' );
?>

```



[config](i./ncludes/class-bible-plugin-config.php)

```php
<?php

class BiblePluginRestController {
  public function __construct() {
    global $wpdb;
    $this->namespace     = '/bible/v1';
    $this->resource_name = 'search';
    $this->tablename = $wpdb->prefix . "bible";
  }
  
  public function register_routes() {
    register_rest_route( $this->namespace, '/' . "count", array(
      array(
          'methods'   => 'GET',
          'callback'  => array( $this, 'get_items_count' )
      )
    ) );

    register_rest_route( $this->namespace, '/' . $this->resource_name, array(
        array(
            'methods'   => 'GET',
            'callback'  => array( $this, 'get_items' ),
        ),
        'schema' => array( $this, 'get_item_schema' ),
    ) );

    register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
        array(
            'methods'   => 'GET',
            'callback'  => array( $this, 'get_item_by_id' ),
        ),
        'schema' => array( $this, 'get_item_schema' ),
    ) );

    register_rest_route( $this->namespace, '/' . $this->resource_name . '/books/(?P<book>[a-zA-Z0-9-]+)', array(
        array(
            'methods'   => 'GET',
            'callback'  => array( $this, 'get_item_by_book' ),
        ),

        'schema' => array( $this, 'get_item_schema' ),
    ) );

    register_rest_route( $this->namespace, '/' . $this->resource_name . '/books/(?P<book>[a-zA-Z0-9-]+)/(?P<chapter>[\d]+)', array(
        array(
            'methods'   => 'GET',
            'callback'  => array( $this, 'get_item_by_book_and_chapter' ),
        ),
        'schema' => array( $this, 'get_item_schema' ),
    ) );

    register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<book>[a-zA-Z0-9-]+)/(?P<searchWord>[a-zA-Z0-9-]+)', array(
        array(
            'methods'   => 'GET',
            'callback'  => array( $this, 'seach_items_by_book' ),
        ),
        'schema' => array( $this, 'get_item_schema' ),
    ) );
  }

  public function get_items_count( $request ) {
    global $wpdb;

    $book = $request["book"];

    if($book === "TB"){
      $result = $wpdb->get_results( 
        $wpdb->prepare("SELECT count(*) AS count FROM $this->tablename;")
      );
    } else {
      $result = $wpdb->get_results( 
        $wpdb->prepare("SELECT count(*) AS count FROM $this->tablename WHERE livro = \"$book\";")
      );
    }
    $wpdb->flush();

    return $result;
  }

  /**
  * Gets versicles in a range of start to size and outputs them as a rest response.
  *
  * @param WP_REST_Request $request['page'] initial query point.
  * 
  * @param WP_REST_Request $request['rowsPerPage'] number of results.  
  * 
  * @param WP_REST_Request $request['book'] desired book.
  *
  * @return rest of items in range of page - rowsPerPage
  *
  */
  public function get_items( $request ) {
    global $wpdb;
    $book = $request["book"];
    $page = $request['page'] ? $request['page'] : 0;
    $rowsPerPage = $request['rowsPerPage'] ? $request['rowsPerPage'] : 10;

    if( $book == "TB" ){
      $results = $wpdb->get_results( 
        $wpdb->prepare("SELECT * FROM $this->tablename ORDER BY livroseq, capitulo, versiculo LIMIT $page, $rowsPerPage;")
      );} 
    else {
      $results = $wpdb->get_results( 
        $wpdb->prepare("SELECT * FROM $this->tablename  WHERE livro = \"$book\"  ORDER BY livroseq, capitulo, versiculo LIMIT $page, $rowsPerPage;")
      );
    }
    $wpdb->flush();
    $data = array();

    if ( empty( $results ) ) {
        return rest_ensure_response( $data );
    }

    return rest_ensure_response( $results );
  }

  /**
  * Gets versicle data of requested id and outputs it as a rest response.
  *
  * @param WP_REST_Request $request Current request.
  *
  * @return result by id or nothing if not found
  */
  public function get_item_by_id( $request ) {
    $id = (int) $request['id'];
      
    global $wpdb;
    $result = $wpdb->get_results( 
      $wpdb->prepare("SELECT * FROM $this->tablename WHERE id = $id")
    );
    $wpdb->flush();

    $data = array("Id $id NOT FOUND!");

    if ( empty( $result ) ) {
        return rest_ensure_response( $data );
    }

    return rest_ensure_response($result);
  }

  /**
  * Gets book data of requested book code and outputs it as a rest response.
  *
  * @param WP_REST_Request $request Current request.
  */
  public function get_item_by_book( $request ) {
    $book = $request['book'];
      
    global $wpdb;
    $results = $wpdb->get_results( 
      $wpdb->prepare("SELECT * FROM $this->tablename WHERE livro LIKE \"$book\";")
    );
    $wpdb->flush();

    $data = array("Book $book NOT FOUND!");

    if ( empty( $results ) ) {
        return rest_ensure_response( $data );
    }

    return rest_ensure_response($results);
  }
  /**
  * Gets data of requested book chapter and outputs it as a rest response.
  *
  * @param WP_REST_Request $$request['book'] book code.
  * @param WP_REST_Request $request['chapter'] chapter number.
  *
  * @return est_ensure_response( $data ) 
  */
  public function get_item_by_book_and_chapter( $request ) {
    $book = $request['book'];
    $chapter = $request['chapter'];
      
    global $wpdb;
    $result = $wpdb->get_results( 
      $wpdb->prepare("SELECT * FROM $this->tablename WHERE livro LIKE \"$book\" AND capitulo = $chapter;")
    );
    $wpdb->flush();

    $data = array("Versículo $chapter NOT FOUND!");

    if ( empty( $result ) ) {
        return rest_ensure_response( $data );
    }

    return rest_ensure_response($result);
  }

  /**
  * search word and outputs it as a rest response.
  *
  * @param WP_REST_Request $$request['book'] book code.
  * @param WP_REST_Request $request['searchWord'] search word
  *
  * @return est_ensure_response( $data ) 
  */
  public function seach_items_by_book( $request ) {
    $book = $request['book'];
    $searchWord = $request['searchWord'];
      
    global $wpdb;
    $result = "";

    if($book === "TB"){
      $result = $wpdb->get_results( 
        $wpdb->prepare("SELECT * FROM $this->tablename WHERE palavra like \"%$searchWord%\";")
      );
    } else {
      $result = $wpdb->get_results( 
        $wpdb->prepare("SELECT * FROM $this->tablename WHERE livro like \"$book\" AND palavra like \"%$searchWord%\";")
      );
    }

    $wpdb->flush();

    $data = array("Palavra $searchWord NOT FOUND!");

    if ( empty( $result ) ) {
        return rest_ensure_response( $data );
    }

    return rest_ensure_response($result);
  }

  /**
  * Get our sample schema for a post.
  *
  * @return array The sample schema for a post
  */
  public function get_item_schema() {
    if ( $this->schema ) {
        // Since WordPress 5.3, the schema can be cached in the $schema property.
        return $this->schema;
    }

    $this->schema = array(
        // This tells the spec of JSON Schema we are using which is draft 4.
        '$schema'              => 'http://json-schema.org/draft-04/schema#',
        // The title property marks the identity of the resource.
        'title'                => 'versicle',
        'type'                 => 'object',
        // In JSON Schema you can specify object properties in the properties attribute.
        'properties'           => array(
            'id' => array(
                'description'  => esc_html__( 'Unique identifier for the object.', 'my-textdomain' ),
                'type'         => 'integer',
                'context'      => array( 'view', 'edit', 'embed' ),
                'readonly'     => true,
            ),
            'content' => array(
                'description'  => esc_html__( 'The content for the object.', 'my-textdomain' ),
                'type'         => 'string',
            ),
        ),
    );

    return $this->schema;
  }
}

function register_bible_rest_routes() {
  $controller = new BiblePluginRestController();
  $controller->register_routes();
}

add_action( 'rest_api_init', 'register_bible_rest_routes' );
?>

```

### Package

```json
{
  "name": "biblia-digital",
  "version": "1.0.0",
  "description": "",
  "main": "index.js",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "plugin-zip": "wp-scripts plugin-zip"
  },
  "keywords": [],
  "author": "",
  "license": "ISC",
  "devDependencies": {
    "@wordpress/scripts": "^26.5.0"
  },
  "dependencies": {
    "@emotion/react": "^11.11.0",
    "@emotion/styled": "^11.11.0",
    "@mui/material": "^5.13.2",
    "axios": "^1.4.0"
  }
}
```

