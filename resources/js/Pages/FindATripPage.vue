<script setup>
import { Head } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import AvailabilityCalendar from "../components/AvailabilityCalendar.vue";
import { useForm } from "@inertiajs/inertia-vue3";

const form = useForm({
    outbound: "LEAB",
    dates: [],
    passengers: 1,
    car: false,
    pet: false,
});
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
                        <date-picker
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
