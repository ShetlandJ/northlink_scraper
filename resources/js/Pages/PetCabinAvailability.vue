<script setup>
import { ref, computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import PetCabinAvailabilityCalendar from "../components/PetCabinAvailabilityCalendar.vue";
</script>

<template>
    <Head title="Northlink Trip Availability" />

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
            <div class="flex justify-center pt-8 sm:justify-start sm:pt-0 mb-4">
                <h1 class="text-4xl text-gray-600 dark:text-gray-200">
                    Pet Cabin Availability
                </h1>
            </div>

            <p class="mb-2">
                This tool helps to determine pet cabin availability on the
                Lerwick/Aberdeen routes.
            </p>
            <div class="mb-2">
                <div class="flex items-center">
                    <div class="availability-dot available" />
                    <span class="ml-3">Available</span>
                </div>
                <div class="flex items-center">
                    <div class="availability-dot unavailable" />
                    <span class="ml-3">Unavailable</span>
                </div>
                <div class="flex items-center">
                    <div class="availability-dot unknown" />
                    <span class="ml-3">Unknown</span>
                </div>
            </div>

            <hr class="my-4" />

            <PetCabinAvailabilityCalendar :start-date="new Date(2022, 9, 1)" />
        </Container>
    </div>
</template>

<style scoped>
.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.available {
    background-color: green;
}

.unavailable {
    background-color: red;
}

.unknown {
    background-color: gray;
}
</style>