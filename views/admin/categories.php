<div id="categories" class="uk-form" v-cloak>
    <div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
        <div class="uk-flex uk-flex-middle uk-flex-wrap" data-uk-margin>
            <h2 class="uk-margin-remove" v-if="!selected.length">{{ '{0} %count% Categories|{1} %count% Categories|]1,Inf[ %count% Categories' | transChoice count {count:count} }}</h2>
            <template v-else>
                <h2 class="uk-margin-remove">{{ '{1} %count% Categories selected|]1,Inf[ %count% Categories selected' | transChoice selected.length {count:selected.length} }}</h2>
                <div class="uk-margin-left" >
                    <ul class="uk-subnav pk-subnav-icon">
                        <li><a class="pk-icon-delete pk-icon-hover" title="Delete" data-uk-tooltip="{delay: 500}" @click="remove" v-confirm="'Delete categories?'"></a></li>
                    </ul>
                </div>
            </template>
            <div class="pk-search">
                <div class="uk-search">
                    <input class="uk-search-field" type="text" v-model="config.filter.search" debounce="300">
                </div>
            </div>
        </div>
        <div data-uk-margin>
            <a class="uk-button uk-button-primary" :href="$url.route('admin/blog/category')">{{ 'Add category' | trans }}</a>
        </div>
    </div>
    <div class="uk-overflow-container">
        <table class="uk-table uk-table-hover uk-table-middle">
            <thead>
            <tr>
                <th class="pk-table-width-minimum"><input type="checkbox" v-check-all:selected.literal="input[name=id]" number></th>
                <th class="pk-table-min-width-200" v-order:title="config.filter.order">{{ 'Title' | trans }}</th>
                <th class="pk-table-min-width-150">{{ 'Description' | trans }}</th>
                <th class="pk-table-width-150" v-order:date="config.filter.order">{{ 'Date' | trans }}</th>
                <th class="pk-table-width-200 pk-table-min-width-200">{{ 'URL' | trans }}</th>
            </tr>
            </thead>
            <tbody>
            <tr class="check-item" v-for="category in categories" :class="{'uk-active': active(category)}">
                <td><input type="checkbox" name="id" :value="category.id"></td>
                <td>
                    <a :href="$url.route('admin/blog/category', {id: category.id})">{{ category.title }}</a>
                </td>
                <td>{{{ category.description }}}</td>
                <td>{{ category.created_at | date }}</td>
                <td class="pk-table-text-break">
                    <a target="_blank" v-if="category.url" :href="this.$url.route(category.url.substr(1))">{{ decodeURI(category.url) }}</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <h3 class="uk-h1 uk-text-muted uk-text-center" v-show="categories && !categories.length">{{ 'No categories found.' | trans }}</h3>
    <v-pagination :page.sync="config.page" :pages="pages" v-show="pages > 1 || page > 0"></v-pagination>
</div>
<script>
    Vue.ready({
        el: '#categories',
        data: function() {
            return _.merge({
                categories: [],
                config: {
                    filter: this.$session.get('categories.filter', {order: 'title asc', limit: 25})
                },
                pages: 0,
                count: 0,
                selected: []
            }, window.$data);
        },
        ready: function () {
            this.resource = this.$resource('api/blog/category{/id}');
            this.$watch('config.page', this.load, {immediate: true});
        },
        watch: {
            'config.filter': {
                handler: function (filter) {
                    if (this.config.page) {
                        this.config.page = 0;
                    } else {
                        this.load();
                    }

                    this.$session.set('categories.filter', filter);
                },
                deep: true
            }
        },
        methods: {
            active: function (category) {
                return this.selected.indexOf(category.id) !== -1;
            },
            remove: function() {
                this.resource.delete({id: 'bulk'}, {ids: this.selected}).then(function () {
                    this.load();
                    this.$notify('Categories successful deleted');
                });
            },
            load: function () {
                this.resource.query({ filter: this.config.filter, page: this.config.page }).then(function (res) {
                    this.$set('categories', res.data.categories || []);
                    this.$set('pages', res.data.pages || 0);
                    this.$set('count', res.data.count || 0);
                    this.$set('selected', []);
                })
            },
            getSelected: function () {
                return this.categories.filter(function (category) {
                    return this.selected.indexOf(category.id) !== -1;
                }, this)
            }
        }
    });
</script>