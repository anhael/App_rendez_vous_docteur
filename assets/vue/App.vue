<template>
    <ClientAppView v-if="mode === 'client'" />
    <AdminLoginView v-else-if="mode === 'admin-login'" :error-message="adminLoginError" :loading="adminLoginLoading"
        :csrf-token="adminCsrfToken" @submit="submitAdminLogin" />
    <AdminPatientsSummaryView v-else-if="mode === 'admin-patients'" />
    <AdminDashboardView v-else />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import ClientAppView from './components/ClientAppView.vue'
import AdminLoginView from './components/AdminLoginView.vue'
import AdminDashboardView from './components/AdminDashboardView.vue'
import AdminPatientsSummaryView from './components/AdminPatientsSummaryView.vue'

const pathname = window.location.pathname

const mode = computed(() => {
    if (pathname.startsWith('/admin/login')) {
        return 'admin-login'
    }

    if (pathname.startsWith('/admin/patients')) {
        return 'admin-patients'
    }

    if (pathname.startsWith('/admin')) {
        return 'admin-dashboard'
    }

    return 'client'
})

const adminLoginError = ref('')
const adminLoginLoading = ref(false)
const adminCsrfToken = ''

const submitAdminLogin = async ({ username, password }) => {
    adminLoginLoading.value = true
    adminLoginError.value = ''

    try {
        const formData = new FormData()
        formData.set('_username', username)
        formData.set('_password', password)

        if (adminCsrfToken) {
            formData.set('_csrf_token', adminCsrfToken)
        }

        const response = await fetch('/admin/login', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            redirect: 'follow',
        })

        if (response.redirected || response.url.endsWith('/admin') || response.ok) {
            window.location.href = '/admin'
            return
        }

        adminLoginError.value = 'Connexion impossible.'
    } catch (error) {
        adminLoginError.value = error.message || 'Connexion impossible.'
    } finally {
        adminLoginLoading.value = false
    }
}

onMounted(() => {
    if (mode.value === 'admin-dashboard') {
        document.title = 'Back-office | MediLine'
    } else if (mode.value === 'admin-patients') {
        document.title = 'Back-office | Patients'
    } else {
        document.title = 'MediLine | Rendez-vous medical'
    }
})
</script>