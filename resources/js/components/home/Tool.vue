<script setup>
import { Link } from "@inertiajs/inertia-vue3";
import { computed } from "@vue/runtime-core";

const props = defineProps({
    emoji: {
        type: String,
        required: true,
    },
    text: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    colour: {
        type: String,
        default: 'indigo',
    }
});

const outerClasses = computed(() => {
    console.log(props.colour);
    return `
    p-2
    items-center
    leading-none
    lg:rounded-full
    flex
    lg:inline-flex
    cursor-pointer
    bg-${props.colour}-800 text-${props.colour}-100
`});
</script>

<template>
    <div class="my-4" :key="text">
        <Link
            role="alert"
            :href="route(routeName)"
            :class="outerClasses"
        >
            <span
                class="
                    flex
                    rounded-full
                    uppercase
                    px-2
                    py-1
                    text-xs
                    font-bold
                    mr-3
                "
                :class="`bg-${colour}-500`"
                >{{ emoji }}</span
            >
            <span class="font-semibold mr-2 text-left flex-auto">
                {{ text }}
            </span>
            <svg
                class="fill-current opacity-75 h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
            >
                <path
                    d="M12.95 10.707l.707-.707L8 4.343 6.586 5.757 10.828 10l-4.242 4.243L8 15.657l4.95-4.95z"
                />
            </svg>
        </Link>
    </div>
</template>