<?php

header('Content-Type: application/json');

$target = $_GET['target'];

class Api {

    private function get($args, $category, $page_num, $search_name) 
{
    $all_posts_args = [
        'post_type' => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ];

    if(isset($category)) {
        $all_posts_args['category_name'] = $category;
        $args['category_name'] = $category;
    } 

    if(isset($page_num)) {
        $args['paged'] = $page_num ?? 1;
    }
    
    if(isset($search_name)) {
        $args['s'] = $search_name;
    }

    $posts = get_posts($args);

    $all_posts = [
        "articles" => [],
    ];

    $all_posts_query = get_posts($all_posts_args);

    foreach ( $posts as $post ) {
        setup_postdata( $post );

        $post_data = [
            'title' => get_the_title($post->ID),
            'permalink' => get_the_permalink( $post->ID ),
            'date' => $post->post_date,
            'excerpt' => strip_tags(get_the_excerpt($post->ID)),
            'thumbnail' => get_the_post_thumbnail($post->ID),
            'content' => get_the_content( $post->ID ),
            // Add more post data as needed
        ];

        array_push($all_posts["articles"], $post_data);

        if($page_num && $args['posts_per_page']) {
            $all_posts['pagination'] = [
                "current_page" => $page_num ?? null,
                "per_page" => $args['posts_per_page'] ?? null,
                "length" => count($posts),
                "total_posts" => count($all_posts_query),
            ];
        }

        if(isset($args['category_name'])) {
            $post_data['category_name'] = $args['category_name'];
        } 

    }
    wp_reset_postdata(); // Restore original post data
    
    // Convert page data to JSON format
    $json_data = json_encode( $all_posts );
    
    // Output JSON data
    echo $json_data;
}

    public function get_posts($args, $category, $page_num, $search_name) {
        $this->get($args, $category, $page_num, $search_name);
    }

    public function get_pages($args, $category, $page_num)
    {
        $this->get($args, null, null, null);
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
    'posts_per_page' =>  $_GET['per_page'] ? $_GET['per_page'] : -1, // Fetch all posts (use -1 to retrieve all posts)
];


switch($target) {
    case 'posts':
        $api->get_posts($args, $_GET['category'], $_GET['page_num'], $_GET['search_name']);
    break;
    case 'pages':
        $args['post_type'] = 'page';
        $api->get_pages($args, $_GET['category'], $_GET['page_num']);
    break;
    case 'categories':
        $api->get_categories();
    break;
}
