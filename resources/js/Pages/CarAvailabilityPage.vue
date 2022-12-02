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
                title="Car availability"
                description="This tool helps to determine car availability on the
                Lerwick/Aberdeen routes."
                api-route="cars"
            >
                <template #before-calendar>
                    <div class="mb-2 dark:text-white">
                        <div class="flex items-center">
                            <div class="availability-dot green" />
                            <span class="ml-3">Good availability</span>
                        </div>
                        <div class="flex items-center">
                            <div class="availability-dot yellow" />
                            <span class="ml-3">Medium availability</span>
                        </div>
                        <div class="flex items-center">
                            <div class="availability-dot red" />
                            <span class="ml-3">No availability</span>
                        </div>
                        <div class="flex items-center">
                            <div class="availability-dot bg-gray-200" />
                            <span class="ml-3">Unknown</span>
                        </div>
                    </div>
                </template>
            </CarAvailabilityCalendar>
        </Container>
    </div>
</template>

<style>
.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin: 0 5px;
}

.red {
  background-color: #DB4325;
}

.orange {
  background-color: #EDA247;
}

.yellow {
  background-color: #FFC20A;
}

.green {
  background-color: #57C4AD;
}

.blue {
  background-color: #0571B0;
}
</style>