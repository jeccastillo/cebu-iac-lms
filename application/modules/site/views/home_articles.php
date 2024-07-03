<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<script>
new Vue({
    el: "#article-section",
    data: {
        articles: [],
    },

    mounted() {
        axios
            .get(
                api_url_article + "external-news?count_content=3"
            )
            .then((data) => {
                this.articles = data.data.data;
            })
            .catch((error) => {
                console.log(error);
            });
    },

    methods: {
        replaceThis: function(str, width) {
            if (str.length < width) {
                return str.substring(0, width);
            } else {
                return str.substring(0, width) + "...";
            }
        },
    },
});