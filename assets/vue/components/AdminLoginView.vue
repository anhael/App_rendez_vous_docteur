<template>
    <div class="container py-4 py-lg-5 admin-vue-shell">
        <div class="admin-login-hero mb-4">
            <div>
                <span class="admin-hero-eyebrow">Admin sécurisé</span>
                <h1>Connexion au back-office</h1>
                <p>Un acces rapide pour piloter les cliniques, medecins et rendez-vous.</p>
            </div>
            <div class="admin-login-pills">
                <span>Gestion des cliniques</span>
                <span>Suivi des rendez-vous</span>
                <span>Comptes patients</span>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="admin-auth-card">
                    <div class="admin-auth-visual">
                        <span class="admin-auth-badge">Back-office</span>
                        <h2>MediLine Admin</h2>
                        <p>Compte demo: admin@medicline.local / Admin123!</p>
                        <a href="/" class="btn btn-light btn-sm mt-3">Retour au site client</a>
                    </div>

                    <div class="admin-auth-form">
                        <div class="mb-4">
                            <h2 class="h4 mb-2">Se connecter</h2>
                            <p class="text-muted mb-0">Utilisez le compte administrateur pour acceder au dashboard.
                            </p>
                        </div>

                        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

                        <form class="row g-3" method="post" action="/admin/login" @submit.prevent="handleSubmit">
                            <div class="col-12">
                                <label class="form-label" for="admin-email">Email</label>
                                <input id="admin-email" v-model.trim="form.username" type="email" name="_username"
                                    class="form-control form-control-lg" autocomplete="email" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="admin-password">Mot de passe</label>
                                <input id="admin-password" v-model="form.password" type="password" name="_password"
                                    class="form-control form-control-lg" autocomplete="current-password" required>
                            </div>

                            <input type="hidden" name="_csrf_token" :value="csrfToken">

                            <div class="col-12 d-grid mt-2">
                                <button class="btn btn-dark btn-lg" type="submit" :disabled="loading">{{ loading ?
                                    'Connexion...' : 'Accéder au dashboard' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
    errorMessage: {
        type: String,
        default: '',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    csrfToken: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['submit'])

const form = reactive({
    username: '',
    password: '',
})

const handleSubmit = () => {
    emit('submit', { ...form })
}
</script>
