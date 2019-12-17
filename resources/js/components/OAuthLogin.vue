<template>
    <div>
        <b-row>
            <b-col sm="12" md="8">
                
                <label class="sr-only" for="auth-token-id">Login</label>
                <b-input-group>
                    <b-input-group-prepend>
                        <b-button @click="loadCodes" variant="outline-info" size="sm"><i :class="spinClasses" class="fa fa-refresh"/></b-button>
                    </b-input-group-prepend>

                    <b-form-select id="auth-token-id" v-model="value" :options="loginOptions" >
                        <template v-slot:first>
                            <option :value="null" disabled>-- No recent logins --</option>
                        </template>
                    </b-form-select>
                </b-input-group>
                    
            </b-col>
            <b-col sm="12" md="4" style="text-align: right; ">
                <div ref="tfLoginButton">
                    <a :href="oauthUrl" class="tf-login-button" target="_blank">Log in to Typeform</a>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import { abstractField } from 'vue-form-generator';
    
    export default {
        name: "OAuthLogin",

        mixins: [ abstractField ],
        
        props: {},

        data() {
            return {
                clientId: 'DoKsNjjrmTVNskHBBoCheX3DV9EeZtjxK8g6rwRZgP3t',
                scope: 'offline+accounts:read+responses:read',
                state: '12345',
                redirect_uri: 'https://portal.local/_connector/typeform/redirect',
                code_uri: '/api/_connector/typeform/code',
                authTokens: [],
                loadingCodes: false
            }
        },

        created() {
            this.loadCodes();
        },

        mounted() {
            window.setInterval(() => {
                this.loadCodes();
            }, 2500)
        },
        
        methods: {
            loadCodes() {
                this.loadingCodes = true;
                return this.$http.get(this.code_uri)
                    .then(response => {
                        let newLogin = this.isNewLogin(response.data);
                        this.authTokens = response.data;
                        if(newLogin !== false) {
                            this.value = newLogin.id
                        }
                    })
                    .catch(error => this.$notify.alert('Could not load logins'))
                    .then(() => this.loadingCodes = false);
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
                    + this.redirect_uri
                    + "&state=" + this.state;
            },
            loginOptions() {
                return this.authTokens.map(token => {
                    return {value: token.id, text: 'Login at ' + token.created_at}
                });
            },
            spinClasses() {
                return (this.loadingCodes?'fa-spin':'');
            }
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
