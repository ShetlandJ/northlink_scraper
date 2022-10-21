<script setup>
import { Head } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import AvailabilityCalendar from "../components/AvailabilityCalendar.vue";
import { useForm } from "@inertiajs/inertia-vue3";
import { ref } from "@vue/runtime-core";

const form = ref({
    outbound: "LEAB",
    dates: {
        start: new Date(),
        end: new Date(),
    },
    passengers: 1,
    car: false,
    pet: false,
});

const setRangeToNextSevenDays = () => {
    const today = new Date();
    const nextWeek = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 7);

    console.log("test", form.value.dates);
    form.value.dates.start = today
    form.value.dates.end = nextWeek;
    console.log("test", form.value.dates);
};

const setRangeToThisMonth = () => {
    const today = new Date();
    const endOfThisMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    form.value.dates.start = today
    form.value.dates.end = endOfThisMonth;
};

const setRangeToNextMonth = () => {
    const today = new Date();
    const startOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    const endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);

    form.value.dates.start = startOfNextMonth
    form.value.dates.end = endOfNextMonth;
};

</script>

<template>
    <Head title="Find a trip" />

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
            <form @submit.prevent="createWord">
                <div class="form-group mb-6">
                    <label
                        for="wordInput"
                        class="
                            form-label
                            inline-block
                            mb-2
                            dark:text-white
                            text-gray-700
                        "
                    >
                        Where are you departing from?
                    </label>
                    <div>
                        <button
                            @click="form.outbound = 'LEAB'"
                            :class="{
                                'bg-blue-500 text-white':
                                    form.outbound === 'LEAB',
                                'bg-gray-200 text-black':
                                    form.outbound !== 'LEAB',
                            }"
                            class="
                                py-2
                                px-4
                                shadow-md
                                no-underline
                                rounded-lg
                                font-sans font-semibold
                                text-sm
                                border-blue
                                btn-primary
                                hover:shadow-lg
                                focus:outline-none
                                active:shadow-none
                                mr-2
                            "
                        >
                            <span class="mr-2" v-if="form.outbound === 'LEAB'"
                                >✓</span
                            >
                            Lerwick
                        </button>
                        <button
                            @click="form.outbound = 'ABLE'"
                            class="
                                py-2
                                px-4
                                shadow-md
                                no-underline
                                rounded-lg
                                font-sans font-semibold
                                text-sm
                                border-orange
                                btn-primary
                                hover:shadow-lg
                                focus:outline-none
                                active:shadow-none
                                mr-2
                            "
                            :class="{
                                'bg-blue-500 text-white':
                                    form.outbound === 'ABLE',
                                'bg-gray-200 text-black':
                                    form.outbound !== 'ABLE',
                            }"
                        >
                            <span class="mr-2" v-if="form.outbound === 'ABLE'"
                                >✓</span
                            >
                            Aberdeen
                        </button>
                    </div>
                </div>

                <div class="form-group mb-6">
                    <label
                        for="wordInput"
                        class="
                            form-label
                            inline-block
                            mb-2
                            dark:text-white
                            text-gray-700
                        "
                    >
                        When are you looking to travel?
                    </label>
                    <div>
                        <!-- create <a> link that says next 7 days -->
                        <div>
                            <button
                                class="text-blue-500 text-sm underline mr-4"
                                @click="setRangeToNextSevenDays"
                            >
                                Next 7 days
                            </button>
                            <button
                                class="text-blue-500 text-sm underline mr-4"
                                @click="setRangeToThisMonth"
                            >
                                This month
                            </button>
                            <button
                                class="text-blue-500 text-sm underline mr-4"
                                @click="setRangeToNextMonth"
                            >
                                Next month
                            </button>
                        </div>

                        <date-picker
                            :key="form.dates"
                            class="mb-6"
                            is-range
                            mode="range"
                            v-model="form.dates"
                            :date="form.dates"
                        >
                        </date-picker>
                    </div>
                </div>
            </form>
        </Container>
    </div>
</template>
