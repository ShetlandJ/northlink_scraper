<script setup>
import { ref, computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import CapacityCalendar from "../components/CapacityCalendar.vue";
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
            <div class="flex justify-center sm:pt-8 sm:justify-start sm:pt-0 mb-4">
                <h1 class="text-4xl text-gray-600 dark:text-gray-200">
                    Capacity calendar
                </h1>
            </div>

            <p class="mb-2">
                This tool shows the availability per day.
            </p>

            <hr class="my-4" />

            <CapacityCalendar :start-date="new Date(2022, 9, 1)" />
        </Container>
    </div>
</template>

<style scoped>
.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
</style>