<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// استيراد مكون الـ Chart من مكتبة vue3-apexcharts المتوافقة مع Vite
import VueApexCharts from 'vue3-apexcharts';
const apexchart = VueApexCharts;

// استقبال الـ Props القادمة من الباكند عبر Inertia
const props = defineProps({
    monthlyAndyearlyResult: Object,
    errors: Object,
    search: Object,
    notes: Object,
});

// متغير لمراقبة السنة المختارة حالياً لتحديث المخططات التفاعلية بناءً عليها
const selectedYear = ref(props.monthlyAndyearlyResult?.data?.[0]?.year || new Date().getFullYear());

// أسماء الأشهر باللغة العربية لمحور X في مخطط الأشهر
const arabicMonths = [
    "يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو",
    "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"
];

// أسماء الأرباع السنوية
const quarterlyLabels = ["الربع الأول (Q1)", "الربع الثاني (Q2)", "الربع الثالث (Q3)", "الربع الرابع (Q4)"];

// إعدادات الـ Toolbar الموحدة للمخططات لتفعيل أزرار الطباعة والتحميل الافتراضية
const baseToolbarConfig = {
    show: true,
    tools: {
        download: '🖨️ / 💾',
        selection: true,
        zoom: false,
        zoomin: false,
        zoomout: false,
        pan: false,
        reset: false
    }
};

// ----------------------------------------
// حساب الإجماليات الديناميكية للسنة المختارة
// ----------------------------------------
const selectedYearTotal = computed(() => {
    const yearsData = props.monthlyAndyearlyResult?.data || [];
    const currentYearData = yearsData.find(item => item.year === parseInt(selectedYear.value));
    return currentYearData ? currentYearData.total_year || 0 : 0;
});

const allYearsTotalSum = computed(() => {
    const yearsData = props.monthlyAndyearlyResult?.data || [];
    return yearsData.reduce((sum, item) => sum + (item.total_year || 0), 0);
});

const selectedYearDepartmentsTotal = computed(() => {
    return departmentDataList.value.reduce((sum, item) => sum + item.total, 0);
});

// ----------------------------------------
// 1. بيانات ومخطط مقارنة السنوات (Light Mode دائم)
// ----------------------------------------
const sortedYearsData = computed(() => {
    const yearsData = props.monthlyAndyearlyResult?.data || [];
    return [...yearsData].reverse();
});

const yearlyChart = computed(() => {
    return {
        series: [{
            name: 'إجمالي الطلبات السنوية',
            data: sortedYearsData.value.map(item => item.total_year || 0)
        }],
        options: {
            chart: { type: 'bar', fontFamily: 'Tajawal, sans-serif', toolbar: baseToolbarConfig },
            theme: { mode: 'light' },
            colors: ['#4F46E5'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%', dataLabels: { position: 'top' } } },
            xaxis: {
                categories: sortedYearsData.value.map(item => `سنة ${item.year}`),
                labels: { style: { colors: '#000000', fontWeight: 'bold' } }
            },
            yaxis: { labels: { style: { colors: '#000000' } } },
            dataLabels: {
                enabled: true,
                style: { colors: ['#000000'], fontSize: '13px', fontWeight: 'bold' },
                offsetY: -20,
                formatter: function (val) { return val.toLocaleString() + " طلب"; }
            }
        }
    };
});

// ----------------------------------------
// 2. بيانات ومخطط حركة الأشهر (Light Mode دائم)
// ----------------------------------------
const currentYearMonthsData = computed(() => {
    const yearsData = props.monthlyAndyearlyResult?.data || [];
    const currentYearData = yearsData.find(item => item.year === parseInt(selectedYear.value));
    return currentYearData ? currentYearData.months.map(m => m.total_month || 0) : Array(12).fill(0);
});

const monthlyChart = computed(() => {
    return {
        series: [{ name: 'الطلبات الشهرية', data: currentYearMonthsData.value }],
        options: {
            chart: { type: 'area', fontFamily: 'Tajawal, sans-serif', toolbar: baseToolbarConfig },
            theme: { mode: 'light' },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#059669'],
            xaxis: { categories: arabicMonths, labels: { style: { colors: '#000000', fontWeight: 'bold' } } },
            yaxis: { labels: { style: { colors: '#000000' } } },
            dataLabels: { enabled: false },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.01 } }
        }
    };
});

// ----------------------------------------
// 3. بيانات ومخطط التقارير الربع سنوية (Light Mode دائم)
// ----------------------------------------
const quarterlyDataList = computed(() => {
    const months = currentYearMonthsData.value;
    const q1 = (months[0] || 0) + (months[1] || 0) + (months[2] || 0);
    const q2 = (months[3] || 0) + (months[4] || 0) + (months[5] || 0);
    const q3 = (months[6] || 0) + (months[7] || 0) + (months[8] || 0);
    const q4 = (months[9] || 0) + (months[10] || 0) + (months[11] || 0);

    return [
        { name: quarterlyLabels[0], total: q1, monthsRange: "يناير - مارس" },
        { name: quarterlyLabels[1], total: q2, monthsRange: "أبريل - يونيو" },
        { name: quarterlyLabels[2], total: q3, monthsRange: "يوليو - سبتمبر" },
        { name: quarterlyLabels[3], total: q4, monthsRange: "أكتوبر - ديسمبر" }
    ];
});

const quarterlyChart = computed(() => {
    return {
        series: [{ name: 'إجمالي الربع السنوي', data: quarterlyDataList.value.map(q => q.total) }],
        options: {
            chart: { type: 'bar', fontFamily: 'Tajawal, sans-serif', toolbar: baseToolbarConfig },
            theme: { mode: 'light' },
            colors: ['#D97706'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', dataLabels: { position: 'top' } } },
            xaxis: { categories: quarterlyLabels, labels: { style: { colors: '#000000', fontWeight: 'bold' } } },
            yaxis: { labels: { style: { colors: '#000000' } } },
            dataLabels: {
                enabled: true,
                style: { colors: ['#000000'], fontSize: '13px', fontWeight: 'bold' },
                offsetY: -20,
                formatter: function (val) { return val.toLocaleString() + " طلب"; }
            }
        }
    };
});

// ----------------------------------------
// 4. بيانات ومخطط توزيع الأقسام الأفقي (Light Mode دائم)
// ----------------------------------------
const departmentDataList = computed(() => {
    const yearsData = props.monthlyAndyearlyResult?.data || [];
    const currentYearData = yearsData.find(item => item.year === parseInt(selectedYear.value));

    const deptTotals = {};
    if (currentYearData && currentYearData.months) {
        currentYearData.months.forEach(month => {
            if (month.departments) {
                month.departments.forEach(dept => {
                    if (!deptTotals[dept.name]) deptTotals[dept.name] = 0;
                    deptTotals[dept.name] += dept.stats?.total || 0;
                });
            }
        });
    }
    return Object.entries(deptTotals)
        .sort((a, b) => b[1] - a[1])
        .map(item => ({ name: item[0], total: item[1] }));
});

const departmentChart = computed(() => {
    const labels = departmentDataList.value.map(item => item.name);
    const data = departmentDataList.value.map(item => item.total);

    return {
        series: [{ name: 'إجمالي الطلبات بالقسم', data: data }],
        options: {
            chart: { type: 'bar', fontFamily: 'Tajawal, sans-serif', toolbar: baseToolbarConfig },
            theme: { mode: 'light' },
            plotOptions: {
                bar: {
                    barHeight: '85%', distributed: true, horizontal: true,
                    dataLabels: { position: 'top', hideOverflowingLabels: false }
                }
            },
            colors: ['#1E3A8A', '#065F46', '#92400E', '#991B1B', '#5B21B6', '#9D174D', '#115E59'],
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: { colors: ['#000000'], fontSize: '14px', fontWeight: 'bold' },
                formatter: function (val) { return val.toLocaleString() + " طلب"; },
                offsetX: 40,
            },
            xaxis: { categories: labels, labels: { show: true, style: { colors: '#000000' } } },
            yaxis: { labels: { style: { fontSize: '14px', fontWeight: 'bold', colors: '#000000' } } },
            legend: { show: false },
            tooltip: { theme: 'light', y: { formatter: function (val) { return val + " طلب" } } }
        }
    };
});

/**
 * دالة طباعة الصفحة وجداولها مباشرة
 */
const triggerGlobalPrint = () => {
    window.print();
};

const searchForm = useForm({
    request_search_key: "",
    department_search_key: "all",
    preserveState: true,
    replace: true,
});

const handlePageChange = (page) => {
    const form = useForm({
        page: page,
        request_search_key: searchForm.request_search_key || "",
        department_search_key: searchForm.department_search_key || "",
    });
    form.get('/waiting-for-search', { preserveState: true, replace: true });
};
</script>

<template>
    <AppLayout title="Dashboard">

        <!-- الخلفية العامة أصبحت بيضاء بالكامل -->
        <div class="py-12 px-4 bg-[#f8fafc] min-h-screen main-printable-wrapper" style="direction: rtl;">

            <!-- قسم فلاتر البحث الفاتح -->
            <div class="non-printable">
                <el-card class="box-card mb-4 light-card">
                    <div class="col-12 row d-flex flex-wrap">
                        <div class="col-3 column py-0">
                            <input @keyup="handlePageChange(1)" v-model="searchForm.request_search_key"
                                class="form-control form-control-sm light-input" placeholder="رقم المراجعة..." />
                        </div>
                        <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                            <select class="form-control form-control-sm light-input" @change="handlePageChange(1)"
                                v-model="searchForm.department_search_key">
                                <option selected="" value="all">الكل</option>
                                <option v-for="item in $page.props.Departments" :key="item.id" :value="item.id">
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </el-card>
            </div>

            <div class="non-printable">
                <div class="d-flex justify-content-between align-items-center w-full mb-4">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight m-0">لوحة الإحصائيات والتقارير الذكية
                    </h2>
                    <!-- زر طباعة مباشر -->
                    <button @click="triggerGlobalPrint"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg shadow-md d-flex align-items-center gap-2 transition">
                        🖨️ طباعة التقرير بالكامل
                    </button>
                </div>
            </div>

            <!-- الكرت الرئيسي الفاتح للمحتوى -->
            <el-card
                class="max-w-8xl mx-auto bg-white border border-gray-200 shadow-xl rounded-xl p-6 text-gray-900 printable-container">
                <div class="m-0">

                    <!-- قسم الكروت العلوية والفلاتر -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 non-printable">
                        <div
                            class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-6 rounded-xl shadow-md d-flex flex-column justify-between>">
                            <div>
                                <p class="text-sm opacity-90 font-bold mb-1">...الإجمالي الكلي العام لجميع الطلبات</p>
                                <h3 class="text-3xl font-extrabold">
                                    {{ props.monthlyAndyearlyResult?.grandTotal ?
                                        props.monthlyAndyearlyResult.grandTotal.toLocaleString() : 0 }}
                                </h3>
                            </div>
                            <span class="text-xs opacity-70 mt-4 block">منذ تاريخ التأسيس وحتى اليوم</span>
                        </div>

                        <div
                            class="bg-[#f1f5f9] p-6 rounded-xl border border-gray-200 d-flex flex-column justify-center">
                            <label class="block text-sm font-medium text-gray-700 mb-2">تصفية المخططات حسب
                                السنة:</label>
                            <select v-model="selectedYear"
                                class="form-control rounded-md bg-white border-gray-300 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="yearItem in props.monthlyAndyearlyResult?.data" :key="yearItem.year"
                                    :value="yearItem.year">
                                    إحصائيات سنة {{ yearItem.year }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- حاوية المخططات الفاتحة بالكامل -->
                    <div id="statistics-print-area" class="grid-container grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6">

                        <!-- 1. تقرير حجم الطلبات السنوية -->
                        <div
                            class="bg-white p-6 rounded-xl border border-gray-300 flex flex-column justify-between report-card-print">
                            <div>
                                <div
                                    class="d-flex justify-content-between align-items-center mb-4 border-b border-gray-300 pb-2 flex-wrapper-print">
                                    <h4 class="text-md font-bold text-gray-800 m-0 print-title">حجم الطلبات بين السنوات
                                        الإجمالية</h4>
                                    <span
                                        class="text-xs bg-indigo-100 text-indigo-800 px-2.5 py-1 rounded-md font-bold border border-indigo-200 badge-print">
                                        الإجمالي: {{ allYearsTotalSum.toLocaleString() }} طلب
                                    </span>
                                </div>
                                <div class="mb-4 chart-container-print">
                                    <apexchart height="300" type="bar" :options="yearlyChart.options"
                                        :series="yearlyChart.series"></apexchart>
                                </div>
                            </div>

                            <div>
                                <div class="overflow-x-auto border border-gray-300 rounded-lg mt-2 table-wrapper-print">
                                    <table class="w-full text-right text-sm data-table-print">
                                        <thead
                                            class="bg-gray-100 text-gray-700 border-b border-gray-300 header-table-print">
                                            <tr>
                                                <th class="p-3">السنة المالية</th>
                                                <th class="p-3 text-left">إجمالي الطلبات</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 body-table-print">
                                            <tr v-for="row in sortedYearsData" :key="row.year"
                                                class="hover:bg-gray-50 transition row-table-print">
                                                <td class="p-3 font-semibold text-gray-900">سنة {{ row.year }}</td>
                                                <td class="p-3 text-left font-bold text-indigo-600">{{ (row.total_year
                                                    || 0).toLocaleString() }} طلب</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div
                                    class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-300 d-flex justify-content-between align-items-center footer-table-print">
                                    <span class="text-sm font-bold text-gray-700">المجموع الكلي لكافة السنوات:</span>
                                    <span class="text-base font-extrabold text-indigo-600">{{
                                        allYearsTotalSum.toLocaleString() }} طلب</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. تقرير الأداء ربع السنوي -->
                        <div
                            class="bg-white p-6 rounded-xl border border-gray-300 flex flex-column justify-between report-card-print">
                            <div>
                                <div
                                    class="d-flex justify-content-between align-items-center mb-4 border-b border-gray-300 pb-2 flex-wrapper-print">
                                    <h4 class="text-md font-bold text-gray-800 m-0 print-title">توزيع الطلبات ربع السنوي
                                        لعام {{ selectedYear }}</h4>
                                    <span
                                        class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-md font-bold border border-amber-200 badge-print">
                                        الإجمالي: {{ selectedYearTotal.toLocaleString() }} طلب
                                    </span>
                                </div>
                                <div class="mb-4 chart-container-print">
                                    <apexchart height="300" type="bar" :options="quarterlyChart.options"
                                        :series="quarterlyChart.series"></apexchart>
                                </div>
                            </div>

                            <div>
                                <div class="overflow-x-auto border border-gray-300 rounded-lg mt-2 table-wrapper-print">
                                    <table class="w-full text-right text-sm data-table-print">
                                        <thead
                                            class="bg-gray-100 text-gray-700 border-b border-gray-300 header-table-print">
                                            <tr>
                                                <th class="p-3">الربع السنوي</th>
                                                <th class="p-3">النطاق الزمني</th>
                                                <th class="p-3 text-left">مجموع الإنتاجية</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 body-table-print">
                                            <tr v-for="(qRow, qIdx) in quarterlyDataList" :key="qIdx"
                                                class="hover:bg-gray-50 transition row-table-print">
                                                <td class="p-3 font-semibold text-gray-900">{{ qRow.name }}</td>
                                                <td class="p-3 text-gray-600 text-xs">{{ qRow.monthsRange }}</td>
                                                <td class="p-3 text-left font-bold text-amber-600">{{
                                                    qRow.total.toLocaleString() }} طلب</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div
                                    class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-300 d-flex justify-content-between align-items-center footer-table-print">
                                    <span class="text-sm font-bold text-gray-700">مجموع الأرباع لعام {{ selectedYear
                                    }}:</span>
                                    <span class="text-base font-extrabold text-amber-600">{{
                                        selectedYearTotal.toLocaleString() }} طلب</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. تقرير نمو الطلبات الشهري -->
                        <div
                            class="bg-white p-6 rounded-xl border border-gray-300 flex flex-column justify-between lg:col-span-2 report-card-print">
                            <div>
                                <div
                                    class="d-flex justify-content-between align-items-center mb-4 border-b border-gray-300 pb-2 flex-wrapper-print">
                                    <h4 class="text-md font-bold text-gray-800 m-0 print-title">تفصيل معدل الحركة ونمو
                                        الطلبات شهرياً لعام {{ selectedYear }}</h4>
                                    <span
                                        class="text-xs bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-md font-bold border border-emerald-200 badge-print">
                                        الإجمالي: {{ selectedYearTotal.toLocaleString() }} طلب
                                    </span>
                                </div>
                                <div class="mb-4 chart-container-print">
                                    <apexchart height="320" type="area" :options="monthlyChart.options"
                                        :series="monthlyChart.series"></apexchart>
                                </div>
                            </div>
                            <div
                                class="mt-1 p-3 bg-gray-50 rounded-lg border border-gray-300 d-flex justify-content-between align-items-center footer-table-print">
                                <span class="text-sm font-bold text-gray-700">مجموع أشهر سنة {{ selectedYear }}:</span>
                                <span class="text-base font-extrabold text-emerald-600">{{
                                    selectedYearTotal.toLocaleString() }} طلب</span>
                            </div>
                        </div>

                        <!-- 4. تقرير الأقسام الأفقي -->
                        <div class="bg-white p-6 rounded-xl border border-gray-300 lg:col-span-2 report-card-print">
                            <div
                                class="d-flex justify-content-between align-items-center mb-4 border-b border-gray-300 pb-2 flex-wrapper-print">
                                <h4 class="text-md font-bold text-gray-800 m-0 print-title">تحليل وتوزيع الطلبات حسب
                                    الأقسام لعام {{ selectedYear }}</h4>
                                <span
                                    class="text-xs bg-blue-100 text-blue-800 px-2.5 py-1 rounded-md font-bold border border-blue-200 badge-print">
                                    الإجمالي للأقسام: {{ selectedYearDepartmentsTotal.toLocaleString() }} طلب
                                </span>
                            </div>
                            <div class="mb-6 chart-container-print">
                                <apexchart height="650" type="bar" :options="departmentChart.options"
                                    :series="departmentChart.series"></apexchart>
                            </div>
                        </div>

                    </div>

                </div>
            </el-card>

        </div>
    </AppLayout>
</template>

<style>
/* تهيئة الفلاتر والمدخلات في الوضع الفاتح */
.light-card {
    background-color: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
}

.light-input {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
}

.light-input:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 1px #6366f1 !important;
}

.el-card {
    --el-card-bg-color: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
}

/* تخصيص مظهر قائمة خيارات الـ Toolbar في الوضع الفاتح */
.apexcharts-toolbar {
    z-index: 10 !important;
    top: -30px !important;
}

.apexcharts-menu {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
}

.apexcharts-menu-item:hover {
    background: #f1f5f9 !important;
    color: #000000 !important;
}

/* جداول Element UI الإضافية إن وُجدت */
.el-table__cell {
    background-color: #ffffff !important;
    color: #0f172a;
}

.el-table__row td {
    border-bottom: 1px solid #e2e8f0 !important;
}

/* ==========================================================================
    قواعد التنسيق عند الطباعة للورق 🖨️
   ========================================================================== */
@media print {

    body,
    html {
        background-color: #ffffff !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-family: 'Tajawal', sans-serif !important;
        direction: rtl !important;
    }

    .apexcharts-toolbar {
        display: none !important;
    }

    .report-card-print {
        width: 100% !important;
        border: 2px solid #000000 !important;
        box-shadow: none !important;
        page-break-inside: avoid !important;
    }

    .table-wrapper-print {
        border: 2px solid #000000 !important;
    }

    .header-table-print th,
    .body-table-print td {
        border: 1px solid #000000 !important;
        color: #000000 !important;
    }

    .non-printable,
    button,
    input,
    select {
        display: none !important;
    }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>