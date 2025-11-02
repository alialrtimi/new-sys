<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ElTable, ElTableColumn, ElPagination, ElDialog } from 'element-plus';
// import Inertia from '@inertiajs/inertia';
import { ref } from 'vue';
// const form = $inertia;
import { useForm } from '@inertiajs/vue3';
const showDialogVisible = ref(false);
const addDialogVisible = ref(false);

// Retrieve data from Inertia props
const props = defineProps({
    people: Object,
    errors: Object,
});

const ShownDate = "";

const newItemForm = new useForm({
    partial_case_number: "",
    criminal_case_number: "",
    name: "",
    mother_name: "",
    date_of_birth: "",
    job: "",
    address: "",
    charges: "",
    judgment_date: "",
    judgment_operative: "",

});
function showDialogVisibleFunction(item) {

    ShownDate = item;
}
function restorePerson(id) {

    newItemForm.get(`/restore-person/${id}`);
}
function addPerson() {
    try {
        newItemForm.post('/save-person-data')
            .then(response => {
                console.log('Data saved successfully:', response);
                alert("done");
                // يمكن إضافة أي عملية أخرى عند نجاح الطلب
            })
            .catch(error => {
                console.error('An error occurred:', error);
                alert('error');
                // التعامل مع الخطأ هنا
            });
    } catch (error) {
        console.error('Unexpected error:', error);
        // التعامل مع الأخطاء غير المتوقعة
    }
}
const form = new useForm({
    page: 1
});
// Handle pagination
const handlePageChange = (page) => {
    form.page = page;
    form.get('/deleted-files');
};
</script>
<template>
    <AppLayout title="Dashboard">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>
        <div class="py-12 px-4" style="direction: rtl;">

            <!-- 
            <button plain @click="addDialogVisible = true">
                Open the outer Dialog
            </button> -->

            <el-dialog :close-on-click-modal="false" v-model="addDialogVisible" title="نمودج ملف جديد" width="800">
                <div class="col-12 row d-flex flex-wrap">
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
                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button @click="addDialogVisible = false">الغاء</button>
                        <button @click="addPerson" class="btn btn-success">حفظ</button>

                    </div>
                </template>
            </el-dialog>


            <el-dialog v-model="showDialogVisible" title="نمودج ملف جديد" width="800">
                <div class="col-12 row d-flex flex-wrap">
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
                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button @click="showDialogVisible = false">الغاء</button>

                    </div>
                </template>
            </el-dialog>
            <div class="max-w-7xl mx-auto bg-white shadow-sm rounded-lg p-6">
                <div class="mt-4">

                    <!-- Pagination -->
                    <el-pagination layout="prev, pager, next" :total="people.total" :page-size="people.per_page"
                        :current-page="people.current_page" @current-change="handlePageChange" />
                </div>
                <!-- Table -->
                <el-table :data="people.data" border style="width: 100%">
                    <el-table-column prop="id" label="ID" width="50" />
                    <el-table-column prop="name" label="Name" />
                    <el-table-column prop="address" label="Address" />
                    <el-table-column prop="charges" label="Charges" />
                    <el-table-column prop="judgment_date" label="Judgment Date" />
                    <el-table-column prop="judgment_date" label="#">
                        <template #default="scope">

                            <button class="btn btn-sm btn-primary"
                                @click="showDialogVisible = true; ShownDate = scope.row">
                                عرض الملف
                            </button>
                            <button class="btn btn-sm btn-success" @click="restorePerson(scope.row.id)">
                                استرجاع
                            </button>

                        </template>
                    </el-table-column>

                </el-table>
                <!-- Pagination -->
                <div class=" mt-4">
                    <el-pagination layout="prev, pager, next" :total="people.total" :page-size="people.per_page"
                        :current-page="people.current_page" @current-change="handlePageChange" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
