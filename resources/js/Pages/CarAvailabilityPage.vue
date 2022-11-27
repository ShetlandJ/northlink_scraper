<script setup>
import { Head } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import CarAvailabilityCalendar from "../components/CarAvailabilityCalendar.vue";
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
            <CarAvailabilityCalendar
                title="Car availabililty"
                description="This tool helps to determine car availability on the
                Lerwick/Aberdeen routes."
                api-route="cars"
            />
        </Container>
    </div>
</template>
