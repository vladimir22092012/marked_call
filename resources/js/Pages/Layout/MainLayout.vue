<template>
    <HeaderVue></HeaderVue>
    <slot/>
</template>
<script>
import HeaderVue from "@/Pages/Layout/HeaderVue.vue";
export default {
    components: {
        HeaderVue,
    },
    mounted() {
        window.Echo.private(`LayoutNotify.${this.$page.props.auth.user.id}`)
            .listen('.marked_call.end', (e) => {
                console.log(e);
                this.$swal({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Внимание!',
                    html: e.message,
                    showConfirmButton: false,
                    timer: 3000
                });
            });
    }
}
</script>
