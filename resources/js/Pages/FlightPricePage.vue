<script setup>
import { Head } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import FlightPriceCalendar from "../components/FlightPriceCalendar.vue";
import { computed, ref } from "@vue/runtime-core";

const ABZ = "ABZ";
const LSI = "LSI";
const KOI = "KOI";

const departureAirport = ref(LSI);
const arrivalAirport = ref(ABZ);

const airports = [
    { text: "Sumburgh", value: LSI },
    { text: "Kirkwall", value: KOI },
    { text: "Aberdeen", value: ABZ },
];

const arrivalAirports = computed(() =>
    airports.filter((airport) => airport.value !== departureAirport.value)
);

const routePayload = computed(() => ({
    departureAirport: departureAirport.value,
    arrivalAirport: arrivalAirport.value,
}));
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
            fit
        "
        style="padding-bottom: 25px"
    >
        <NavBar />
        <Container>
            <FlightPriceCalendar
                title="Flight price chart"
                description="This calendar shows the price of flying around the isles"
                api-route="flights"
                :route-payload="routePayload"
            >
                <template #before-calendar>
                    <hr class="my-4" />

                    <div class="flex justify-between">
                        <div>
                            <p class="dark:text-white">
                                Select departure airport:
                            </p>

                            <select
                                class="form-select block w-full mt-1"
                                v-model="departureAirport"
                            >
                                <option
                                    v-for="airport in airports"
                                    :key="airport.value"
                                    :value="airport.value"
                                >
                                    {{ airport.text }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <p class="dark:text-white">
                                Select arrival airport:
                            </p>

                            <select
                                class="form-select block w-full mt-1"
                                v-model="arrivalAirport"
                            >
                                <option
                                    v-for="airport in arrivalAirports"
                                    :key="airport.value"
                                    :value="airport.value"
                                >
                                    {{ airport.text }}
                                </option>
                            </select>
                        </div>
                    </div>
                </template>
            </FlightPriceCalendar>
        </Container>
    </div>
</template>

<style scoped>
/* .fit class width: fit-content when mobile */
@media (max-width: 640px) {
    .fit {
        width: fit-content;
    }
}
</style>
