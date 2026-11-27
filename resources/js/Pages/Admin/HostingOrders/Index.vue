<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    orders: Object,
    counts: Object,
    filters: Object,
    statuses: Object,
});

const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'all');

const applyFilter = (statusOverride = null) => {
    if (statusOverride !== null) {
        selectedStatus.value = statusOverride;
    }
    router.get(route('admin.orders.index'), {
        search: search.value,
        status: selectedStatus.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    selectedStatus.value = 'all';
    applyFilter();
};

const updateOrderStatus = (order, newStatus) => {
    router.patch(route('admin.orders.update-status', order.id), {
        status: newStatus,
    }, { preserveScroll: true });
};

const deleteOrder = (order) => {
    if (confirm(`Are you sure you want to delete order #${order.order_number}?`)) {
        router.delete(route('admin.orders.destroy', order.id));
    }
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'requested':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'pending':
            return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'confirmed':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        case 'cancelled':
            return 'bg-rose-100 text-rose-800 border-rose-300';
        default:
            return 'bg-slate-100 text-slate-800 border-slate-300';
    }
};

const getStatusDot = (st) => {
    switch (st) {
        case 'requested': return 'bg-amber-500 animate-pulse';
        case 'pending': return 'bg-blue-500';
        case 'confirmed': return 'bg-emerald-500';
        case 'cancelled': return 'bg-rose-500';
        default: return 'bg-slate-400';
    }
};

const getWhatsAppLink = (order) => {
    if (!order.customer_phone) return null;
    const cleanPhone = order.customer_phone.replace(/[^0-9]/g, '');
    const text = encodeURIComponent(
        `Hello ${order.customer_name}, thank you for your order request for ${order.plan_name} (Order #${order.order_number}) on Host Divine Networks. How can we assist you with the setup?`
    );
    return `https://wa.me/${cleanPhone}?text=${text}`;
};
</script>

<template>
    <AdminLayout>
        <Head title="Manage Hosting Orders" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-server text-blue-600"></i>
                        Hosting & Server Orders
                    </h2>
                    <p class="text-sm text-slate-500">
                        Manage all hosting and server order requests submitted from website plan cards and pricing popups.
                    </p>
                </div>
            </div>

            <!-- Metric Cards / Quick Filter Tabs -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <button
                    type="button"
                    @click="applyFilter('all')"
                    class="p-4 rounded-xl border text-left transition shadow-sm"
                    :class="selectedStatus === 'all' ? 'bg-blue-50/70 border-blue-400 ring-2 ring-blue-500/20' : 'bg-white border-slate-200 hover:border-slate-300'"
                >
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">All Orders</div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ counts?.total || 0 }}</div>
                </button>

                <button
                    type="button"
                    @click="applyFilter('requested')"
                    class="p-4 rounded-xl border text-left transition shadow-sm relative overflow-hidden"
                    :class="selectedStatus === 'requested' ? 'bg-amber-50/70 border-amber-400 ring-2 ring-amber-500/20' : 'bg-white border-slate-200 hover:border-amber-300'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Requested</span>
                        <span v-if="counts?.requested > 0" class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-amber-900">{{ counts?.requested || 0 }}</div>
                    <div class="text-[11px] text-amber-600 font-medium">Needs Admin Action</div>
                </button>

                <button
                    type="button"
                    @click="applyFilter('pending')"
                    class="p-4 rounded-xl border text-left transition shadow-sm"
                    :class="selectedStatus === 'pending' ? 'bg-sky-50/70 border-sky-400 ring-2 ring-sky-500/20' : 'bg-white border-slate-200 hover:border-sky-300'"
                >
                    <div class="text-xs font-semibold text-sky-700 uppercase tracking-wider">Pending</div>
                    <div class="mt-2 text-2xl font-extrabold text-sky-900">{{ counts?.pending || 0 }}</div>
                    <div class="text-[11px] text-sky-600 font-medium">Awaiting Payment/Setup</div>
                </button>

                <button
                    type="button"
                    @click="applyFilter('confirmed')"
                    class="p-4 rounded-xl border text-left transition shadow-sm"
                    :class="selectedStatus === 'confirmed' ? 'bg-emerald-50/70 border-emerald-400 ring-2 ring-emerald-500/20' : 'bg-white border-slate-200 hover:border-emerald-300'"
                >
                    <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Confirmed</div>
                    <div class="mt-2 text-2xl font-extrabold text-emerald-900">{{ counts?.confirmed || 0 }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium">Active / Completed</div>
                </button>

                <button
                    type="button"
                    @click="applyFilter('cancelled')"
                    class="p-4 rounded-xl border text-left transition shadow-sm"
                    :class="selectedStatus === 'cancelled' ? 'bg-rose-50/70 border-rose-400 ring-2 ring-rose-500/20' : 'bg-white border-slate-200 hover:border-rose-300'"
                >
                    <div class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Cancelled</div>
                    <div class="mt-2 text-2xl font-extrabold text-rose-900">{{ counts?.cancelled || 0 }}</div>
                    <div class="text-[11px] text-rose-600 font-medium">Declined / Closed</div>
                </button>
            </div>

            <!-- Filter Toolbar -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-xs text-slate-400"></i>
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter()"
                        type="text"
                        placeholder="Search by order #, customer name, email, phone, domain, or plan..."
                        class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                    />
                </div>
                <div class="w-full sm:w-48">
                    <select
                        v-model="selectedStatus"
                        @change="applyFilter()"
                        class="w-full text-sm py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="all">All Statuses</option>
                        <option value="requested">Requested</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button
                        @click="applyFilter()"
                        class="px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm"
                    >
                        Search
                    </button>
                    <button
                        v-if="search || selectedStatus !== 'all'"
                        @click="resetFilter"
                        class="px-3 py-2 text-sm font-medium bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition"
                        title="Reset Filters"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-3 px-4">Order # / Date</th>
                                <th class="py-3 px-4">Customer Details</th>
                                <th class="py-3 px-4">Plan & Pricing</th>
                                <th class="py-3 px-4">Domain / Location</th>
                                <th class="py-3 px-4">Status & Action</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!orders?.data || orders.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-4xl mb-3 text-slate-300 block"></i>
                                    No hosting orders found matching your criteria.
                                </td>
                            </tr>
                            <tr
                                v-for="order in orders?.data || []"
                                :key="order.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <!-- Order # & Date -->
                                <td class="py-3 px-4 align-top">
                                    <Link
                                        :href="route('admin.orders.show', order.id)"
                                        class="font-mono font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1.5"
                                    >
                                        {{ order.order_number }}
                                    </Link>
                                    <div class="text-xs text-slate-400 mt-1">{{ order.created_at }}</div>
                                    <div class="text-[11px] text-slate-400">{{ order.created_at_human }}</div>
                                </td>

                                <!-- Customer Details -->
                                <td class="py-3 px-4 align-top">
                                    <div class="font-semibold text-slate-800">{{ order.customer_name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        <a :href="`mailto:${order.customer_email}`" class="hover:text-blue-600 hover:underline">
                                            {{ order.customer_email }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-0.5 flex items-center gap-2">
                                        <span>{{ order.customer_phone }}</span>
                                        <a
                                            v-if="getWhatsAppLink(order)"
                                            :href="getWhatsAppLink(order)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded text-[11px] font-medium"
                                            title="Chat on WhatsApp"
                                        >
                                            <i class="fa-brands fa-whatsapp mr-1"></i> WhatsApp
                                        </a>
                                    </div>
                                </td>

                                <!-- Plan & Pricing -->
                                <td class="py-3 px-4 align-top">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ order.plan_name }}</span>
                                        <span v-if="order.plan_category" class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">
                                            {{ order.plan_category }}
                                        </span>
                                    </div>
                                    <div class="text-xs font-semibold text-blue-600 mt-1">
                                        {{ order.plan_price || 'Custom' }} <span class="text-slate-400 font-normal">{{ order.plan_period || '' }}</span>
                                        <span v-if="order.billing_cycle" class="text-slate-500 font-normal text-[11px]">
                                            ({{ order.billing_cycle }})
                                        </span>
                                    </div>
                                </td>

                                <!-- Domain / Server Location -->
                                <td class="py-3 px-4 align-top text-xs">
                                    <div v-if="order.domain_name" class="font-mono font-medium text-slate-700 flex items-center gap-1">
                                        <i class="fa-solid fa-globe text-slate-400"></i>
                                        {{ order.domain_name }}
                                    </div>
                                    <div v-else class="text-slate-400 italic">No domain provided</div>
                                    <div v-if="order.server_location" class="text-slate-500 mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-slate-400"></i>
                                        {{ order.server_location }}
                                    </div>
                                </td>

                                <!-- Status & Quick Action Dropdown -->
                                <td class="py-3 px-4 align-top">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border" :class="getStatusBadge(order.status)">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="getStatusDot(order.status)"></span>
                                            {{ statuses[order.status] || order.status }}
                                        </span>
                                    </div>
                                    <!-- Quick update select -->
                                    <select
                                        :value="order.status"
                                        @change="updateOrderStatus(order, $event.target.value)"
                                        class="text-xs py-1 px-2 bg-slate-50 border border-slate-200 rounded focus:ring-1 focus:ring-blue-500 cursor-pointer"
                                    >
                                        <option value="requested">Mark as Requested</option>
                                        <option value="pending">Mark as Pending</option>
                                        <option value="confirmed">Mark as Confirmed</option>
                                        <option value="cancelled">Mark as Cancelled</option>
                                    </select>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 align-top text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.orders.show', order.id)"
                                            class="px-2.5 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition"
                                            title="View Order Details"
                                        >
                                            View
                                        </Link>
                                        <button
                                            @click="deleteOrder(order)"
                                            class="px-2 py-1 text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-md transition"
                                            title="Delete Order"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="orders?.links && orders.links.length > 3" class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Showing {{ orders.from || 0 }} to {{ orders.to || 0 }} of {{ orders.total || 0 }} orders
                    </div>
                    <div class="flex gap-1">
                        <template v-for="(link, i) in orders.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 text-xs rounded border transition"
                                :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1 text-xs rounded border border-slate-100 text-slate-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
