<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="bg-white overflow-hidden shadow-xl sm:rounded-lg flex"
          style="min-height: 400px; max-height: 400px"
        >
          <!--list de usuários -->
          <div
            class="w-3/12 bg-gray-200 bg-opacity-25 border-r border-gray-200 overflow-y-scroll"
          >
            <ul class="">
              <li
                v-for="user in users"
                :key="user.id"
                @click="
                  () => {
                    loadMessages(user.id);
                  }
                "
                :class="
                  userActive && userActive.id == user.id
                    ? 'bg-gray-200 bg-opacity-50'
                    : ''
                "
                style="height: 70px"
                class="p-6 text-lg text-gray-600 leading-7 font-semibold border-b border-gray-200 hover:bg-opacity-50 hover:bg-gray-200 hover:cursor-pointer"
              >
                <div class="flex">
                  <div class="w-full">
                    <p class="">
                      {{ user.name }}
                    </p>
                  </div>

                  <div class="text-right">
                    <span
                      v-if="user.notification > 0"
                      class="bg-blue-500 rounded-full h-5 w-5 flex items-center justify-center text-white text-xs"
                      >{{ user.notification }}</span
                    >
                  </div>
                </div>
                <div v-if="user.typing == 0" class="text-xs text-blue-500">
                  <p class=""></p>
                </div>
                <div v-if="user.typing == 1" class="text-xs text-blue-500">
                  <p class="">Digitando...</p>
                </div>
              </li>
            </ul>
          </div>

          <!--box de conversa -->
          <div class="w-9/12 flex flex-col justify-between">
            <!--message -->

            <div class="w-full p-6 flex flex-col overflow-y-auto">
              <div
                v-for="message in messages"
                :key="message.id"
                :class="message.from == $attrs.auth.user.id ? 'text-right' : ''"
                class="w-full mb-3 message"
              >
                <p
                  :class="
                    message.from == $attrs.auth.user.id
                      ? 'messageFromME'
                      : 'messageToMe'
                  "
                  class="inline-block p-2 rounded-md"
                  style="max-width: 75%"
                >
                  {{ message.content }}
                </p>
                <span class="block mt-1 text-xs text-gray-500">{{
                  formatDate(message.created_at)
                }}</span>
              </div>

              <div v-if="userActive.typing == 1" class="w-full mt-5 message">
                <p
                  class="block mt-1 text-xs text-blue-500"
                  style="max-width: 75%"
                >
                  {{ userActive.name }} está digitando...
                </p>
              </div>
            </div>
            <div
              v-if="userActive"
              class="w-full bg-gray-200 bg-opacity-25 p-6 border-t border-gray-200"
            >
              <form v-on:submit.prevent="sedMessage">
                <div class="flex rounded-md overflow-hidden border border-gray">
                  <input
                    @keydown="typing"
                    v-model="message"
                    type="text"
                    class="flex-1 px-4 py-2 text-sm focus:outline-none border-none"
                    name=""
                    id=""
                  />
                  <button
                    type="submit"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2"
                  >
                    Enviar
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import moment from "moment";
import { store } from "../store";
import Vue from "vue";

export default {
  components: {
    AppLayout,
  },
  data() {
    return {
      users: [],
      idUserMe: "",
      nameUser: "",
      messages: [],
      userActive: 0,
      message: "",
      escrevendo: 0,
      typingUser: "",
    };
  },
  methods: {
    scrollToButton: function () {
      if (this.messages.length) {
        document.querySelectorAll(".message:last-child")[0].scrollIntoView();
      }
    },
    loadMessages: async function (userId) {
      const user = this.users.findIndex((user) => {
        if (user.id === userId) {
          return user;
        }
      });

      this.users[user].notification = 0;

      axios.get(`api/user/${userId}`).then((response) => {
        this.userActive = response.data.user;
        this.userActive.typing = 0;
      });

      await axios.get(`api/messages/${userId}`).then((response) => {
        this.messages = response.data.messages;
      });

      this.scrollToButton();
    },
    typing: function () {
      Echo.private(`typing.${this.userActive.id}`).whisper("typing", {
        id: this.idUserMe,
        name: this.nameUser,
      });
    },
    sedMessage: async function () {
      if (this.message != "") {
        await axios
          .post("api/messages/store", {
            content: this.message,
            to: this.userActive.id,
          })
          .then((response) => {
            this.messages.push({
              from: this.idUserMe,
              to: this.userActive.id,
              content: this.message,
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            });
            this.message = "";
          });
        this.scrollToButton();
      } else {
        return false;
      }
    },
    formatDate: function (date) {
      return moment(date).format("DD/MM/YYYY HH:mm");
    },
  },
  async mounted() {
    let tempoDigitando;
    let userDigitando;
    await axios.get("api/users").then((response) => {
      this.users = response.data.users;
    });

    await axios.get("api/user/me").then((Response) => {
      this.idUserMe = Response.data.me.id;
      this.nameUser = Response.data.me.name;
    });

    Echo.private(`user.${this.idUserMe}`).listen(".SendMessage", async (e) => {
      if (this.userActive && this.userActive.id === e.message.from) {
        clearTimeout(tempoDigitando);
        this.userActive.typing = 0;
        await this.messages.push(e.message);
        this.scrollToButton();
      } else {
        const user = this.users.findIndex((user) => {
          if (user.id === e.message.from) {
            return user;
          }
        });

        if (this.users[user].notification === undefined) {
          this.users[user].notification = 0;
        }

        this.users[user].notification += 1;
      }
    });

    Echo.private(`typing.${this.idUserMe}`).listenForWhisper(
      "typing",
      async (e) => {
        if (this.userActive && this.userActive.id === e.id) {
          await (this.userActive.typing = 1);
          clearTimeout(tempoDigitando);
          this.scrollToButton();
          tempoDigitando = setTimeout(() => (this.userActive.typing = 0), 1000);
        }

        const indexUser = this.users.findIndex((user) => {
          if (user.id === e.id) {
            return user;
          }
        });
        clearTimeout(userDigitando);
        this.users[indexUser].typing = 1;
        userDigitando = setTimeout(
          () => (this.users[indexUser].typing = 0),
          1000
        );
      }
    );
  },
};
</script>




