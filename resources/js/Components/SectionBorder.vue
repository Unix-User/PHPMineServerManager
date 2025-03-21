<template>
    <div class="hidden sm:block">
        <div class="py-8">
            <div :class="{'border-t border-gray-200': !isDark, 'border-t border-gray-700': isDark}" />
        </div>
    </div>
</template>

<script>
export default {
  data() {
    return {
      isDark: window.matchMedia('(prefers-color-scheme: dark)').matches
    };
  },
  mounted() {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
      this.isDark = e.matches;
    });
  }
}
</script>