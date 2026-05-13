<template>
  <div class="min-h-screen bg-surface pb-20 lg:pb-0 lg:pl-72">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 bg-navy text-white lg:flex lg:flex-col">
      <div class="px-8 py-8"><div class="font-display text-2xl font-extrabold">Vic Enterprise</div><div class="text-xs font-bold uppercase tracking-[0.2em] text-gold">Mission Control</div></div>
      <nav class="flex-1 space-y-1 px-4">
        <Link v-for="item in nav" :key="item.href" :href="item.href" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-bold" :class="active(item.href) ? 'bg-white/15 text-white border-l-4 border-gold' : 'text-blue-100 hover:bg-white/10'">{{ item.label }}</Link>
      </nav>
      <div class="border-t border-white/15 p-6 text-sm"><div class="font-bold">{{ user.name }}</div><div class="text-blue-200">{{ user.email }}</div></div>
    </aside>
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 lg:px-10">
      <div class="flex items-center gap-4"><input class="input max-w-xl" placeholder="Search orders, inventory, or fleet..." /><div class="ml-auto text-sm font-bold text-navy">{{ user.roles.join(', ') }}</div></div>
    </header>
    <main class="px-4 py-6 lg:px-10"><slot /></main>
    <nav class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-4 border-t border-slate-200 bg-white lg:hidden">
      <Link v-for="item in mobileNav" :key="item.href" :href="item.href" class="px-2 py-3 text-center text-xs font-bold" :class="active(item.href) ? 'text-navy border-t-4 border-gold' : 'text-slate-500'">{{ item.short }}</Link>
    </nav>
  </div>
</template>
<script>
import { Link } from '@inertiajs/inertia-vue3';
export default { components: { Link }, computed: { user() { return this.$page.props.auth.user || { name: 'Guest', email: '', roles: [] }; }, nav() { return [{label:'Dashboard',short:'Home',href:'/dashboard'},{label:'Sales Orders',short:'Orders',href:'/sales-orders'},{label:'Dispatch Portal',short:'Dispatch',href:'/dispatches'},{label:'Inventory',short:'Stock',href:'/inventory'},{label:'Reports',short:'Reports',href:'/reports'},{label:'Customers',short:'Data',href:'/customers'}]; }, mobileNav() { return this.nav.slice(0,4); } }, methods: { active(href) { return window.location.pathname === href || window.location.pathname.startsWith(href + '/'); } } }
</script>