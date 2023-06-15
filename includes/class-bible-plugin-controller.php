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

    register_rest_route( $this->namespace, '/' . $this->resource_name . '/chapters/(?P<book>[a-zA-Z0-9-]+)', array(
        array(
            'methods'   => 'GET',
            'callback'  => array( $this, 'get_chapters_by_book' ),
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

    register_rest_route( $this->namespace, '/' . $this->resource_name . '/book/(?P<book>[a-zA-Z0-9-]+)/(?P<search>([a-zA-Z]|%20)+)', array(
        array(
            'methods'   => 'POST',
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
  * Gets chapters by book and outputs it as a rest response.
  *
  * @param WP_REST_Request $request['book'] Current book.
  */
  public function get_chapters_by_book( $request ) {
    $book = $request['book'];
      
    global $wpdb;
    $results = $wpdb->get_results( 
      $wpdb->prepare("SELECT * FROM $this->tablename WHERE versiculo = \"1\" AND livro = \"$book\";")
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
    $book = urldecode_deep($request['book']);
    $search = urldecode_deep($request['search']);
      
    global $wpdb;

    $results = $wpdb->get_results( "SELECT * FROM $this->tablename WHERE `livro` LIKE \"$book\" AND `palavra` LIKE \"%$search%\";" );

    $wpdb->flush();

    return rest_ensure_response($results);
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
