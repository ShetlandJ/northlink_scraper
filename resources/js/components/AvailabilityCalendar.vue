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
    LEAB: [],
    ABLE: [],
});

const loading = ref(false);

const viewingMonth = ref(null);
const viewingYear = ref(null);

const requestData = async (month, year, route = null) => {
    loading.value = true;
    let params = "";
    if (props.routePayload) {
        params = new URLSearchParams(props.routePayload).toString();
    }

    viewingMonth.value = month;
    viewingYear.value = year;

    const { data } = await axios.get(
        `/api/${props.apiRoute}/${month}/${year}?${params}`
    );

    if (!route) {
        dates.value.LEAB = data.LEAB;
        dates.value.ABLE = data.ABLE;
    } else {
        dates.value[route] = data[route];
    }
    loading.value = false;
};

const today = new Date();
const month = today.getMonth() + 1;
const year = today.getFullYear();

requestData(month, year);

const getAvailabilityClass = (date, route) => {
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

    let monthString = month.toString().length === 1 ? "0" + month : month;
    const formattedDate = `${year}-${monthString}-${day}`;

    const foundDate = dates.value[route].find(
        (item) => item.date === formattedDate
    );

    if (!foundDate) {
        return "bg-gray-200";
    }

    if (foundDate.past) return "blue";

    return foundDate.available ? "green" : "red";
};

const updateFromPage = ({ month, year }, route) => {
    requestData(month, year, route);
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
                <div class="availability-dot blue" />
                <span class="ml-3">This ship has sailed</span>
            </div>
            <div class="flex items-center">
                <div class="availability-dot bg-gray-200" />
                <span class="ml-3">Unknown</span>
            </div>
        </div>

        <slot name="before-calendar" />

        <hr class="my-4" />

        <div v-for="(route, index) in ['LEAB', 'ABLE']" :key="route">
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
            </div>

            <div>
                <div class="calendar-spinner" v-if="loading">
                    <easy-spinner
                        type="spins"
                        size="50"
                        color="#22C55E"
                    />
                </div>

                <Calendar
                    class="mb-6"
                    is-expanded
                    :is-dark="isDarkMode"
                    @update:from-page="(value) => updateFromPage(value, route)"
                >
                    <template v-slot:day-content="{ day, dayEvents }">
                        <div v-on="dayEvents">
                            <div class="flex justify-center">
                                {{ day.label }}
                            </div>
                            <div class="flex justify-center mb-4">
                                <div
                                    class="availability-dot"
                                    :class="
                                        getAvailabilityClass(day.date, route)
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </Calendar>
            </div>

            <hr v-if="index === 0" class="mb-4" />
        </div>

    </div>
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