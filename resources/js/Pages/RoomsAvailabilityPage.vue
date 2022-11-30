<script setup>
import { Head } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import PriceAvailabilityCalendar from "../components/PriceAvailabilityCalendar.vue";
import { computed, ref } from "@vue/runtime-core";

const roomType = ref('NI4');

const roomTypes = [
    { text: "Inside 4 berth cabin (bunk beds)", value: "NI4" },
    { text: "Inside 4 berth cabin with Privacy Curtains (bunk beds)", value: "NI4S" },
    { text: "Inside 4 berth Pet-friendly cabin (bunk beds)", value: "NPETI4" },
    { text: "Inside 2 berth cabin (bunk beds)", value: "NI2" },
    { text: "Inside 2 berth Pet-friendly cabin", value: "NPETI2" },
    { text: "Outside 2 berth cabin", value: "NO2" },
    { text: "Outside 2 berth Pet-friendly cabin", value: "NPETO2" },
    { text: "Outside 3 berth cabin (bunk beds)", value: "NO3" },
    { text: "Premium Outside 2 berth cabin", value: "NPREM" },
    { text: "Premium Outside 2 berth Pet-friendly cabin", value: "NPETPR" },
    { text: "Executive Outside 2 berth cabin (bunk beds)", value: "NEXEC" },
    { text: "Pod in Pod Lounge 1", value: "POD" },
    { text: "Pod in Pod Lounge 2", value: "POD2" },
    { text: "Pod in Pod Lounge 3", value: "POD3" },
    { text: "Reclining seat", value: "NRSEAT" },
];

const routePayload = computed(() => ({
    roomType: roomType.value,
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
        style="padding-bottom: 25px;"
    >
        <NavBar />
        <Container>
            <PriceAvailabilityCalendar
                title="Room availability"
                description="Use this tool to determine available accommodation on future ferry sailings. The top pill is the cost of the room and the bottom is how many are remaining"
                api-route="accommodation"
                :route-payload="routePayload"
            >
                <template #before-calendar>
                    <p class="dark:text-white">Select a room type from the below list:</p>

                    <select
                        class="form-select block w-full mt-1"
                        v-model="roomType"
                    >
                        <option
                            v-for="roomType in roomTypes"
                            :key="roomType.value"
                            :value="roomType.value"
                        >
                            {{ roomType.text }}
                        </option>
                    </select>
                </template>
            </PriceAvailabilityCalendar>
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