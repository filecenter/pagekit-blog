<?php $view->script('editor') ?>

<form id="category" class="uk-form" v-validator="form" @submit.prevent="save | valid" v-cloak>
    <div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
        <div data-uk-margin>
            <h2 class="uk-margin-remove" v-if="category.id">{{ 'Edit Category' | trans }}</h2>
            <h2 class="uk-margin-remove" v-else>{{ 'Add Category' | trans }}</h2>
        </div>
        <div data-uk-margin>
            <a class="uk-button uk-button-primary" v-if="category.id" :href="$url.route('admin/blog/category')">{{ 'Add Category' | trans }}</a>
            <button class="uk-button uk-button-primary" type="submit">{{ 'Save' | trans }}</button>
            <a class="uk-button uk-margin-small-right" :href="$url.route('admin/blog/categories')">{{ category.id ? 'Close' : 'Cancel' | trans }}</a>
        </div>
    </div>
    <div class="uk-grid pk-grid-large pk-width-sidebar-large uk-form-stacked" data-uk-grid-margin>
        <div class="pk-width-content">
            <div class="uk-form-row">
                <label for="form-title" class="uk-form-label">{{ 'Category Title' | trans }}</label>
                <div class="uk-form-controls">
                    <input id="form-title" class="uk-width-1-1" type="text" name="title" :placeholder="'Enter Title' | trans" v-model="category.title" v-validate:required>
                    <p class="uk-form-help-block uk-text-danger" v-show="form.title.invalid">{{ 'Title cannot be blank.' | trans }}</p>
                </div>
            </div>
            <div class="uk-form-row"></div>
            <div class="uk-form-row">
                <label for="form-desc" class="uk-form-label">{{ 'Description' | trans }}</label>
                <div class="uk-form-controls">
                    <v-editor id="Category-content" :value.sync="category.description" :options="{markdown: false}"></v-editor>
                </div>
            </div>
        </div>
        <div class="pk-width-sidebar">
            <div class="uk-panel">
                <div class="uk-form-row">
                    <label for="form-slug" class="uk-form-label">{{ 'Slug' | trans }}</label>
                    <div class="uk-form-controls">
                        <input id="form-slug" class="uk-width-1-1" type="text" v-model="category.slug">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    Vue.ready({
        el: '#category',
        data: function () {
            return {
                data: window.$data,
                category: window.$data.category || {}
            }
        },
        created: function () {
            this.resource = this.$resource('api/blog/category{/id}');
        },
        methods: {
            save: function () {
                var data = {category: this.category, id: this.category.id};

                this.$broadcast('save', data);
                this.resource.save({id: this.category.id}, data).then(function (res) {
                    if ( ! this.category.id) {
                        window.history.replaceState({}, '', this.$url.route('admin/blog/category', {id: res.data.category.id}))
                    }

                    this.$notify(this.category.id ? 'Category saved' : 'Category created');
                    this.$set('category', res.data.category);
                }, function (res) {
                    this.$notify(res.data, 'danger');
                })
            }
        }
    });
</script>