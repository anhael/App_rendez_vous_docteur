<template>
    <section class="auth-panel mb-4">
        <div class="auth-card">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <span class="section-label">Espace client</span>
                    <h2 class="auth-title">Compte voyageur</h2>
                    <p class="text-muted mb-0">Connecte-toi pour réserver et suivre tes séjours.</p>
                </div>

                <div v-if="user" class="auth-user-badge">
                    <strong>{{ user.firstname }} {{ user.name }}</strong>
                    <span>{{ user.email }}</span>
                    <button class="btn btn-sm btn-outline-secondary mt-2" type="button" @click="$emit('logout')">
                        Déconnexion
                    </button>
                </div>
            </div>

            <div v-if="!user" class="mt-3">
                <div class="auth-tabs mb-3">
                    <button class="btn btn-sm" :class="mode === 'login' ? 'btn-dark' : 'btn-outline-dark'" type="button"
                        @click="mode = 'login'">Connexion</button>
                    <button class="btn btn-sm" :class="mode === 'register' ? 'btn-dark' : 'btn-outline-dark'"
                        type="button" @click="mode = 'register'">Inscription</button>
                </div>

                <form v-if="mode === 'login'" class="row g-2" @submit.prevent="submitLogin">
                    <div class="col-12 col-md-5">
                        <input v-model.trim="loginForm.email" class="form-control" type="email" placeholder="Email"
                            required>
                    </div>
                    <div class="col-12 col-md-5">
                        <input v-model="loginForm.password" class="form-control" type="password"
                            placeholder="Mot de passe" required>
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-dark" type="submit" :disabled="loading">Se connecter</button>
                    </div>
                </form>

                <form v-else class="row g-2" @submit.prevent="submitRegister">
                    <div class="col-12 col-md-3">
                        <input v-model.trim="registerForm.firstname" class="form-control" type="text"
                            placeholder="Prénom" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <input v-model.trim="registerForm.name" class="form-control" type="text" placeholder="Nom"
                            required>
                    </div>
                    <div class="col-12 col-md-3">
                        <input v-model.trim="registerForm.email" class="form-control" type="email" placeholder="Email"
                            required>
                    </div>
                    <div class="col-12 col-md-2">
                        <input v-model="registerForm.password" class="form-control" type="password"
                            placeholder="Mot de passe" minlength="6" required>
                    </div>
                    <div class="col-12 col-md-1 d-grid">
                        <button class="btn btn-dark" type="submit" :disabled="loading">OK</button>
                    </div>
                </form>
            </div>

            <p v-if="message" class="mt-3 mb-0" :class="messageType === 'error' ? 'text-danger' : 'text-success'">
                {{ message }}
            </p>
        </div>
    </section>
</template>

<script setup>
import { reactive, ref } from 'vue'

defineProps({
    user: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: '',
    },
    messageType: {
        type: String,
        default: 'success',
    },
})

const emit = defineEmits(['login', 'register', 'logout'])

const mode = ref('login')
const loginForm = reactive({
    email: '',
    password: '',
})

const registerForm = reactive({
    firstname: '',
    name: '',
    email: '',
    password: '',
})

const submitLogin = () => {
    emit('login', { ...loginForm })
}

const submitRegister = () => {
    emit('register', { ...registerForm })
}
</script>
