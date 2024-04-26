<?php
$cat_query = get_queried_object();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = [
    "post_type" => "post",
    "posts_per_page" => 5,
    "cat" => $cat_query->cat_ID,
    // "category_name" => "noticia",
    "paged" => $paged
];
$query = new WP_Query($args);
?>
<div class="container" style="margin-top: 3rem;">
<div id="post__breadcrumb">
    <li>
        <a href="<?php echo home_url("category/noticia"); ?>">
        TODAS AS NOTÍCIAS 
        </a>
    </li>
    <li>
        <?php echo $cat_query->cat_name; ?>
    </li>
</div>

<?php
    if($query->have_posts()):
    while($query->have_posts()) : $query->the_post();
?>
    <div class="article">
        <?php if(has_post_thumbnail()) {
            $thumb_link = get_the_post_thumbnail_url();
            echo '<img src="' . esc_url($thumb_link) . '" alt="' . esc_attr(get_the_title()) . '">';
        }
        ?>
        <div>
            <a href="<?php the_permalink(); ?>">
                <div class="post-title">
                    <h3><?php the_title(); ?></h3>
                </div>
            </a>
            <p><?php the_excerpt(); ?></p>
        </div>
    </div>
<?php 
    endwhile;
    $pagination_args = array(
        'current' => max(1, get_query_var('paged')),
        'total'   => $query->max_num_pages,
    );

    echo '<div class="pagination">';
    echo paginate_links($pagination_args);
    echo '</div>';
?>
<?php
if ($pagination):
?>
<div class="pagination">
  <ul>
<?php
  foreach ($pagination as $page) {
    echo '<li>' . $page . '</li>';
  }
?>
  </ul>
</div>
<?php
endif;
else: 
?>
    Nenhuma postagem nessa categoria.
<?php
    endif;
    
 ?>
</div>

<style>
.article {
    margin-bottom: 1rem;
    display: flex;
    flex-direction: row;
}

.article img {
    width: 200px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 1rem;
    margin-bottom: 1rem;
}

.entry-title {
    color: #000;
    font-weight: bold;
}
#post__breadcrumb {
    display: flex;
    flex-direction: row;
}

#post__breadcrumb li {
    list-style: none;
    margin-right: 10px;
}

#post__breadcrumb li:not(:last-child)::after {
    content: '>';
    margin-left: 10px;
}

.entry-title a {
    color: #333;
    text-decoration: none;
}

.entry-title a:hover {
    text-decoration: underline;
}

.wp-post-image {
    margin: 0 auto;
    width: 30%;
}

.post {
    padding: 2rem 0;
    border-bottom: 1px dotted #ccc;
}

.pagination {
    margin-top: 20px;
    text-align: center;
}

.pagination a,
.pagination span {
    display: inline-block;
    padding: 6px 12px;
    text-decoration: none;
    background-color: #fff;
    color: #337ab7;
    border: 1px solid #ccc;
    border-radius: 3px;
}

.pagination li a:hover {
}

.pagination .current {
    background-color: #337ab7;
    color: #fff;
}
</style>
