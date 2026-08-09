<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ElPagination, ElDialog, ElMessage, ElMessageBox } from 'element-plus';
// import Inertia from '@inertiajs/inertia';
import { ref, watch } from 'vue';
// const form = $inertia;
import { useForm } from '@inertiajs/vue3';
import { el, it } from 'element-plus/es/locale/index.mjs';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
const showDialogVisible = ref(false);
const showPrintDialogVisible = ref(false);
const editDialogVisible = ref(false);

const addDialogVisible = ref(false);
import { faInbox } from '@fortawesome/free-solid-svg-icons'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faInbox);

const checkAll = ref(false);
const selectedItems = ref([]);


// print cart functions

// توليد بيانات كثيرة للتجربة
const PrintTemplate = `
   <html lang="ar">
   <head>
     <meta charset="UTF-8">
     <title>طباعة جدول</title>
    </head>
      <style>
    body {
      font-family: Arial, sans-serif;
      direction: rtl;
      padding: 20px;
    }

    button {
      margin-bottom: 20px;
      padding: 10px 20px;
      cursor: pointer;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
    }

    thead {
      background: #eee;
    }

    /* ================= PRINT ================= */
    @media print {

      button {
        display: none;
      }

      table {
        width: 100%;
        border-collapse: collapse;
      }

      thead {
        display: table-header-group; /* تكرار الهيدر */
      }

      tfoot {
        display: table-footer-group;
      }

      tr {
        page-break-inside: avoid; /* منع تقطيع الصف */
      }

      td, th {
        border: 1px solid #000;
        padding: 8px;
      }

      @page {
        margin: 20mm;
      }

      /* رقم الصفحة */
      body::after {
        content: "صفحة " counter(page);
        position: fixed;
        bottom: 10px;
        left: 20px;
        font-size: 30px;
      }
    }
  </style>
  
  `;
const tbody = document.getElementById("printTableBody");

function printTable() {

    const table = document.querySelector("table");

    if (!table) return;

    const printStyles = `
        body {
            font-family: Arial;
            direction: rtl;
            padding: 10px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
            background: #bca295;
            color: #000;
        }
        
        th, td {
            border: 1px solid #999;
            padding: 8px;

            text-align: center;
            font-size: 18px;
            color: #000;
        }
        td{
        padding-top: 20px;
        padding-bottom: 20px;
        }
        p {
            margin: 0;
            padding: 0;
        }
        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        tbody tr:nth-child(even) {
            background: #eeeeee;
        }

        tr {
            page-break-inside: avoid;
        }

        @page {
            margin: 8px;
        }

        body::after {
            content: "صفحة " counter(page);
            position: fixed;
            bottom: 10px;
            left: 20px;
            font-size: 12px;
            color: #555;
        }
    `;

    const iframe = document.createElement("iframe");

    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "0";

    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;

    doc.open();
    doc.write(`
        <html lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>طباعة الجدول</title>
            <style>${printStyles}</style>
        </head>
        <body>
            ${table.outerHTML}
        </body>
        </html>
    `);
    doc.close();

    iframe.onload = function () {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    };
}


// print cart functions

function selectAll(value) {
    props.people.data.forEach(item => {
        item.checked = value;
    });
}
// Retrieve data from Inertia props
const props = defineProps({
    people: Object,
    errors: Object,
    search: Object,
    notes: Object,
    count: Number,
    last_page: Number,
});
props.people.data.forEach(item => {
    // push new property to each item to track checkbox state
    item.checked = false;
});



const DialogData = "";
// const Editendata = "";


const editItemForm = new useForm({

    request_search_key: "",
    department_search_key: "",

    note: '',
});
const newItemForm = new useForm({

    request_search_key: "",
    department_search_key: "",


});



function addToPrintCart(item) {
    // looping through the people data to find checked items
    props.people.data.forEach(item => {
        if (item.checked && !selectedItems.value.includes(item)) {
            selectedItems.value.push(item);
        }
    });
}

function deletePrintCart() {
    selectedItems.value = [];
    props.people.data.forEach(item => {
        item.checked = false;
    });
}
function getTextColor(color) {
    // حساب التباين البسيط بناءً على اللون
    if (!color) return '#000000'; // نص أسود إذا لم يكن هناك لون

    // تحويل اللون من هيكس إلى RGB
    const rgb = this.hexToRgb(color);
    if (!rgb) return '#000000'; // إذا كان اللون غير صالح، استخدام النص الأسود

    // حساب التباين
    const brightness = 0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b;
    return brightness < 128 ? '#FFFFFF' : '#000000'; // إذا كان اللون غامق، استخدم نصًا أبيض، وإذا كان فاتحًا استخدم نصًا أسود
}

// دالة لتحويل اللون من هيكس إلى RGB
function hexToRgb(hex) {
    const match = hex.match(/^#([a-fA-F0-9]{6})$/);
    if (!match) return null;

    const r = parseInt(match[1].substr(0, 2), 16);
    const g = parseInt(match[1].substr(2, 2), 16);
    const b = parseInt(match[1].substr(4, 2), 16);

    return { r, g, b };
}

function showDialogVisibleFunction(item) {

    DialogData = item;
}
function editDialogVisibleFunction(item) {

    // Editendata = item;
}
function openPersonDialog(item) {
    // this.$inertia.visit(`/person-data/${item.libyan_person_id}`);
    showDialogVisible = true;
    DialogData = item;
}
function goToUrl(url) {
    this.$inertia.visit(url)
}
function goToPage(page) {
    this.$inertia.visit(`?page=${page}`)
}

function celarErrors() {
    Object.keys(props.errors).forEach(key => delete props.errors[key]); // Clears the object
}
function deltePerson(id) {
    ElMessageBox.confirm(
        'هل أنت متأكد من أنك تريد حذف هذا العنصر؟',
        'تأكيد الحذف',
        {
            confirmButtonText: 'نعم',
            cancelButtonText: 'إلغاء',
            type: 'warning',
        }
    )
        .then(() => {
            newItemForm.delete(`/delete-person/${id}`, {

                preserveState: true, // Optional: Keeps form state
                replace: true,  // Optional: Replace history state instead of pushing
                onSuccess: () => {

                    console.log('Data saved successfully.');

                    // document.getElementById('addDialogVisibleButton').click();
                    ElMessage({
                        message: 'تم الحدف بنجاح',
                        type: 'success',
                        center: true, // يجعل الرسالة في المنتصف
                    })
                },
                onError: (errors) => {
                    console.error('Validation errors:', errors);
                    // alert('Failed to save data. Please check the form and try again.');
                },
                onFinish: () => {
                    console.log('Request finished.');
                }
            });






        });

}


//
function editPerson() {


    editItemForm.post('/edit-person-data', {

        onSuccess: () => {

            console.log('Data saved successfully.');

            document.getElementById('editDialogVisibleButton').click();
            ElMessage({
                message: 'تمت التعديل بنجاح',
                type: 'success',
                center: true, // يجعل الرسالة في المنتصف
            })
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            // alert('Failed to save data. Please check the form and try again.');
        },
        onFinish: () => {
            console.log('Request finished.');
        }
    });
}


function addPerson() {

    newItemForm.post('/save-person-data', {
        onSuccess: () => {

            console.log('Data saved successfully.');

            document.getElementById('addDialogVisibleButton').click();
            ElMessage({
                message: 'تمت الإضافة بنجاح',
                type: 'success',
                center: true, // يجعل الرسالة في المنتصف
            })
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            // alert('Failed to save data. Please check the form and try again.');
        },
        onFinish: () => {
            console.log('Request finished.');
        }
    });
}



// Function to upload the file


const searchForm = new useForm({

    request_search_key: "",
    department_search_key: "all",


    preserveState: true, // Optional: Keeps form state
    replace: true,  // Optional: Replace history state instead of pushing

});

const form = new useForm({
    page: 1,

    request_search_key: searchForm.request_search_key || "",
    department_search_key: searchForm.department_search_key || "",




});
// Handle pagination
const handlePageChange = (page) => {

    checkAll.value = false;
    form.page = page;

    form.request_search_key = searchForm.request_search_key;
    form.department_search_key = searchForm.department_search_key;


    form.get('/rejected-personal-pictures-index', {
        preserveState: true, // Optional: Keeps form state
        replace: true,  // Optional: Replace history state instead of pushing
        onSuccess: () => {
            props.people.data.forEach(item => {
                item.checked = false; // Add a checked property to each item
            });
        },
    },

    );
};





</script>
<template>
    <AppLayout title="Dashboard">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>
        <div class="py-12 px-4" style="direction: rtl;">

            <div>
                <el-card class="box-card mb-2" v-if="$inertia.page.props.auth.user.type == 'super_admin'">




                    <div class="col-12 row d-flex flex-wrap ">





                        <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">

                            <input @keyup="handlePageChange(1)" v-model="searchForm.request_search_key"
                                class="form-control form-control-sm " placeholder="رقم المراجعة..." />

                        </div>
                        <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                            <select class="form-control form-control-sm " @change="handlePageChange(1)"
                                v-model="searchForm.department_search_key">
                                <option value="all">الكل</option>
                                <option v-for="item in $page.props.Departments" :value="item.id"> {{ item.name }}
                                </option>

                            </select>
                        </div>


                    </div>
                </el-card>

            </div>

            <!-- <div class="col-12 p-0 d-flex justify-content-end" v-if="$inertia.page.props.auth.user.type != 'user'">
                <button plain @click="addDialogVisible = true; celarErrors(); newItemForm.reset()"
                    class="btn btn-sm btn-success ">
                    + ملف جديد
                </button>
            </div> -->
            <el-dialog v-model="showPrintDialogVisible" title="سلة الطباعة" width="85%">
                <button class="btn btn-primary mb-2" @click="printTable()">طباعة</button>
                <div class="col-12 row">
                    <table style="font-size: 0.5rem !important;" id="printTableBody">
                        <thead>
                            <tr>
                                <th>

                                    #
                                </th>
                                <th class="print_header_column">تاريخ الطلب </th>
                                <th class="print_header_column">الاسم الكامل</th>
                                <th class="print_header_column">اللقب</th>
                                <th class="print_header_column">إسم الأم بالكامل</th>
                                <th class="print_header_column">الرقم الوطني</th>
                                <th class="print_header_column"> اثبات الهوية</th>
                                <th class="print_header_column"> ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in selectedItems" :key="item.id">
                                <td>
                                    {{ selectedItems.indexOf(item) + 1 }}
                                </td>
                                <td>
                                    <!-- Y-m-d -->
                                    <label style="display: block;">
                                        {{ new Date(item.l_req_date).toLocaleDateString('sv-SE') }}


                                    </label>

                                    <label>( {{ item.id }} )</label>
                                </td>
                                <td class="print_header_column">{{ item.libyan_person.full_name }}</td>
                                <td class="print_header_column">{{ item.libyan_person.last_name }}</td>
                                <td class="print_header_column">{{ item.libyan_person.mother_full_name }}</td>

                                <td class="print_header_column">{{ item.libyan_person.ssn }}</td>
                                <td class="print_header_column">
                                    <p class="m-0 p-0">
                                        {{ item.libyan_person?.doc_type == 1 ? ' (ب / ش) ' :
                                            item.libyan_person?.doc_type == 2
                                                ? ' (ج/ س)' : '' }}
                                        -

                                        {{ item.libyan_person.document_issued_place?.name || '' }}

                                    </p>


                                    <p class="m-0 p-0">
                                        {{ item.libyan_person.passport_no }}
                                    </p>

                                </td>
                                <td class="m-0 p-0 " style="width: 15%;">
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

            </el-dialog>
            <el-dialog v-model="showDialogVisible" :close-on-click-modal="false" :close-on-press-escape="false"
                title=" بيانات الطلب " width="800" :class="'text-white'">

                <div class=" col-12 row d-flex flex-wrap">
                    <div class="col-3 d-flex justify-content-center">
                        <img class=" p-1 " style=" height: 200px;width: 200px;border-radius: unset "
                            :src="DialogData.libyan_person?.personal_picture" alt=" image">
                    </div>
                    <div class="col-9 row d-flex flex-wrap ">
                        <div class="col-6 pt-2">
                            <strong>الاسم الكامل:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.full_name }}</p>
                        </div>
                        <div class="col-6 pt-2">
                            <strong>اللقب:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.last_name }}</p>

                        </div>
                        <div class="col-6 pt-2">
                            <strong>إسم الأم بالكامل:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.mother_full_name }}</p>
                        </div>
                        <div class="col-6 pt-2">
                            <strong>مكان وتاريخ الميلاد:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.place_of_b }} - {{ DialogData.libyan_person?.date_of_b
                                    ? DialogData.libyan_person.date_of_b : '' }}</p>
                        </div>
                        <div class=" col-6 pt-2">
                            <strong>المهنة:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.job }}</p>
                        </div>
                        <div class="col-6 pt-2">
                            <strong>العنوان:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.address.name }}</p>
                        </div>
                        <div class="col-6 pt-2">
                            <strong>الرقم الوطني:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.ssn }}</p>
                        </div>
                        <div class="col-6 pt-2">
                            <strong>نوع اثبات الهوية:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.doc_type == 1 ? 'بطاقة شخصية' :
                                    DialogData.libyan_person?.doc_type == 2
                                        ? 'جواز سفر' : '' }}</p>
                        </div>

                        <div class="col-6 pt-2">
                            <strong>مكان إصدار الهوية:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.document_issued_place?.name || '' }}</p>
                        </div>

                        <div class="col-6 pt-2">
                            <strong>الجنس:</strong>
                            <p class="gray-bg p-1 rounded">
                                {{ DialogData.libyan_person?.sex == 1 ? 'ذكر' :
                                    'أنثى' }}</p>
                        </div>

                    </div>
                </div>


                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button @click="showDialogVisible = false">الغاء</button>

                    </div>
                </template>
            </el-dialog>
            <div class="max-w-8xl  bg-darksilver mx-auto bg-white shadow-sm rounded-lg p-6">



                <div class="m-0 d-flex col-12 d-flex justify-content-between
                ">
                    <div class="col-2 d-flex justify-content-start">
                        <button class="btn btn-primary btn-sm" :disabled="!people.prev_page_url"
                            @click="handlePageChange(1)">
                            الأولى
                        </button>
                        <el-pagination background layout="prev, next" :current-page="form.page" :page-size="1"
                            :total="people.current_page + (people.next_page_url ? 1 : people.current_page)"
                            @current-change="handlePageChange" />
                    </div>
                    <div class="col-3 d-flex justify-content-center">
                        <button class="btn btn-sm  btn-outline-danger" @click="deletePrintCart()">
                            <FontAwesomeIcon :icon="'trash'" class="danger-icon p-0 m-0" />


                        </button>
                        <button class="btn btn-sm btn-outline-primary" @click="showPrintDialogVisible = true">
                            طباعه السلة
                            <FontAwesomeIcon :icon="'inbox'" class=" p-0 m-0" />

                        </button>
                        <button class="btn btn-sm btn-outline-success" @click="addToPrintCart()">
                            +
                        </button>
                    </div>



                </div>



                <div>
                    <div style="max-height: 600px; overflow: auto;">

                        <table class="table table-dark table-striped text-nowrap">
                            <thead>
                                <tr>
                                    <th>
                                        <el-checkbox v-model="checkAll" @change="selectAll(checkAll)"></el-checkbox>
                                    </th>
                                    <th>الصورة </th>
                                    <th>تاريخ الطلب</th>
                                    <th>الاسم الكامل</th>
                                    <th>اللقب</th>
                                    <th>إسم الأم بالكامل</th>
                                    <th>مكان وتاريخ الميلاد</th>
                                    <th>المهنة</th>
                                    <th>العنوان</th>
                                    <th>الرقم الوطني</th>
                                    <th>نوع اثبات الهوية</th>
                                    <th>مكان إصدار الهوية</th>
                                    <th>الجنس</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in people.data" :key="item.id">
                                    <td>
                                        <!-- <input type="checkbox" v-model="item.checked" @change="checkItem(item)"> -->
                                        <el-checkbox v-model="item.checked"></el-checkbox>
                                    </td>
                                    <td> <button class="btn btn-primary p-0"
                                            @click="showDialogVisible = true; DialogData = item">
                                            <img class=" p-1 " style=" height: 75px;width: 75px;border-radius: unset "
                                                src="" alt=" image">
                                        </button>

                                    </td>
                                    <td>
                                        <!-- Y-m-d -->
                                        <label style="display: block;">
                                            {{ new Date(item.l_req_date).toLocaleDateString('sv-SE') }}


                                        </label>

                                        <label>( {{ item.id }} )</label>
                                    </td>
                                    <td>{{ item.libyan_person?.full_name }}</td>
                                    <td>{{ item.libyan_person?.last_name }}</td>
                                    <td>{{ item.libyan_person?.mother_full_name }}</td>
                                    <td>{{ item.libyan_person?.place_of_b }} - {{ item.libyan_person?.date_of_b
                                        ? new
                                            Date(item.libyan_person.date_of_b).getFullYear() : '' }}</td>
                                    <td>{{ item.libyan_person?.job }}</td>
                                    <td>{{ item.libyan_person?.address.name }}</td>
                                    <td>{{ item.libyan_person?.ssn }}</td>
                                    <td>{{ item.libyan_person?.doc_type == 1 ? 'بطاقة شخصية' :
                                        item.libyan_person?.doc_type == 2
                                            ? 'جواز سفر' : '' }}</td>
                                    <td>{{ item.libyan_person?.document_issued_place?.name || '' }}</td>
                                    <td v-if="item.libyan_person.sex ? 1 : 0">{{ item.libyan_person.sex == 1 ? 'ذكر'
                                        :
                                        'أنثى' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>
.el-table__cell {

    color: #ffffff;
    height: 10px !important;
    max-height: 10px !important;

}

.el-table__row td {
    border-bottom: 1px solid rgb(53, 53, 53) !important;
}

tr {

    border-bottom: 1px solid rgb(53, 53, 53) !important;
}

table {
    border-bottom: 1px solid rgb(53, 53, 53) !important;
}

/* ::v-deep(tr:nth-child(odd)) {
    background-color: #ffffff !important;
}

::v-deep(tr:nth-child(even)) {
    background-color: black !important;
} */


.dark-table {
    --el-table-bg-color: ##191c24;
    --el-table-tr-bg-color: #191c24;
    --el-table-row-hover-bg-color: #2a2a2a;
    --el-table-header-bg-color: #2a2a2a;
    --el-table-border-color: #333;
    color: #eee;
}

.el-table--striped .el-table__body tr.el-table__row--striped td.el-table__cell {
    background-color: #131313;
}

/* Stripe rows */
/* .dark-table .el-table__row:nth-child(2n) {
    background-color: #252525;
} */
.auto-width-table .el-table__body,
.auto-width-table .el-table__header {
    width: max-content !important;
}

.auto-width-table .cell {
    white-space: nowrap;
}

td,
th {
    white-space: nowrap;
    font-size: 1.1rem !important;
    font-weight: bold;
    padding-left: 0px !important;
}

td,
th {
    vertical-align: middle;
}

.print_header_column {
    font-size: 0.85rem !important;
    height: 10px;
}
</style>