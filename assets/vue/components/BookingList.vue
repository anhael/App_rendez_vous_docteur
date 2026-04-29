<template>
    <section class="bookings-panel">
        <div class="section-heading mb-3">
            <span class="section-label">Mes réservations</span>
            <h2>Historique personnel</h2>
            <p>Les réservations sont récupérées depuis l’API Symfony et affichées sous forme de timeline.</p>
        </div>

        <div v-if="loading" class="state-card text-center py-5">
            <div class="spinner-border text-light" role="status"></div>
            <p class="mt-3 mb-0">Chargement de l’historique...</p>
        </div>

        <div v-else-if="authMessage && !bookings.length" class="state-card text-center py-5">
            <h3 class="h5 mb-2">Accès privé</h3>
            <p class="mb-0">{{ authMessage }}</p>
        </div>

        <div v-else-if="bookings.length" class="booking-timeline">
            <article v-for="booking in bookings" :key="booking.id" class="timeline-card">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div>
                            <h3 class="h6 mb-1">{{ booking.property?.title || booking.property || 'Logement réservé' }}
                            </h3>
                            <p class="text-muted mb-0">Réservation #{{ booking.id }}</p>
                        </div>
                        <span class="timeline-badge">Confirmée</span>
                    </div>

                    <div class="timeline-dates">
                        <span>Arrivée: {{ booking.startDate }}</span>
                        <span>Départ: {{ booking.endDate }}</span>
                    </div>
                </div>
            </article>
        </div>

        <div v-else class="state-card text-center py-5">
            <h3 class="h5 mb-2">Aucune réservation pour le moment</h3>
            <p class="mb-0">Réserve un logement pour afficher la timeline ici.</p>
        </div>
    </section>
</template>

<script setup>
defineProps({
    bookings: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    authMessage: {
        type: String,
        default: '',
    },
})
</script>