<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ElTable, ElTableColumn, ElPagination, ElDialog, ElMessage, ElMessageBox } from 'element-plus';
// import Inertia from '@inertiajs/inertia';
import { ref, watch } from 'vue';
// const form = $inertia;
import { useForm } from '@inertiajs/vue3';
const showDialogVisible = ref(false);
const editDialogVisible = ref(false);

const addDialogVisible = ref(false);

// Retrieve data from Inertia props
const props = defineProps({
    people: Object,
    errors: Object,
    search: Object,
    notes: Object,
});

const ShownDate = "";
const Editendata = "";


const editItemForm = new useForm({

    request_search_key: "",
    department_search_key: "",

    note: '',
});
const newItemForm = new useForm({

    request_search_key: "",
    department_search_key: "",


});

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

    ShownDate = item;
}
function editDialogVisibleFunction(item) {

    Editendata = item;
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
    department_search_key: "",


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
    form.page = page;

    form.request_search_key = searchForm.request_search_key;
    form.department_search_key = searchForm.department_search_key;


    form.get('/ready-requests', {
        preserveState: true, // Optional: Keeps form state
        replace: true,  // Optional: Replace history state instead of pushing
    });
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
                                <option selected="" value="all">الكل</option>
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
            <el-dialog :close-on-click-modal="false" v-model="addDialogVisible" title="نمودج ملف جديد" width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <div class="col-6 column py-2">
                        <label> نوع الملاحظه</label>

                        <select v-model="newItemForm.note_id" class="form-control " placeholder="  نوع الملاحظه...">
                            <option v-for="item in notes" :value="item.id">{{ item.name }}</option>
                        </select>
                        <label class="text-danger" v-if="errors.note_id">{{ errors.note_id
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>رقم الملف الجزئي</label>
                        <input v-model="newItemForm.partial_case_number" class="form-control "
                            placeholder="رقم الملف الجزئي..." />
                        <label class="text-danger" v-if="errors.partial_case_number">{{ errors.partial_case_number
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>رقم الملف الجنائي</label>
                        <input v-model="newItemForm.criminal_case_number" class="form-control "
                            placeholder="رقم القضية..." />
                        <label class="text-danger" v-if="errors.criminal_case_number">{{ errors.criminal_case_number
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>الإسم</label>
                        <input v-model="newItemForm.name" class="form-control " placeholder="الإسم..." />
                        <label class="text-danger" v-if="errors.name">{{ errors.name }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>إسم الأم</label>
                        <input v-model="newItemForm.mother_name" class="form-control " placeholder="اسم الأم..." />
                        <label class="text-danger" v-if="errors.mother_name">{{ errors.mother_name }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ الميلاد</label>
                        <input v-model="newItemForm.date_of_birth" class="form-control "
                            placeholder="تاريخ الميلاد..." />
                        <label class="text-danger" v-if="errors.date_of_birth">{{ errors.date_of_birth }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>المهنة</label>
                        <input v-model="newItemForm.request_search_key" class="form-control " placeholder="المهنة..." />
                        <label class="text-danger" v-if="errors.request_search_key">{{ errors.request_search_key
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>العنوان</label>
                        <input v-model="newItemForm.department_search_key" class="form-control "
                            placeholder="العنوان..." />
                        <label class="text-danger" v-if="errors.department_search_key">{{ errors.department_search_key
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>التهم</label>
                        <input v-model="newItemForm.charges" class="form-control " placeholder="التهم..." />
                        <label class="text-danger" v-if="errors.charges">{{ errors.charges }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ القضية</label>
                        <input v-model="newItemForm.judgment_date" class="form-control "
                            placeholder="تاريخ القضية..." />
                        <label class="text-danger" v-if="errors.judgment_date">{{ errors.judgment_date }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>منطوق الحكم</label>
                        <input v-model="newItemForm.judgment_operative" class="form-control "
                            placeholder="منطوق الحكم..." />
                        <label class="text-danger" v-if="errors.judgment_operative">{{ errors.judgment_operative
                        }}</label>
                    </div>


                    <div class="col-12 column py-2">
                        <label> ملاحظات</label>
                        <input v-model="newItemForm.note" class="form-control " placeholder=" ملاحظات..." />
                        <label class="text-danger" v-if="errors.note">{{ errors.note
                        }}</label>
                    </div>
                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button id="addDialogVisibleButton" @click="addDialogVisible = false">الغاء</button>
                        <button @click="addPerson" class="btn btn-success">حفظ</button>

                    </div>
                </template>
            </el-dialog>
            <el-dialog :close-on-click-modal="false" v-model="editDialogVisible" title="نمودج تعديل ملف " width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <div class="col-6 column py-2">
                        <label> نوع الملاحظة</label>
                        <select v-model="editItemForm.note_id" class="form-control ">
                            <option v-for="item in notes" :value="item.id">{{ item.name }}</option>
                        </select>
                        <label class="text-danger" v-if="errors.note_id">{{ errors.note_id
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>رقم الملف الجزئي</label>
                        <input v-model="editItemForm.partial_case_number" class="form-control " />
                        <label class="text-danger" v-if="errors.partial_case_number">{{ errors.partial_case_number
                        }}</label>
                    </div>


                    <div class="col-6 column py-2">
                        <label>رقم الملف الجنائي</label>
                        <input v-model="editItemForm.criminal_case_number" class="form-control " />
                        <label class="text-danger" v-if="errors.criminal_case_number">{{ errors.criminal_case_number
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>الإسم</label>
                        <input v-model="editItemForm.name" class="form-control " />
                        <label class="text-danger" v-if="errors.name">{{ errors.name }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>إسم الأم</label>
                        <input v-model="editItemForm.mother_name" class="form-control " />
                        <label class="text-danger" v-if="errors.mother_name">{{ errors.mother_name }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ الميلاد</label>
                        <input v-model="editItemForm.date_of_birth" class="form-control " />
                        <label class="text-danger" v-if="errors.date_of_birth">{{ errors.date_of_birth }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>المهنة</label>
                        <input v-model="editItemForm.request_search_key" class="form-control " />
                        <label class="text-danger" v-if="errors.request_search_key">{{ errors.request_search_key
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>العنوان</label>
                        <input v-model="editItemForm.department_search_key" class="form-control " />
                        <label class="text-danger" v-if="errors.department_search_key">{{ errors.department_search_key
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>التهم</label>
                        <input v-model="editItemForm.charges" class="form-control " />
                        <label class="text-danger" v-if="errors.charges">{{ errors.charges }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ القضية</label>
                        <input v-model="editItemForm.judgment_date" class="form-control " />
                        <label class="text-danger" v-if="errors.judgment_date">{{ errors.judgment_date }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>منطوق الحكم</label>
                        <input v-model="editItemForm.judgment_operative" class="form-control " />
                        <label class="text-danger" v-if="errors.judgment_operative">{{ errors.judgment_operative
                        }}</label>
                    </div>
                    <div class="col-12 column py-2">
                        <label> ملاحظلات</label>
                        <input v-model="editItemForm.note" class="form-control " />
                        <label class="text-danger" v-if="errors.note">{{ errors.note
                        }}</label>
                    </div>


                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button id='editDialogVisibleButton' @click="
                            editDialogVisible = false;

                        ">الغاء</button><button class="btn btn-primary" @click="
                            //editDialogVisible = false;
                            editPerson();
                        ">تعديل</button>

                    </div>
                </template>
            </el-dialog>

            <el-dialog v-model="showDialogVisible" title=" # " width="800">
                <div class="col-12 row d-flex flex-wrap">

                    <div class="col-6 column py-2">
                        <label>نوع الملاحظة</label>
                        <input :style="{ backgroundColor: ShownDate.person_note?.color_code }" disabled
                            class="form-control" />
                        <label class="text-danger" v-if="errors.note_id">{{ errors.note_id }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>رقم الملف الجزئي</label>
                        <input disabled v-model="ShownDate.partial_case_number" class="form-control " />
                        <label class="text-danger" v-if="errors.partial_case_number">{{ errors.partial_case_number
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>رقم الملف الجنائي</label>
                        <input disabled v-model="ShownDate.criminal_case_number" class="form-control " />
                        <label class="text-danger" v-if="errors.criminal_case_number">{{ errors.criminal_case_number
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>الإسم</label>
                        <input disabled v-model="ShownDate.name" class="form-control " />
                        <label class="text-danger" v-if="errors.name">{{ errors.name }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>إسم الأم</label>
                        <input disabled v-model="ShownDate.mother_name" class="form-control " />
                        <label class="text-danger" v-if="errors.mother_name">{{ errors.mother_name }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ الميلاد</label>
                        <input disabled v-model="ShownDate.date_of_birth" class="form-control " />
                        <label class="text-danger" v-if="errors.date_of_birth">{{ errors.date_of_birth }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>المهنة</label>
                        <input disabled v-model="ShownDate.request_search_key" class="form-control " />
                        <label class="text-danger" v-if="errors.request_search_key">{{ errors.request_search_key
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>العنوان</label>
                        <input disabled v-model="ShownDate.department_search_key" class="form-control " />
                        <label class="text-danger" v-if="errors.department_search_key">{{ errors.department_search_key
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>التهم</label>
                        <input disabled v-model="ShownDate.charges" class="form-control " />
                        <label class="text-danger" v-if="errors.charges">{{ errors.charges }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ القضية</label>
                        <input disabled v-model="ShownDate.judgment_date" class="form-control " />
                        <label class="text-danger" v-if="errors.judgment_date">{{ errors.judgment_date }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>منطوق الحكم</label>
                        <input disabled v-model="ShownDate.judgment_operative" class="form-control " />
                        <label class="text-danger" v-if="errors.judgment_operative">{{ errors.judgment_operative
                        }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>طريقة الإدخال</label>
                        <input disabled v-model="ShownDate.inserted_way" class="form-control " />
                        <label class="text-danger" v-if="errors.inserted_way">{{ errors.inserted_way
                        }}</label>
                    </div>

                    <div class="col-6 column py-2">
                        <label> المدخل </label>
                        <input disabled v-model="ShownDate.user.name" class="form-control " />

                    </div>
                    <div class="col-12 column py-2">
                        <label> ملاحظات</label>
                        <input disabled v-model="ShownDate.note" class="form-control " />
                        <label class="text-danger" v-if="errors.note">{{ errors.note
                        }}</label>
                    </div>
                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button @click="showDialogVisible = false">الغاء</button>

                    </div>
                </template>
            </el-dialog>
            <div class="max-w-8xl  bg-darksilver mx-auto bg-white shadow-sm rounded-lg p-6">



                <div class="m-0">

                    <!-- Pagination -->

                    <el-pagination layout="prev, pager, next" :total="people.total" :page-size="people.per_page"
                        :current-page="people.current_page" @current-change="handlePageChange" />


                </div>
                <div style="width: 100%; overflow-x: auto;">
                    <!-- Table -->
                    <el-table :data="people.data" border height="400" style="min-width: 800px">
                        >

                        <el-table-column prop="id" label="الصورة الشخصية" width="120" />

                        <el-table-column prop="libyan_person.full_name" width="250" label="الاسم الكامل" />
                        <el-table-column prop="libyan_person.last_name" width="120" label="اللقب" />
                        <el-table-column prop="libyan_person.mother_full_name" width="250" label="إسم الأم بالكامل" />
                        <!-- fix the el-table-column prop to show the place and date of birth in one column -->

                        <el-table-column label="مكان وتاريخ الميلاد" width="144">
                            <template #default="scope">
                                {{ scope.row.libyan_person.place_of_b }} - {{ function () {
                                    if (scope.row.libyan_person.date_of_b) {
                                        const date = new Date(scope.row.libyan_person.date_of_b);
                                        return date.getFullYear(); // Adjust the locale as needed
                                    }
                                    return '';
                                }() }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="libyan_person.job" width="150" label="المهنة" />
                        <el-table-column prop="libyan_person.job" label="العنوان" />
                        <el-table-column prop="libyan_person.ssn" width="120" label="الرقم الوطني" />
                        <el-table-column label="نوع اثبات الهوية" width="135">
                            <template #default="scope">
                                {{ scope.row.libyan_person?.doc_type == 1 ? 'بطاقة شخصية' :
                                    scope.row.libyan_person?.doc_type == 2 ? 'جواز سفر' : ''
                                }}
                            </template>
                        </el-table-column>
                        <el-table-column label="مكان إصدار الهوية" width="150">
                            <template #default="scope">
                                {{ scope.row.libyan_person.document_issued_place?.name || ''
                                }}
                            </template>
                        </el-table-column>
                        <el-table-column label="الجنس">
                            <template #default="scope">
                                {{ scope.row.libyan_person?.doc_type == 1 ? ' ذكر' :
                                    scope.row.libyan_person?.doc_type == 2 ? ' أنثى' : ''
                                }}
                            </template>
                        </el-table-column>



                        <!-- <el-table-column prop="judgment_date" label="#">
                            <template #default="scope">

                                <button class="btn btn-sm btn-primary "
                                    @click="showDialogVisible = true; ShownDate = scope.row">
                                    عرض الملف
                                </button>
                                <button class="btn btn-sm btn-primary " @click="editDialogVisible = true; Editendata = scope.row;
                                editItemForm.id = scope.row.id;
                                editItemForm.partial_case_number = scope.row.partial_case_number;
                                editItemForm.note_id = scope.row.note_id;

                                editItemForm.criminal_case_number = scope.row.criminal_case_number;
                                editItemForm.name = scope.row.name;
                                editItemForm.mother_name = scope.row.mother_name;
                                editItemForm.date_of_birth = scope.row.date_of_birth;
                                editItemForm.request_search_key = scope.row.request_search_key;
                                editItemForm.department_search_key = scope.row.department_search_key;
                                editItemForm.charges = scope.row.charges;
                                editItemForm.judgment_date = scope.row.judgment_date;
                                editItemForm.judgment_operative = scope.row.judgment_operative;
                                editItemForm.note = scope.row.note;
                                ">
                                    تعديل
                                </button>
                                <button v-if="$inertia.page.props.auth.user.type != 'user'"
                                    class="btn btn-sm btn-danger" @click="deltePerson(scope.row.id)">
                                    حدف
                                </button>

                            </template>
                        </el-table-column> -->

                    </el-table>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>
.el-table__cell {
    background-color: #191c24 !important;
    color: #ffffff;
    height: 10px !important;
    max-height: 10px !important;

}

.el-table__row td {
    border-bottom: 1px solid white !important;
}

tr {

    border-bottom: 1px solid white !important;
}

table {
    border-bottom: 1px solid white !important;

}
</style>