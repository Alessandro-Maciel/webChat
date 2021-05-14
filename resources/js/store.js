import axios from 'axios';
import { createStore } from 'vuex';
import CreatePersistedState from 'vuex-persistedstate';

export const store = createStore({
    state: {
        user: {}
    },
    mutations: {
        setUserState: (state, value) => state.user = value
    },
    actions: {
        userStateAction: async ({ commit }) => {

            await axios.get('api/user/me').then((Response) => {

                const userResponse = Response.data.me
                commit('setUserState', userResponse);

            });

        }
    },
    plugins: [CreatePersistedState()]
})




