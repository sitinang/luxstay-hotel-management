<script setup>
const props = defineProps({
    financialSummary: Object,
    transactions: Array
});

const emit = defineEmits(['openTransactionModal']);

const formatMoney = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div class="space-y-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ $t('income') }}</h4>
                </div>
                <div class="text-3xl font-serif font-bold text-slate-900">{{ formatMoney(financialSummary.income) }}</div>
            </div>
            
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-arrow-trend-down"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ $t('expense') }}</h4>
                </div>
                <div class="text-3xl font-serif font-bold text-slate-900">{{ formatMoney(financialSummary.expense) }}</div>
            </div>

            <div class="bg-navy-900 p-8 rounded-[2.5rem] shadow-xl shadow-navy-900/20 text-white">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 text-gold-400 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white/60 uppercase tracking-wider">{{ $t('net_profit') }}</h4>
                </div>
                <div class="text-3xl font-serif font-bold text-gold-400">{{ formatMoney(financialSummary.income - financialSummary.expense) }}</div>
            </div>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 p-8">
            <div class="flex justify-between items-center gap-6">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-slate-900">{{ $t('transaction_history') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Record of hotel operational income and expenses.</p>
                </div>
                <div class="btn-premium-container btn-navy">
                    <button @click="$emit('openTransactionModal')" class="btn-premium-inner px-6 py-2.5 text-sm font-bold whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-2 text-gold-400"></i> {{ $t('add_transaction') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="px-8 py-5">{{ $t('date') }}</th>
                            <th class="px-8 py-5">{{ $t('category') }}</th>
                            <th class="px-8 py-5">{{ $t('description') }}</th>
                            <th class="px-8 py-5">{{ $t('type') }}</th>
                            <th class="px-8 py-5 text-right">{{ $t('amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5 text-slate-500 font-medium">
                                {{ new Date(tx.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-700 border border-slate-200 uppercase tracking-wider">{{ tx.category }}</span>
                            </td>
                            <td class="px-8 py-5 text-slate-600 italic">
                                {{ tx.description || '-' }}
                            </td>
                            <td class="px-8 py-5">
                                <span v-if="tx.type === 'income'" class="text-emerald-600 font-bold uppercase text-[10px] tracking-widest flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-[6px]"></i> {{ $t('income') }}
                                </span>
                                <span v-else class="text-rose-600 font-bold uppercase text-[10px] tracking-widest flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-[6px]"></i> {{ $t('expense') }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right font-bold" :class="tx.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">
                                {{ tx.type === 'income' ? '+' : '-' }} {{ formatMoney(tx.amount) }}
                            </td>
                        </tr>
                        <tr v-if="transactions.length === 0">
                            <td colspan="5" class="py-20 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-5xl mb-4 block"></i>
                                <p class="font-bold text-lg text-slate-500 font-serif">{{ $t('no_transactions_found') }}</p>
                                <p class="text-sm">{{ $t('no_transactions_desc') }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
