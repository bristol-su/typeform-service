<template>
    <div>
        <p-select
            id="oauth-login"
            v-model="dynamicValue"
            :select-options="loginsForSelect"
            null-label="-- No Logins --">

        </p-select>

        <div ref="tfLoginButton">
            <a :href="oauthUrl" class="tf-login-button" target="_blank">Log in to Typeform</a>
        </div>
    </div>
</template>

<script>
    import FormInputMixin from '@bristol-su/portal-ui-kit/src/components/atomic/dynamic-form/FormInputMixin';

    export default {
        name: "OAuthLogin",

        mixins: [ FormInputMixin ],

        data() {
            return {
                clientId: '',
                scope: 'offline+accounts:read+responses:read+webhooks:read+webhooks:write+forms:read',
                state: '12345',
                redirect_uri: '/_connector/typeform/redirect',
                authTokens: [],
                loadingCodes: false,
                intervalId: null,
            }
        },

        created() {
            this.clientId = this.$tools.utils.WindowAccessor.get('typeform_client_id');
            this.loadCodes();
        },

        mounted() {
            this.intervalId = window.setInterval(() => {
                this.loadCodes();
            }, 2500)
        },

        methods: {
            loadCodes() {
                if(this.loadingCodes === false) {
                    this.loadingCodes = true;
                    return this.$httpBasic.get('_connector/typeform/code')
                        .then(response => {
                            let newLogin = this.isNewLogin(response.data);
                            this.authTokens = response.data;
                            if(newLogin !== false) {
                                this.setValue(newLogin.id)
                            }
                        })
                        .catch(error => this.$notify.alert('Could not load logins'))
                        .then(() => this.loadingCodes = false);
                }
            },

            isNewLogin(newValue) {
                let currentIds = this.authTokens.map(token => token.id);
                if(Array.isArray(newValue)) {
                    for(let val of newValue) {
                        if(val.hasOwnProperty('id')) {
                            if(currentIds.indexOf(val.id) === -1) {
                                return val;
                            }
                        }
                    }
                }
                return false;
            }
        },

        computed: {
            oauthUrl() {
                return "https://api.typeform.com/oauth/authorize?client_id="
                    + this.clientId
                    + "&scope="
                    + this.scope
                    + "&redirect_uri="
                    + this.$tools.routes.basic.baseWebUrl() + this.redirect_uri
                    + "&state=" + this.state;
            },
            loginsForSelect() {
                return this.authTokens.map(t => {
                    return { id: t.id, value: t.created_at };
                })
            },
        },

        destroyed() {
            window.clearInterval(this.intervalId)
        }
    }
</script>

<style scoped>
    .tf-login-button {
        text-decoration: none;
        background-color: #262627;
        border: 0;
        border-radius: 2px;
        color: #fff;
        display: inline-block;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        line-height: 24px;
        padding: 8px 16px;
        transition: 0.2s;
        white-space: nowrap;
        --webkit-font-smoothing: antialiased;
    }

    .tf-login-button > a:hover {
        opacity: .8;
    }

    .tf-login-button > a:visited {
        color: #fff;
    }
</style>
