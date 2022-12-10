<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { ref, watch } from "@vue/runtime-core";
import { usePage } from "@inertiajs/inertia-vue3";
import Pulse from './Pulse.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    apiRoute: {
        type: String,
        required: true,
    },
    routePayload: {
        type: Object,
        default: null,
    },
});

const jobStatus = usePage().props.value.jobStatus;


const isDarkMode = ref(false);
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");
isDarkMode.value = darkTheme.matches;

const dates = ref({
    ABKI: [],
    ABLE: [],
    KIAB: [],
    KILE: [],
    LEAB: [],
    LEKI: [],
    SCST: [],
    STSC: [],
});

const loading = ref(false);

const viewingMonth = ref(null);
const viewingYear = ref(null);

const requestData = async (month, year, route = null) => {
    loading.value = true;
    let params = "";

    if (props.routePayload) {
        params = `?${new URLSearchParams(props.routePayload).toString()}`;
    }

    viewingMonth.value = month;
    viewingYear.value = year;

    const { data } = await axios.get(
        `/api/${props.apiRoute}/${month}/${year}/${routeCode.value}${params}`
    );

    dates.value[route] = data[routeCode.value];

    loading.value = false;
};

const routeCode = ref("ABLE");

const setRouteCode = (code) => {
    routeCode.value = code;
};

const today = new Date();
const month = today.getMonth() + 1;
const year = today.getFullYear();

const routesList = [
    { code: "ABKI", name: "Aberdeen to Kirkwall" },
    { code: "ABLE", name: "Aberdeen to Lerwick" },
    { code: "KIAB", name: "Kirkwall to Aberdeen" },
    { code: "KILE", name: "Kirkwall to Lerwick" },
    { code: "LEAB", name: "Lerwick to Aberdeen" },
    { code: "LEKI", name: "Lerwick to Kirkwall" },
    { code: "SCST", name: "Scrabster to Stromness" },
    { code: "STSC", name: "Stromness to Scrabster" },
];

requestData(month, year, routeCode.value);

const getAvailabilityClass = (date, route) => {
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

    let monthString = month.toString().length === 1 ? "0" + month : month;
    const formattedDate = `${year}-${monthString}-${day}`;

    const foundDate = dates.value[route].find(
        (item) => item.date === formattedDate
    );

    if (!foundDate || foundDate.past) {
        return "bg-gray-200";
    }

    return foundDate.available ? "green" : "red";
};

const updateFromPage = ({ month, year }, route) => {
    requestData(month, year, route);
};

const isToday = (day) => {
    const today = new Date();
    const date = new Date(day.id);

    return (
        date.getFullYear() === today.getFullYear() &&
        date.getMonth() === today.getMonth() &&
        date.getDate() === today.getDate()
    );
};

watch(() => props.routePayload, () => requestData(viewingMonth.value, viewingYear.value));

</script>

<template>
    <div>
        <div class="sm:flex justify-between sm:pt-0 mb-4">
            <h1 class="text-4xl mb-2 md:mb-0 text-gray-600 dark:text-white">
                {{ title }}
            </h1>

            <div v-if="jobStatus.lastFetched" class="text-sm dark:text-white">
                <p>Last fetched: {{ jobStatus.lastFetched }}</p>
                <div class="flex items-center mt-2" v-if="jobStatus.currentlyRunning">
                    <span>currently syncing</span>
                    <Pulse class="ml-4" />
                </div>
            </div>
        </div>

        <p class="mb-2 dark:text-white">
            {{ description }}
        </p>

        <p class="mb-3 dark:text-white">
            Please note: This data is for information and should not be fully relied upon for trip planning. We attempt to sync data every 15 minutes.
        </p>

        <div class="mb-2 dark:text-white">
            <div class="flex items-center">
                <div class="availability-dot green" />
                <span class="ml-3">Available</span>
            </div>
            <div class="flex items-center">
                <div class="availability-dot red" />
                <span class="ml-3">Unavailable</span>
            </div>
            <div class="flex items-center">
                <div class="availability-dot bg-gray-200" />
                <span class="ml-3">Unknown</span>
            </div>
        </div>

        <slot name="before-calendar" />

        <hr class="my-4" />

                <select
            v-model="routeCode"
            class="border border-gray-300 rounded-md px-3 py-2 mb-4"
            style="width: 50%"
            @change="
                updateFromPage(
                    { month: viewingMonth, year: viewingYear },
                    routeCode
                )
            "
        >
            <option
                v-for="route in routesList"
                :key="route.code"
                :value="route.code"
            >
                {{ route.name }}
            </option>
        </select>


        <!-- <div v-for="(route, index) in ['LEAB', 'ABLE']" :key="route">
            <div class="flex justify-center">
                <p
                    v-if="route === 'LEAB'"
                    class="text-2xl text-gray-600 dark:text-white"
                >
                    Lerwick to Aberdeen
                </p>
                <p
                    v-if="route === 'ABLE'"
                    class="text-2xl text-gray-600 dark:text-gray-200"
                >
                    Aberdeen to Lerwick
                </p>
            </div> -->

            <div>
                <div class="calendar-spinner" v-if="loading">
                    <easy-spinner
                        type="spins"
                        size="50"
                        color="#22C55E"
                    />
                </div>

                <Calendar
                    :key="routeCode"
                    class="mb-6"
                    is-expanded
                    :is-dark="isDarkMode"
                    @update:from-page="(value) => updateFromPage(value, routeCode)"
                >
                    <template v-slot:day-content="{ day, dayEvents }">
                        <div v-on="dayEvents">
                            <div class="flex justify-center">
                                <span v-if="!isToday(day)">{{ day.label }}</span>
                                <div v-else class="today">{{ day.label }}</div>
                            </div>
                            <div class="flex justify-center mb-4">
                                <div
                                    class="availability-dot"
                                    :class="
                                        getAvailabilityClass(day.date, routeCode)
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </Calendar>
            </div>
        </div>

    <!-- </div> -->
</template>

<style scoped>
.calendar-spinner {
    z-index: 9999;
    position: absolute;
    top: 57%;
    left: 48%;
}

.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin: 0 5px;
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