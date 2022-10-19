<script setup>
import { ref, computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import Tool from "../components/home/Tool.vue";
</script>

<template>
    <Head title="Northlink Scanner" />

    <div
        class="
            items-top
            justify-center
            min-h-screen
            bg-gray-100
            dark:bg-gray-900
            sm:items-center sm:pt-0
        "
        style="padding-bottom: 25px"
    >
        <NavBar />

        <Container>
            <p class="mb-2 text-xl">Welcome to Northlink Scanner.</p>

            <p class="mb-2">
                The aim of this website is to help those travelling on the
                Northlink ferry to find cheaper bookings and check availability
                most easily than using Northlink's booking service.
            </p>
            <p class="mb-2">
                These are the tools that are currently available:
            </p>

            <div class="bg-gray-300 text-center py-4 lg:px-4">
                <Tool
                    emoji="📆"
                    text="Check available capacity"
                    route-name="capacity"
                    colour="green"
                />

                <Tool
                    emoji="🐶"
                    text="Check pet cabin availability"
                    route-name="pets"
                    colour="indigo"
                />

                <Tool
                    emoji="🚗"
                    text="Check car availability"
                    route-name="cars"
                    colour="yellow"
                />
            </div>
        </Container>
    </div>
</template>

<style>
progress::-webkit-progress-bar {
    background-color: white;
}

progress::-webkit-progress-value {
    transition: width 0.1s;
}

.bg-gray-100 {
    background-color: #f7fafc;
    background-color: rgba(247, 250, 252, var(--tw-bg-opacity));
}

.text-gray-500 {
    color: #a0aec0;
    color: rgba(160, 174, 192, var(--tw-text-opacity));
}

.text-gray-600 {
    color: #718096;
    color: rgba(113, 128, 150, var(--tw-text-opacity));
}

.text-gray-700 {
    color: #4a5568;
    color: rgba(74, 85, 104, var(--tw-text-opacity));
}

.text-gray-900 {
    color: #1a202c;
    color: rgba(26, 32, 44, var(--tw-text-opacity));
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-gray-800 {
        background-color: #2d3748;
        background-color: rgba(45, 55, 72, var(--tw-bg-opacity));
    }

    .dark\:bg-gray-900 {
        background-color: #1a202c;
        background-color: rgba(26, 32, 44, var(--tw-bg-opacity));
    }

    .dark\:border-gray-700 {
        border-color: #4a5568;
        border-color: rgba(74, 85, 104, var(--tw-border-opacity));
    }

    .dark\:text-white {
        color: #fff;
        color: rgba(255, 255, 255, var(--tw-text-opacity));
    }
}
</style>
