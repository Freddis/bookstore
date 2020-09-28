<template>
    <div class="page-catalogue">
        <h1>Catalogue</h1>
        <nav v-if="products.length > 0">
            <ul class="pagination">
                <li :class="'page-item' + (prev ? '' : ' disabled')"><a class="page-link" href="#" v-on:click.prevent="setPage(prev)">Previous</a></li>
                <li class="page-item disabled">
                    <span class="page-link">{{currentPage}} of {{lastPage}}</span>
                </li>
                <li :class="'page-item' + (next ? '' : ' disabled')" class="page-item"><a class="page-link" href="#" v-on:click.prevent="setPage(next)">Next</a></li>
            </ul>
        </nav>
        <div v-if="!error" class="products row">
            <div v-for="item in products" class="col-4">
                <div class="card">
                    <img :src="'/'+item.image" class="card-img-top" alt="">
                    <div class="card-body">
                        <h5 class="card-title">{{item.title}}</h5>
                        <p class="card-text">{{item.description}}</p>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="error" class="alert alert-danger">{{error}}</div>
    </div>
</template>

<script>
    export default {
        name: "catalogue",
        data: function () {
            return {
                csrf: window.CSRF_TOKEN,
                error: null,
                products: [],
                next: null,
                prev: null,
                currentPage: 1,
                lastPage: null,
            }
        },
        methods: {
            setPage(url) {
                if (url == null) {
                    return;
                }
                this.loadPage(url);
            },
            loadPage(url) {
                const _this = this;
                const options = {
                    method: 'post',
                    url: url,
                    data: {id: this.jobId},
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    }
                };
                axios(options)
                    .then(function (e) {
                        console.log('successfull response', e);
                        if (e.status != 200) {
                            _this.error = "Response status " + e.status;
                            return;
                        }
                        _this.products = e.data.data;
                        _this.prev = e.data.prev_page_url;
                        _this.lastPage = e.data.last_page;
                        const current = e.data.current_page;

                        if(_this.products.length == 0 && current != 1)
                        {
                            _this.error = "Page doesn't exist";
                            return;
                        }

                        _this.next = e.data.next_page_url;
                        if(current != _this.currentPage)
                        {
                            _this.$router.push('/catalogue/' + _this.currentPage);
                        }
                        _this.currentPage = current;


                    })
                    .catch(function (e) {
                        console.log("error exception", e);
                        _this.error = "Response status " + e;
                    });
            }
        },
        mounted() {
            console.log("Mounted");
            this.currentPage = this.$route.params.page ? this.$route.params.page : 1;
            this.loadPage('/catalogue/getProducts?page=' + this.currentPage);
        },
    }
</script>