<template>
    <aside class="booking-panel">
        <div class="panel-card">
            <span class="panel-label">Réservation</span>
            <h2 class="panel-title">Bloquer ton séjour</h2>
            <p class="panel-description">
                Choisis une période pour la propriété en vedette et envoie une réservation test.
            </p>

            <div v-if="property" class="booking-summary mb-4">
                <img :src="property.imageUrl" :alt="property.title" class="booking-thumb">
                <div>
                    <h3 class="h6 mb-1">{{ property.title }}</h3>
                    <p class="mb-1 text-muted">{{ property.city }}</p>
                    <strong>{{ property.nightLabel }} / nuit</strong>
                </div>
            </div>

            <form class="booking-form" @submit.prevent="submitBooking">
                <div class="mb-3">
                    <label class="form-label">Date d’arrivée</label>
                    <input v-model="startDate" class="form-control" type="date" :disabled="submitting">
                </div>

                <div class="mb-3">
                    <label class="form-label">Date de départ</label>
                    <input v-model="endDate" class="form-control" type="date" :disabled="submitting">
                </div>

                <button class="btn btn-primary w-100" type="submit" :disabled="submitting || !property">
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                    {{ submitting ? 'Réservation...' : 'Confirmer la réservation' }}
                </button>
            </form>

            <p v-if="authMessage" class="panel-note mt-3 mb-0">{{ authMessage }}</p>
            <p v-if="errorMessage" class="text-danger mt-3 mb-0">{{ errorMessage }}</p>
            <p v-if="successMessage" class="text-success mt-3 mb-0">{{ successMessage }}</p>
        </div>
    </aside>
</template>

<script setup>
import { ref, watchEffect } from 'vue'

const props = defineProps({
    property: {
        type: Object,
        default: null,
    },
    submitting: {
        type: Boolean,
        default: false,
    },
    successMessage: {
        type: String,
        default: '',
    },
    errorMessage: {
        type: String,
        default: '',
    },
    authMessage: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['book'])

const startDate = ref('')
const endDate = ref('')

const defaultStart = () => {
    const date = new Date()
    date.setDate(date.getDate() + 14)
    return date.toISOString().slice(0, 10)
}

const defaultEnd = () => {
    const date = new Date()
    date.setDate(date.getDate() + 17)
    return date.toISOString().slice(0, 10)
}

watchEffect(() => {
    if (!startDate.value) {
        startDate.value = defaultStart()
    }

    if (!endDate.value) {
        endDate.value = defaultEnd()
    }

    if (props.property) {
        startDate.value = defaultStart()
        endDate.value = defaultEnd()
    }
})

const submitBooking = () => {
    emit('book', {
        startDate: startDate.value,
        endDate: endDate.value,
    })
}
</script>