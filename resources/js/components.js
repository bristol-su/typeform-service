import OAuthLogin from './components/OAuthLogin';
import Toolkit from '@bristol-su/frontend-toolkit';
import Vue from 'vue';

Vue.use(Toolkit);

Vue.component('p-typeform-auth-code', OAuthLogin);
