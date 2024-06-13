<div id="posts">
  <div class="posts"></div>
  <div class="total"></div>
  <div id="pagination"></div>
</div>

<script>
const totalPostsElement = document.querySelector('#posts .total');
const postsContainer = document.querySelector('#posts .posts');
const paginationContainer = document.querySelector('#posts #pagination');
const endpoint = '/wp-json/wp/v2/posts';
let currentPage = 1;

async function load(perPage) {
  const response = await fetch(`${endpoint}?per_page=${perPage}&page=${currentPage}`);
  const totalPagesHeader = parseInt(response.headers.get('X-WP-Total'));
  const totalPages = Math.ceil(totalPagesHeader / perPage);
  const posts = await response.json();

  // Clear the posts container
  postsContainer.innerHTML = '';

  // Render the posts
  posts.forEach(post => {
    const postElement = document.createElement('div');
    postElement.classList.add('posts');

    const titleElement = document.createElement('h1');
    titleElement.textContent = post.title.rendered;

    const dateElement = document.createElement('div');
    dateElement.classList.add('date');
    dateElement.textContent = `Date: ${post.date}`;

    const contentElement = document.createElement('div');
    contentElement.classList.add('content');
    contentElement.innerHTML = post.excerpt.rendered;

    postElement.appendChild(titleElement);
    postElement.appendChild(dateElement);
    postElement.appendChild(contentElement);
    postsContainer.appendChild(postElement);
  });

  // Update the total posts
  totalPostsElement.textContent = `Total posts: ${totalPagesHeader}`;

  // Render the pagination
  paginationContainer.innerHTML = '';
  for (let i = 1; i <= totalPages; i++) {
    const pageLink = document.createElement('a');
    pageLink.href = '#';
    pageLink.textContent = i;
    pageLink.style.margin = '5px';
    pageLink.addEventListener('click', async () => {
      currentPage = i;
      await load(3);
    });
    paginationContainer.appendChild(pageLink);
  }

  return currentPage;
}

(async () => {
  await load(3);
})();
</script>
