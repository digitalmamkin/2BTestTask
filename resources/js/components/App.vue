<template>
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Blogs Posts Filters</div>
                    <div class="card-body">

                        <div class="filters-box">
                            <div>
                                <div>Blog</div>
                                <select v-model="blog">
                                    <option value="all" selected>All</option>
                                    <option v-for="blog in blogs" :value="blog.id">{{blog.title}}</option>
                                </select>
                            </div>

                            <div>
                                <div>From date</div>
                                <input type="date" v-model="date_from">
                            </div>

                            <div>
                                <div>To date</div>
                                <input type="date" v-model="date_to">
                            </div>

                            <div>
                                <div>Read time</div>
                                <select v-model="time">
                                    <option value="all" selected>All</option>
                                    <option v-for="time in times" :value="time.time">{{time.time}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div v-for="post in posts" class="post">
                                <div class="post-info-box mb-4">
                                    <div>
                                        <div>Title: <b>{{post.title}}</b></div>
                                        <div>Original URL: <b><a :href="post.url" target="_blank">{{post.url}}</a></b></div>
                                    </div>
                                    <div>
                                        <div>Read time: <b>{{post.time}} min</b></div>
                                        <div>Post date: <b>{{post.created_at}}</b></div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button v-if="!post.show" @click="post.show = true" class="btn btn-sm btn-primary">Read</button>
                                    <button v-if="post.show" @click="post.show = false" class="btn btn-sm btn-warning">Hide</button>
                                </div>
                                <div>
                                    <div v-if="post.show" v-html="post.content"></div>
                                </div>
                            </div>

                            <div v-if="posts.length > 0" class="text-center">
                                <button class="btn btn-success" @click="getPosts()">Load more...</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "App",
        data: () => {
            return {
                times: [],
                time: 'all',
                blogs: [],
                blog: 'all',
                date_from: '',
                date_to: '',
                posts: []
            }
        },
        methods: {
            async getBlogsLists(){
                this.blogs = []
                const response = await axios.get('/getBlogsList')

                if(response.data.status === 200){
                    this.blogs = response.data.result
                }
            },
            async getReadTimeList(){
                const response = await axios.get('/getTimeList')

                if(response.data.status === 200){
                    this.times = response.data.result
                }
            },
            async getPosts(){
                const filter = {
                    blog: this.blog,
                    time: this.time,
                    date_from: this.date_from,
                    date_to: this.date_to,
                    length: this.posts.length
                }

                const response = await axios.get(`/getPostsList?filter=${JSON.stringify(filter)}`)

                if(response.data.status === 200){
                    response.data.result.forEach((post) => {
                        this.posts.push({
                            id: post.id,
                            url: post.url,
                            title: post.title,
                            time: post.read_time,
                            content: post.content,
                            created_at: post.post_date,
                            show: false
                        })
                    })
                }
            }
        },
        watch: {
            blog(){
                this.getPosts()
            },
            time(){
                this.getPosts()
            },
            date_from(){
                this.getPosts()
            },
            date_to(){
                this.getPosts()
            }
        },
        mounted() {
            this.getBlogsLists()
            this.getReadTimeList()
            this.getPosts()
        }
    }
</script>

<style lang="scss">
.filters-box{
    display: flex;
    gap: 30px;
    justify-content: center;
    align-items: center;
}

.post{
    margin-bottom: 20px;
    background: aliceblue;
    padding: 15px;
    border-radius: 10px;
}

.post-info-box{
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
}

img{
    max-width: 100%;
}
</style>
