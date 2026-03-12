<template>
    <router-view />
    <AlertContainer />
</template>

<script>
import AlertContainer from './components/AlertContainer.vue';

export default {
    name: 'App',
    components: {
        AlertContainer
    }
}
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background-color: #f7f8fc;
    color: #2c3e50;
}
</style>

