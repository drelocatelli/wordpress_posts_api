<div class="secao">
    <div class="container">
        <h5 class="title-noticia" style="margin-top: 55px;">Notícias</h5>
        <div class="content_posts">
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

    </div>
</div>
<script>
    postLoadingEl = document.querySelector('.post-loading');
    params = new URLSearchParams(window.location.search);
    let perPage = 5;

    async function loadPosts(options) {
        
        console.log(params.get('category'))
        params.set('target', 'posts');
        params.set('page_num', options?.pageNum ? options.pageNum : params.get('page_num') ? params.get('page_num') : 1);
        params.set('per_page', options?.perPage ? options.perPage : params.get('per_page') ? params.get('per_page') : perPage);

        if(options?.searchName) params.set('search_name', options?.searchName );
        if(options?.category) { 
            params.set('category', options.category );
        }
        // document.querySelector('category')?.innerHTML = params.get('category') ?? 'sem categoria';
        
        try {
            postLoadingEl.innerHTML = 'Carregando...';

            let response = await fetch(`<?= site_url(); ?>/api?${params.toString()}`);
            response = await response.json();

            setPostDiv(response.articles);
            makePagination(response.pagination, response.articles.length);

            const newParamsString = params.toString();
            const newURL = newParamsString ? `${window.location.pathname}?${newParamsString}` : window.location.pathname;
            history.pushState(null, '', newURL);

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
            dateEL.classList.add('post-date');
            contentEl.classList.add('post-content');

            const postTitleEntry = document.createElement('div');
            postTitleEntry.classList.add('post-header');
            titleEl.innerHTML = post?.title;
            titleEl.classList.add('post-title');
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
            contentEl.innerHTML = post?.excerpt;
            thumbnailEl.innerHTML = post?.thumbnail;

            postTitleEntry.appendChild(titleEl)
            postTitleEntry.appendChild(dateEL)
            
            divPostEl.appendChild(postTitleEntry);
            divPostEl.appendChild(thumbnailEl);
            divPostEl.appendChild(contentEl);
            divPostEl.innerHTML = `<a href="${post?.permalink}">${divPostEl.innerHTML}</a>`;

            postEntryEl.appendChild(divPostEl);
        });
        
    }

        async function makePagination(pagination, length) {
        const paginationEl = document.querySelector('.content_posts .pagination');
        paginationEl.innerHTML = '';

        const totalPages = Math.ceil(pagination?.total_posts / pagination?.per_page) ?? 0;
        const currentPage = Number(pagination.current_page);

        if (totalPages === 0) {
            postLoadingEl.innerHTML = 'Nenhum post encontrado';
        } else {
            postLoadingEl.innerHTML = '';
        }

        const visiblePages = 5; // Define quantas páginas visíveis queremos mostrar

        let startPage = currentPage - Math.floor(visiblePages / 2);
        startPage = Math.max(startPage, 1); // Garante que startPage não seja menor que 1

        let endPage = startPage + visiblePages - 1;
        endPage = Math.min(endPage, totalPages); // Garante que endPage não seja maior que totalPages

        if (endPage - startPage < visiblePages - 1) {
            startPage = Math.max(1, endPage - visiblePages + 1);
        }

        if (currentPage > 1) {
            if(currentPage > 2) {
                const firstPageItem = document.createElement('a');
                firstPageItem.innerText = '<<';
                firstPageItem.dataset.pageNum = 1;
                firstPageItem.href = "javascript:void(0);";
                firstPageItem.classList.add('page');
                paginationEl.appendChild(firstPageItem);
            }


            const prevPageItem = document.createElement('a');
            prevPageItem.innerText = '<';
            prevPageItem.dataset.pageNum = currentPage - 1;
            prevPageItem.href = "javascript:void(0);";
            prevPageItem.classList.add('page');
            paginationEl.appendChild(prevPageItem);

        }

        for (let i = startPage; i <= endPage; i++) {
            const pageItem = document.createElement('a');
            pageItem.innerText = i;
            pageItem.dataset.pageNum = i;
            pageItem.href = "javascript:void(0);";
            pageItem.classList.add('page');

            if (i === currentPage) {
                pageItem.classList.add('active');
            }

            paginationEl.appendChild(pageItem);
        }

        if (currentPage < totalPages) {
            const nextPageItem = document.createElement('a');
            nextPageItem.href = "javascript:void(0);";
            nextPageItem.classList.add('page');
            nextPageItem.innerHTML = '>';
            nextPageItem.dataset.pageNum = currentPage + 1;
            paginationEl.appendChild(nextPageItem);

            if(currentPage < totalPages) {
                const lastPageItem = document.createElement('a');
                lastPageItem.innerText = '>>';
                lastPageItem.dataset.pageNum = totalPages;
                lastPageItem.href = "javascript:void(0);";
                lastPageItem.classList.add('page');
                paginationEl.appendChild(lastPageItem);
            }
        }

        makePageLink(paginationEl, length);
    }


    function makePageLink(paginationEl, length) {
        // make page link
        const paginationLinksEl = paginationEl.querySelectorAll('a.page');
        paginationLinksEl.forEach(async (link) => {
            link.addEventListener('click', async() => {
                params.set('per_page', params.get('per_page'));
                const newParamsString = params.toString();

                console.log('link', link.dataset.pageNum)
                await loadPosts({ pageNum: link.dataset.pageNum });
            });
        });

        const paginationCountEl = document.createElement('div');
        paginationCountEl.innerText = `Total: ${length}`;

        paginationEl.appendChild(paginationCountEl)

    }
    
    document.addEventListener('DOMContentLoaded', async () => {
        await loadPosts();
        search();
    });
</script>

<style>
    .pagination a {
        background: #cdcdcd;
        color: #fff;
        padding: 5px 16px;
        border-radius: 5px;
    }

    .pagination a:hover {
        opacity: 0.8;
    }

    .pagination a.active {
        background: #20745f;

    }

    .pagination {
        flex-wrap: wrap;
    }
</style>
