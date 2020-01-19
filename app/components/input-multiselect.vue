<template>
    <ul class="uk-nav uk-nav-side">
        <li v-for="value in selectOptions" @click.prevent="toggle(value)">
            <a href="#" class="uk-flex uk-flex-middle uk-flex-space-between" style="display: flex !important;">
                <span class="uk-flex-item-auto uk-text-truncate">{{ $key }}</span>
                <span class="uk-float-right" :class="{'pk-icon-check': isSelected(value)}"></span>
            </a>
        </li>
    </ul>
</template>
<script>
    module.exports = {
        props: {
            value: Array,
            options: [Array, Object]
        },
        data: () => ({
            selected: [],
        }),
        computed: {
            selectOptions() {
                if (_.isArray(this.options)) {
                    let options = {};
                    this.options.forEach(option => {
                        options[option.title] = option.value
                    });

                    return options;
                }

                return this.options;
            }
        },
        created() {
            this.selected = this.value;
            this.$watch('selected', this.setValue);
        },
        methods: {
            toggle(value) {
                if (this.isSelected(value)) {
                    this.selected.$remove(value);
                } else {
                    this.selected.push(value);
                }
            },
            isSelected(value) {
                return this.selected.indexOf(value) > -1;
            },
            setValue() {
                this.value = [];

                _.forIn(this.selectOptions, (value) => { // keep values in order
                    if (this.isSelected(value)) {
                        this.value.push(value);
                    }
                });
            }
        }
    };

    Vue.component('input-multiselect', function (resolve, reject) {
        resolve(module.exports)
    });
</script>