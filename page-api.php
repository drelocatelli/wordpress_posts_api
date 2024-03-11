<?php

header('Content-Type: application/json');

$target = $_GET['target'];

class Api {

    private function get($args) 
    {
        $pages = get_posts( $args );
        
        $pages_data = array(); // Initialize an array to store page data
        
        foreach ( $pages as $page ) {
            setup_postdata( $page );
        
            // Get page data
            $page_data = array(
                'title' => get_the_title( $page->ID ),
                'content' => get_the_content( $page->ID ),
                // Add more page data as needed
            );
        
            // Add page data to the array
            $pages_data[] = $page_data;
        }
        wp_reset_postdata(); // Restore original post data
        
        // Convert page data to JSON format
        $json_data = json_encode( $pages_data );
        
        // Output JSON data
        echo $json_data;
    }

    public function get_posts($args) {
        $this->get($args);
    }

    public function get_pages($args)
    {
        $this->get($args);
    }

    public function get_categories()
    {
        $categories = get_categories();
        if ( ! empty( $categories ) ) {
                $categories_data = array(); // Initialize an array to store category data

                foreach ( $categories as $category ) {
                    $category_data = array(
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        // Add more category data as needed
                    );

                    $categories_data[] = $category_data;
                }

                // Convert category data to JSON format
                $json_data = json_encode( $categories_data );

                // Output JSON data
                echo $json_data;
        } else {
            echo json_encode([]);
        }

    }
    
}

$api = new Api();

$args = [
    'post_type' => 'post', // Specify the post type
    'post_status' => 'publish', // Fetch only published posts
    'posts_per_page' => -1, // Fetch all posts (use -1 to retrieve all posts)
];

switch($target) {
    case 'posts':
        $api->get_posts($args);
    break;
    case 'pages':
        $args['post_type'] = 'page';
        $api->get_pages($args);
    break;
    case 'categories':
        $api->get_categories($args);
    break;
}

