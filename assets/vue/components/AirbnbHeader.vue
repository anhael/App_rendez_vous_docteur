<template>
    <header class="airbnb-header sticky-top">
        <div class="container py-3">
            <div class="header-bar">
                <div>
                    <span class="brand-kicker">Portfolio project</span>
                    <h1 class="brand-title mb-0">Voyage Maison</h1>
                </div>

                <div class="header-chip-group d-none d-md-flex">
                    <span class="header-chip">{{ propertiesCount }} logements</span>
                    <span class="header-chip">{{ bookingsCount }} réservations</span>
                    <span class="header-chip header-chip-emphasis">{{ selectedTitle }}</span>
                </div>

                <div class="header-actions">
                    <button class="btn btn-sm btn-outline-light" type="button" @click="$emit('show-listings')">
                        Catalogue
                    </button>
                    <button class="btn btn-sm btn-primary" type="button" @click="$emit('show-bookings')">
                        Réservations
                    </button>
                    <a class="btn btn-sm btn-light" href="/admin">
                        Back-office
                    </a>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
defineProps({
    propertiesCount: {
        type: Number,
        default: 0,
    },
    bookingsCount: {
        type: Number,
        default: 0,
    },
    selectedTitle: {
        type: String,
        default: '',
    },
})
</script>