<template>
    <HeaderVue></HeaderVue>
    <slot/>
</template>
<script>
import HeaderVue from "@/Pages/Layout/HeaderVue.vue";
export default {
    components: {
        HeaderVue,
    }
}
</script>
