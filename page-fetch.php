<div id="content_posts">
    <form id="get_posts" method="get">
        <input type="search" name="keyword" placeholder="Digite sua busca...">
        <select name="category">
            <option value="">Aguarde...</option>
        </select>
        <button type="submit">Buscar</button>
    </form>
    <div id="posts">
        <div class="post-entry">
            <div class="post-loading">
                Carregando...
            </div>
            <div class="post">
            </div>
        </div>
        <div class="pagination">
            
        </div>
    </div>
</div>

<script>
    const postLoadingEl = document.querySelector('.post-loading');
    const perPage = 2;
    const params = new URLSearchParams(window.location.search);

    function loadCategories() {
        const categoryIn = document.querySelector('select[name="category"]');
        fetch('/api?target=categories')
            .then((response) => response.json())
            .then((response) => {
                categoryIn.innerHTML = '';
                response.forEach((category) => {
                    categoryIn.add(new Option(category.name, category.name));
                })
            })
            .catch((err) => {
                categoryIn.querySelector('option').innerText = 'Ocorreu um erro';
                console.error(err);
            })
    }
    
    async function search(e) {
        const form = document.querySelector('form#get_posts');
        form.onsubmit = async(e) => {
            e.preventDefault();
            
            const data = new FormData(form);
            const keyword = data.get('keyword');
            const category = data.get('category');
            
            const params = new URLSearchParams(window.location.search);
            params.set('target', 'posts');
            if (category) params.set('category', category);
            if (keyword) {
                params.set('search_name', keyword)
            } else {
                params.delete('search_name');
            }

            const newParamsString = params.toString();
            const newURL = newParamsString ? `${window.location.pathname}?${newParamsString}` : window.location.pathname;
            history.pushState(null, '', newURL);

            await loadPosts({
                pageNum: -1,
                searchName: keyword,
                perPage: keyword.length !== 0 ? -1 : perPage,
                category,
            });
            
        }
    }

    async function loadPosts(options) {
        
        params.set('target', 'posts');
        params.set('page_num', options?.pageNum ? options.pageNum : params.get('page_num') ? params.get('page_num') : 1);
        params.set('per_page', options?.perPage ? options.perPage : params.get('per_page') ? params.get('per_page') : 1);

        if(options?.searchName) params.set('search_name', options?.searchName );
        if(options?.category) params.set('category', options.category );
        
        try {
            postLoadingEl.innerHTML = 'Carregando...';

            let response = await fetch(`/api?${params.toString()}`);
            response = await response.json();

            setPostDiv(response.articles);
            makePagination(response.pagination, response.articles.length);

        } catch(err) {
            console.error(err);
            postLoadingEl.innerHTML = 'Ocorreu um erro.';
        }
  
    }

    function setPostDiv(posts) {
        const postEntryEl = document.querySelector('.post-entry');
        postLoadingEl.innerHTML = '';
        postEntryEl.innerHTML = '';

        posts?.forEach((post) => {
            const divPostEl = document.createElement('div');
            const titleEl = document.createElement('h3');
            const contentEl = document.createElement('div');
            const thumbnailEl = document.createElement('div');
            const dateEL = document.createElement('p');

            divPostEl.classList.add('post');
            contentEl.classList.add('post-content');
            dateEL.classList.add('post-date');

            titleEl.innerHTML = post?.title;
            contentEl.innerHTML = post?.excerpt;
            dateEL.innerHTML = post?.date;
            thumbnailEl.innerHTML = post?.thumbnail;
            
            divPostEl.appendChild(titleEl);
            divPostEl.appendChild(thumbnailEl);
            divPostEl.appendChild(dateEL);
            divPostEl.appendChild(contentEl);
            divPostEl.innerHTML = `<a href="${post?.permalink}">${divPostEl.innerHTML}</a>`;

            postEntryEl.appendChild(divPostEl);
        });
        
    }

    async function makePagination(pagination, length) {
        const paginationEl = document.querySelector('#content_posts .pagination');

        if(pagination?.per_page !== -1) {
            paginationEl.innerHTML = '';
            for(let i = 1; i <= pagination?.length; i++) {
                const pageItem = document.createElement('a');
                pageItem.innerText = i;
                pageItem.href = "javascript:void(1);";
                pageItem.classList.add('page');
                
                paginationEl.appendChild(pageItem);
            }
        }

        makePageLink(paginationEl, length);
        
    }

    function makePageLink(paginationEl, length) {
        // make page link
        const paginationLinksEl = paginationEl.querySelectorAll('a.page');
        paginationLinksEl.forEach(async (link) => {
            link.addEventListener('click', async() => {
                params.set('per_page', link.innerText);
                const newParamsString = params.toString();

                await loadPosts({ pageNum: link.innerText });
            });
        });

        const paginationCountEl = document.createElement('div');
        paginationCountEl.innerText = `Total: ${length}`;

        paginationEl.appendChild(paginationCountEl)

    }
    
    document.addEventListener('DOMContentLoaded', async () => {
        loadCategories();
        await loadPosts();
        search();
    });
</script>

<style>
    #content_posts {
        
    }

    #content_posts .pagination {
        display: flex;
        flex-direction: row;
    }
    #content_posts .pagination div {
        margin-left: 6px;

    }

    #content_posts #posts {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    #content_posts .post-entry {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 1rem;
    }

    #content_posts .post-entry .post {
        width: 200px;
    }

    #content_posts .post-entry .post img {
        width: 200px;
        height: 150px;
        object-fit: cover;
    }

    #content_posts .post-content {
        display: -webkit-box;
        overflow: hidden;
        text-overflow: ellipsis;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
</style>
