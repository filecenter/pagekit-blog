<template>
    <div>
        <div class="uk-flex uk-flex-wrap" data-uk-margin="">
            <div v-for="tag in tags" class="uk-badge uk-margin-small-right uk-margin-small-bottom" track-by="$index">
                <a class="uk-float-right uk-close" @click.prevent="removeTag(tag)"></a>
                <span>{{ tag }}</span>
            </div>
        </div>
        <div class="uk-flex uk-flex-middle uk-margin">
            <div v-if="existing.length">
                <div class="uk-position-relative" data-uk-dropdown="{mode:'click'}">
                    <button type="button" class="uk-button uk-button-small">{{ 'Select' | trans }}</button>
                    <div class="uk-dropdown uk-dropdown-scrollable uk-dropdown-small">
                        <ul class="uk-nav uk-nav-dropdown">
                            <li v-for="tag in existing">
                                <a v-show="!selected(tag)" @click.prevent="addTag(tag)">{{ tag }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div v-if="!readOnly" class="uk-flex-item-1 uk-margin-small-left">
                <div class="uk-form-password">
                    <input type="text" class="uk-width-1-1" v-model="newtag">
                    <a class="uk-form-password-toggle" @click.prevent="addTag()">
                        <i class="uk-icon-check uk-icon-hover"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            tags: Array,
            existing: {type: Array, default: () => ([])},
            readOnly: {type: Boolean, default: false}
        },
        data: () => ({
            newtag: '',
        }),
        methods: {
            addTag(tag) {
                tag = tag || (this.readOnly ? '' : this.newtag);
                if (!tag || this.selected(tag)) {
                    return;
                }
                this.tags.push(tag);
                if (this.style === 'tags') {
                    this.$nextTick(function () {
                        UIkit.$html.trigger('resize'); //todo why no check.display or changed.dom???
                    });
                }
                this.newtag = '';
            },

            removeTag(tag) {
                this.tags.$remove(tag)
            },

            selected(tag) {
                return this.tags.indexOf(tag) > -1;
            }
        }
    };

    Vue.component('input-tags', function (resolve, reject) {
        resolve(module.exports)
    });
</script>
