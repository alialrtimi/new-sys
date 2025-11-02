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

const searchFormVisable = ref(false);

const editItemForm = new useForm({
    id: "",
    partial_case_number: "",
    note_id: '',
    criminal_case_number: "",
    name: "",
    mother_name: "",
    date_of_birth: "",
    job: "",
    address: "",
    charges: "",
    judgment_date: "",
    judgment_operative: "",
    note: '',
});
const newItemForm = new useForm({
    partial_case_number: "",
    note_id: '',
    criminal_case_number: "",
    name: "",
    mother_name: "",
    date_of_birth: "",
    job: "",
    address: "",
    charges: "",
    judgment_date: "",
    judgment_operative: "",
    note: '',

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
const excelForm = useForm({
    file: null, // File placeholder
});
const handleFileChange = (file) => {
    excelForm.file = file.raw; // Store the raw file in the form
};
// Function to upload the file
const uploadFile = () => {
    if (!excelForm.file) {
        ElMessage({
            message: 'Please select a file before uploading.',
            type: 'warning',
        });
        return;
    }

    excelForm.post('/upload-excel', {
        forceFormData: true, // Ensures FormData is used
        onSuccess: () => {
            ElMessage({
                message: 'File uploaded successfully!',
                type: 'success',
            });
            excelForm.reset(); // Reset the form
        },
        onError: () => {
            ElMessage({
                message: 'An error occurred while uploading the file.',
                type: 'error',
            });
        },
    });
};

const searchForm = new useForm({
    partial_case_number: "",
    note_id: '',
    criminal_case_number: "",
    name: "",
    mother_name: "",
    date_of_birth: "",
    job: "",
    address: "",
    charges: "",
    judgment_date: "",
    judgment_operative: "",
    inserted_way: "",


    preserveState: true, // Optional: Keeps form state
    replace: true,  // Optional: Replace history state instead of pushing
    note: "",
});

const form = new useForm({
    page: 1,
    partial_case_number: searchForm.partial_case_number || "",
    note_id: searchForm.note_id || "",

    criminal_case_number: searchForm.criminal_case_number || "",
    name: searchForm.name || "",
    mother_name: searchForm.mother_name || "",
    date_of_birth: searchForm.date_of_birth || "",
    job: searchForm.job || "",
    address: searchForm.address || "",
    charges: searchForm.charges || "",
    judgment_date: searchForm.judgment_date || "",
    judgment_operative: searchForm.judgment_operative || "",
    inserted_way: searchForm.inserted_way || "",
    note: searchForm.note || "",



});
// Handle pagination
const handlePageChange = (page) => {
    form.page = page;
    form.partial_case_number = searchForm.partial_case_number;
    form.note_id = searchForm.note_id;

    form.criminal_case_number = searchForm.criminal_case_number;
    form.name = searchForm.name;
    form.mother_name = searchForm.mother_name;
    form.date_of_birth = searchForm.date_of_birth;
    form.job = searchForm.job;
    form.address = searchForm.address;
    form.charges = searchForm.charges;
    form.judgment_date = searchForm.judgment_date;
    form.judgment_operative = searchForm.judgment_operative;
    form.inserted_way = searchForm.inserted_way;
    form.note = searchForm.note;

    form.get('/dashboard', {
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
                <el-card class="box-card" v-if="$inertia.page.props.auth.user.type == 'super_admin'">
                    <h2>Upload Excel File</h2>
                    <el-upload action="#" :on-change="handleFileChange" :show-file-list="false" :auto-upload="false">
                        <el-button type="primary">Choose File</el-button>
                    </el-upload>

                    <div v-if="excelForm.file">
                        <p><strong>Selected File:</strong> {{ excelForm.file.name }}</p>
                        <el-button type="success" @click="uploadFile" :loading="excelForm.processing">
                            Upload
                        </el-button>
                    </div>
                </el-card>
            </div>
            <div class="col-12 p-0 d-flex justify-content-end" v-if="$inertia.page.props.auth.user.type != 'user'">
                <button plain @click="addDialogVisible = true; celarErrors(); newItemForm.reset()"
                    class="btn btn-sm btn-success ">
                    + ملف جديد
                </button>
            </div>
            <el-dialog :close-on-click-modal="false" v-model="addDialogVisible" title="نمودج ملف جديد" width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <div class="col-6 column py-2">
                        <label> نوع الملاحظه</label>

                        <select v-model="newItemForm.note_id" class="form-control " placeholder="  نوع الملاحظه...">
                            <option v-for="item in notes " :value="item.id">{{ item.name }}</option>
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
                        <input v-model="newItemForm.job" class="form-control " placeholder="المهنة..." />
                        <label class="text-danger" v-if="errors.job">{{ errors.job }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>العنوان</label>
                        <input v-model="newItemForm.address" class="form-control " placeholder="العنوان..." />
                        <label class="text-danger" v-if="errors.address">{{ errors.address }}</label>
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
                        <input v-model="editItemForm.job" class="form-control " />
                        <label class="text-danger" v-if="errors.job">{{ errors.job }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>العنوان</label>
                        <input v-model="editItemForm.address" class="form-control " />
                        <label class="text-danger" v-if="errors.address">{{ errors.address }}</label>
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
                        <input disabled v-model="ShownDate.job" class="form-control " />
                        <label class="text-danger" v-if="errors.job">{{ errors.job }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>العنوان</label>
                        <input disabled v-model="ShownDate.address" class="form-control " />
                        <label class="text-danger" v-if="errors.address">{{ errors.address }}</label>
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
            <div class="max-w-7xl mx-auto bg-white shadow-sm rounded-lg p-6">
                <button @click="searchFormVisable = true" v-if="!searchFormVisable" class="btn bnt-sm btn-info">
                    فتح نمودج البحث
                </button><button @click="searchFormVisable = false" v-if="searchFormVisable"
                    class="btn bnt-sm btn-info">
                    اغلاق نمودج البحث
                </button>
                <button @click="searchForm.reset(); handlePageChange(1)" v-if="searchFormVisable"
                    class="btn bnt-sm btn-info">
                    تفريغ حقول البحث
                </button>
                <div class="col-12 row d-flex flex-wrap " v-if="searchFormVisable">

                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label> نوع الملاحظة</label>
                        <select @change="handlePageChange(1)" v-model="searchForm.note_id"
                            class="form-control form-control-sm " placeholder="  نوع الملاحظه...">
                            <option v-for="item in notes" :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>رقم الملف الجزئي</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.partial_case_number"
                            class="form-control form-control-sm " placeholder="رقم الملف الجزئي..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>رقم الملف الجنائي</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.criminal_case_number"
                            class="form-control form-control-sm " placeholder="رقم القضية..." />
                    </div>
                    <div class="col-3 column py-0">
                        <label>الإسم</label>
                        <input @keyup=" handlePageChange(1)" v-model="searchForm.name"
                            class="form-control form-control-sm " placeholder="الإسم..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>إسم الأم</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.mother_name"
                            class="form-control form-control-sm " placeholder="اسم الأم..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>تاريخ الميلاد</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.date_of_birth"
                            class="form-control form-control-sm " placeholder="تاريخ الميلاد..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>المهنة</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.job"
                            class="form-control form-control-sm " placeholder="المهنة..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>العنوان</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.address"
                            class="form-control form-control-sm " placeholder="العنوان..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>التهم</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.charges"
                            class="form-control form-control-sm " placeholder="التهم..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>تاريخ القضية</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.judgment_date"
                            class="form-control form-control-sm " placeholder="تاريخ القضية..." />
                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label>منطوق الحكم</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.judgment_operative"
                            class="form-control form-control-sm " placeholder="منطوق الحكم..." />

                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label> طريقة الإدخال</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.inserted_way"
                            class="form-control form-control-sm " placeholder=" طريقة الإدخال..." />

                    </div>
                    <div class="col-3 column py-0" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <label> ملاحظات</label>
                        <input @keyup="handlePageChange(1)" v-model="searchForm.note"
                            class="form-control form-control-sm " placeholder=" ملاحظات ..." />

                    </div>
                </div>

                <div class="mt-4">

                    <!-- Pagination -->

                    <el-pagination v-if="$inertia.page.props.auth.user.type != 'user'" layout="prev, pager, next"
                        :total="people.total" :page-size="people.per_page" :current-page="people.current_page"
                        @current-change="handlePageChange" />
                </div>

                <!-- Table -->
                <el-table :data="people.data" border style="width: 100%;" :row-style="({ row }) => ({
                    background: row.person_note?.color_code || 'transparent',
                    color: getTextColor(row.person_note?.color_code)
                })">
                    >

                    <el-table-column prop="id" label="ID" width="70" />
                    <el-table-column prop="name" label="الاسم" />

                    <el-table-column prop="date_of_birth" label="المواليد" width="70" />

                    <el-table-column prop="judgment_operative" label=" منطوق الحكم" />

                    <el-table-column prop="charges" label="التهم" v-if="$inertia.page.props.auth.user.type != 'user'" />
                    <el-table-column prop="judgment_date" label="تاريخ القضية" width="110"
                        v-if="$inertia.page.props.auth.user.type != 'user'" />
                    <el-table-column prop="judgment_date" label="#" v-if="$inertia.page.props.auth.user.type != 'user'">
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
                            editItemForm.job = scope.row.job;
                            editItemForm.address = scope.row.address;
                            editItemForm.charges = scope.row.charges;
                            editItemForm.judgment_date = scope.row.judgment_date;
                            editItemForm.judgment_operative = scope.row.judgment_operative;
                            editItemForm.note = scope.row.note;
                            ">
                                تعديل
                            </button>
                            <button v-if="$inertia.page.props.auth.user.type != 'user'" class="btn btn-sm btn-danger"
                                @click="deltePerson(scope.row.id)">
                                حدف
                            </button>

                        </template>
                    </el-table-column>

                </el-table>

                <!-- Pagination -->
                <div class=" mt-4">
                    <el-pagination v-if="$inertia.page.props.auth.user.type != 'user'" layout="prev, pager, next"
                        :total="people.total" :page-size="people.per_page" :current-page="people.current_page"
                        @current-change="handlePageChange" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
