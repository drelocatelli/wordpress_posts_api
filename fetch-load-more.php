<div class="container">
    <h5 class="title-noticia" style="margin-top: 25px;"><?= $search_title;  ?> </h5>
    <div id="content_posts">
        <form id="get_posts" method="get">
            <input type="search" name="keyword" placeholder="Palavras-Chave">
            <select name="category">
                <option value="">Aguarde...</option>
            </select>
            <button type="submit">Buscar</button>
        </form>
        <div id="posts">
            <div class="post-loading">
                Carregando...
            </div>
            <div class="post-entry">
            </div>
            <div class="pagination">
                
            </div>
        </div>
    </div>
</div>

<script>
    const form = document.querySelector('form#get_posts');
    const postLoadingEl = document.querySelector('.post-loading');
    const perPage = 8;
    const params = new URLSearchParams(window.location.search);
    const postEntryEl = document.querySelector('.post-entry');
    let currentCategory = '<?= $search_name;  ?>'

    function loadCategories() {
        const categoryIn = document.querySelector('select[name="category"]');
        fetch('<?= site_url(); ?>/api?target=categories')
            .then((response) => response.json())
            .then((response) => {
                categoryIn.innerHTML = '';
                response.forEach((category) => {
                    const option = new Option(category.name, category.slug);
                    if(category?.slug == params.get('category')) {
                        option.selected = true;
                    }
                    categoryIn.add(option);
                })
            })
            .catch((err) => {
                categoryIn.querySelector('option').innerText = 'Ocorreu um erro';
                console.error(err);
            })
    }
    
    async function search(e) {
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
                searchName: keyword,
                perPage: keyword.length !== 0 ? -1 : perPage,
                category,
            });
            
        }
    }

    async function loadPosts(options) {
        
        params.set('target', 'posts');
        params.set('page_num', params.get('page_num') == '-1' ? '1' : Number(params.get('page_num')) + 1);
        params.set('per_page', options?.perPage ? options.perPage : params.get('per_page') ? params.get('per_page') : perPage);

        if(options?.searchName) params.set('search_name', options?.searchName );
        if(options?.category) {
            params.set('category', options.category );
        } else {
            params.set('category', params.get('category') ?? currentCategory);
        }
        
        try {
            postLoadingEl.style.display = 'block';
            postLoadingEl.innerHTML = 'Carregando...';

            let response = await fetch(`<?= site_url(); ?>/api?${params.toString()}`);
            response = await response.json();
            
            let append = true;
            
            if(params.get('category') !== currentCategory) {
                currentCategory = params.get('category');
                params.set('page_num', '-1');
                append = false;
            }
            postLoadingEl.style.display = 'none';

            setPostDiv(response.articles, append);
            makePagination(response.pagination, response.articles.length);

            const newParamsString = params.toString();
            const newURL = newParamsString ? `${window.location.pathname}?${newParamsString}` : window.location.pathname;
            history.pushState(null, '', newURL);

        } catch(err) {
            console.error(err);
            postLoadingEl.innerHTML = 'Ocorreu um erro.';
        }
  
    }

    function setPostDiv(posts, append) {
        postLoadingEl.innerHTML = '';
        
        if(!append) {
            postEntryEl.innerHTML = '';
        } 

        // add post on dom
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
            let date = new Date(post?.date);
            date = new Date(date.getTime() - (3 * 60 * 60 * 1000));
            date = date.toLocaleString('pt-BR', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false,
                timeZone: 'America/Sao_Paulo' // Brasília Time (BRT)
            });

            dateEL.innerHTML = date;
            divPostEl.appendChild(contentEl);
            divPostEl.innerHTML = `<a href="${post?.permalink}">${divPostEl.innerHTML}</a>`;

            postEntryEl.appendChild(divPostEl);
        });
        
    }

    async function makePagination(pagination, length) {
        const paginationEl = document.querySelector('#content_posts .pagination');
        paginationEl.innerHTML = '';

        const loadMoreEl = document.createElement('a');
        loadMoreEl.href = 'javascript:void(0);';
        const loadMoreHTML ='<i class="fa fa-plus-circle"></i>&nbsp;&nbsp;Carregar mais'
        loadMoreEl.innerHTML = loadMoreHTML;
        const loadMoreStyle =  `
            background-color: #FFF;
            border: 1px solid #E1E1E1;
            color: #666;
            font-weight: bold;
            position: relative;
            display: table;
            margin: 0 auto;
            padding: 15px 30px;`
        loadMoreEl.style.cssText = loadMoreStyle;

        loadMoreEl.onclick = async () => {
            loadMoreEl.innerHTML = '<p style="text-align:center;"><br /><br /><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br /><br /></p>';
            loadMoreEl.style.border = 'unset';
            loadMoreEl.style.margin = '0 auto';
            loadMoreEl.style.padding = 'unset';
            await loadPosts({ pageNum: Number(params.get('page_num')) + 1 });
            loadMoreEl.style.cssText = loadMoreStyle;
            loadMoreEl.innerHTML = loadMoreHTML;

        }
        
        paginationEl.appendChild(loadMoreEl);
    }

    function makePageLink(paginationEl, length) {
        // make page link
        const paginationLinksEl = paginationEl.querySelectorAll('a.page');
        paginationLinksEl.forEach(async (link) => {
            link.addEventListener('click', async() => {
                params.set('per_page', params.get('per_page'));
                const newParamsString = params.toString();

                await loadPosts({ pageNum: link.innerText });
            });
        });

        const paginationCountEl = document.createElement('div');
        paginationCountEl.innerText = `Total: ${length}`;

        paginationEl.appendChild(paginationCountEl)

    }

    function preventDuplicatedPostOnSearchButton() {
        const select = form.querySelector('select');
        // prevent duplicated posts
        console.log(select.value, params.get('category'))
        if(select.value === params.get('category')) {
            form.querySelector('button[type="submit"]').disabled = true;
        }
        select.onchange = (e) => {
            const {value} = e.target;
            if(value === params.get('category')) {
                form.querySelector('button[type="submit"]').disabled = true;
            } else {
                form.querySelector('button[type="submit"]').disabled = false;

            }
        }
    }
    
    document.addEventListener('DOMContentLoaded', async () => {
        loadCategories();
        await loadPosts();
        search();
        preventDuplicatedPostOnSearchButton();        
    });
</script>


<style>
    #content_posts {
        
    }

    #get_posts input,
    #get_posts button,
    #get_posts select {
        padding: 0.8rem;
    }

    #get_posts select {
        text-transform: capitalize;
    }

    #get_posts button {
        border: none;
        background-color: #1F90BD;
        color: #fff;
        border-radius: 3px;
        padding: 0.8rem 1.5rem;
    }

    #get_posts input,
    #get_posts select {
        border-radius: 3px 0 0 3px;
        border: 1px solid #e8e2e2;
        outline: none;
        padding: 0.8rem;
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
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        width: 100%;
        gap: 8px;
        margin-top: 15px;
    }

    #content_posts .post-entry .post {
        width: 320px;
        display: table;
        /* width: 100%; */
        min-height: 205px;
        padding: 10px;
        background-color: #f0f0f0;
        color: #666;
        font-size: 15px;
    }

    #content_posts .post-entry a,  #content_posts .post-entry a h3  {
        color: inherit;
    }

    #content_posts .post-entry .post img {
        width: 100%;
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
