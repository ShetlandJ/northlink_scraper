<script setup>
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import Tool from "../components/home/Tool.vue";
</script>

<template>
    <Head title="Northlink Scanner" />

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
            <div class="dark:text-white">
                <p class="mb-2 text-xl">Welcome to Northlink Scanner.</p>

                <p class="mb-2">
                    The aim of this website is to help those travelling on the
                    Northlink ferry to find cheaper bookings and check availability
                    more easily than using Northlink's booking service.
                </p>
                <p class="mb-2">
                    These are the tools that are currently available:
                </p>
            </div>

            <div class="text-center py-4 lg:px-4">
                <!-- <Tool
                    emoji="🚢"
                    text="Find a trip"
                    route-name="find-a-trip"
                    colour="blue"
                /> -->

                <Tool
                    emoji="🛏"
                    text="Room availability"
                    route-name="accommodation"
                    colour="green"
                />

                <Tool
                    emoji="🚗"
                    text="Car availability"
                    route-name="cars"
                    colour="yellow"
                />

                <Tool
                    emoji="🐶"
                    text="Pet cabin availability"
                    route-name="pets"
                    colour="blue"
                />

                <!-- <Tool
                    emoji="🛩️"
                    text="Loganair flight prices"
                    route-name="flights"
                    colour="indigo"
                /> -->

                <!-- <Tool
                    emoji="📱"
                    text="Text cancellation demo"
                    route-name="text-demo"
                    colour="indigo"
                /> -->
            </div>
        </Container>
    </div>
</template>

<style>
progress::-webkit-progress-bar {
    background-color: white;
}

progress::-webkit-progress-value {
    transition: width 0.1s;
}

.bg-gray-100 {
    background-color: #f7fafc;
    background-color: rgba(247, 250, 252, var(--tw-bg-opacity));
}

.text-gray-500 {
    color: #a0aec0;
    color: rgba(160, 174, 192, var(--tw-text-opacity));
}

.text-gray-600 {
    color: #718096;
    color: rgba(113, 128, 150, var(--tw-text-opacity));
}

.text-gray-700 {
    color: #4a5568;
    color: rgba(74, 85, 104, var(--tw-text-opacity));
}

.text-gray-900 {
    color: #1a202c;
    color: rgba(26, 32, 44, var(--tw-text-opacity));
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-gray-800 {
        background-color: #2d3748;
        background-color: rgba(45, 55, 72, var(--tw-bg-opacity));
    }

    .dark\:bg-gray-900 {
        background-color: #1a202c;
        background-color: rgba(26, 32, 44, var(--tw-bg-opacity));
    }

    .dark\:border-gray-700 {
        border-color: #4a5568;
        border-color: rgba(74, 85, 104, var(--tw-border-opacity));
    }

    .dark\:text-white {
        color: #fff;
        color: rgba(255, 255, 255, var(--tw-text-opacity));
    }
}
</style>
