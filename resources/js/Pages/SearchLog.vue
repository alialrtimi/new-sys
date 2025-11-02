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
const searchFormVisable = ref(false);
// Retrieve data from Inertia props
const props = defineProps({
    people: Object,
    errors: Object,

});

const ShownDate = "";


const jsonResult = "";




function showDialogVisibleFunction(item) {

    ShownDate = item;
}






function celarErrors() {
    Object.keys(props.errors).forEach(key => delete props.errors[key]); // Clears the object
}


//




function formatDate(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-indexed
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    const seconds = String(date.getSeconds()).padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}
// Function to upload the file


const searchForm = new useForm({
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
    inserted_way: "",


    preserveState: true, // Optional: Keeps form state
    replace: true,  // Optional: Replace history state instead of pushing
});

const form = new useForm({
    page: 1,
    partial_case_number: searchForm.partial_case_number || "",
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


});
// Handle pagination
const handlePageChange = (page) => {
    form.page = page;
    form.partial_case_number = searchForm.partial_case_number;
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
    form.get('/search-log', {
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





            <el-dialog v-model="showDialogVisible" title=" # " width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <!-- <p v-if="jsonResult == ''" class="text-warning">
                        لم تتم مطابقة البحث
                    </p> -->
                    <div v-if="jsonResult.total != 0">

                        <p class="text-success"> تمت مطابقة البحث </p>

                        <h1> نتيجة البحث </h1>
                        <h1> عدد النتائج : {{ jsonResult.total }} </h1>
                        <h1> رقم الصفحة : {{ jsonResult.current_page }} </h1>
                        <h1> عدد الملفات التي تم عرضها : {{ jsonResult.data.length }} </h1>
                        <h1> نتيجة البحث </h1>

                    </div>
                    <p v-else class="text-warning">
                        لم تتم المطابقة
                    </p>
                    <!--  -->
                    <div class="col-12 row d-flex flex-wrap mb-4 mx-2 " style="border-bottom: solid;"
                        v-for="jsonDataItem in jsonResult.data">
                        <div class="col-6 column py-2">
                            <label>رقم الملف الجزئي</label>
                            <input disabled v-model="jsonDataItem.partial_case_number" class="form-control " />
                            <label class="text-danger" v-if="errors.partial_case_number">{{ errors.partial_case_number
                                }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>رقم الملف الجنائي</label>
                            <input disabled v-model="jsonDataItem.criminal_case_number" class="form-control " />
                            <label class="text-danger" v-if="errors.criminal_case_number">{{ errors.criminal_case_number
                                }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>الإسم</label>
                            <input disabled v-model="jsonDataItem.name" class="form-control " />
                            <label class="text-danger" v-if="errors.name">{{ errors.name }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>إسم الأم</label>
                            <input disabled v-model="jsonDataItem.mother_name" class="form-control " />
                            <label class="text-danger" v-if="errors.mother_name">{{ errors.mother_name }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>تاريخ الميلاد</label>
                            <input disabled v-model="jsonDataItem.date_of_birth" class="form-control " />
                            <label class="text-danger" v-if="errors.date_of_birth">{{ errors.date_of_birth }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>المهنة</label>
                            <input disabled v-model="jsonDataItem.job" class="form-control " />
                            <label class="text-danger" v-if="errors.job">{{ errors.job }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>العنوان</label>
                            <input disabled v-model="jsonDataItem.address" class="form-control " />
                            <label class="text-danger" v-if="errors.address">{{ errors.address }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>التهم</label>
                            <input disabled v-model="jsonDataItem.charges" class="form-control " />
                            <label class="text-danger" v-if="errors.charges">{{ errors.charges }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>تاريخ القضية</label>
                            <input disabled v-model="jsonDataItem.judgment_date" class="form-control " />
                            <label class="text-danger" v-if="errors.judgment_date">{{ errors.judgment_date }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>منطوق الحكم</label>
                            <input disabled v-model="jsonDataItem.judgment_operative" class="form-control " />
                            <label class="text-danger" v-if="errors.judgment_operative">{{ errors.judgment_operative
                                }}</label>
                        </div>
                        <div class="col-6 column py-2">
                            <label>طريقة الإدخال</label>
                            <input disabled v-model="jsonDataItem.inserted_way" class="form-control " />
                            <label class="text-danger" v-if="errors.inserted_way">{{ errors.inserted_way
                                }}</label>
                        </div>

                        <div class="col-6 column py-2">
                            <label> المدخل </label>
                            <input disabled v-model="ShownDate.user.name" class="form-control " />

                        </div>
                    </div>
                    <!--  -->

                </div>
                <!-- <div class="col-12 row d-flex flex-wrap">
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
                </div> -->
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
                </div>

                <div class="mt-4">

                    <!-- Pagination -->

                    <el-pagination v-if="$inertia.page.props.auth.user.type != 'user'" layout="prev, pager, next"
                        :total="people.total" :page-size="people.per_page" :current-page="people.current_page"
                        @current-change="handlePageChange" />
                </div>

                <!-- Table -->
                <el-table :data="people.data" border style="width: 100%">
                    <!-- <el-table-column prop="id" label="ID" width="50" /> -->
                    <!-- <el-table-column prop="created_at" label="التاريخ والوقت" /> -->
                    <el-table-column prop="created_at" label="التاريخ والوقت">
                        <template #default="{ row }">
                            {{ formatDate(row.created_at) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="user.name" label="المستخدم" />
                    <el-table-column label="حقول البحث" width="600">

                        <template #default="scope">
                            <div class="col-12 d-flex row flex-wrap">
                                <p class="col-6" v-if="scope.row.partial_case_number"> partial_case_number : {{
                                    scope.row.partial_case_number }}</p>
                                <p class="col-6" v-if="scope.row.criminal_case_number"> criminal_case_number : {{
                                    scope.row.criminal_case_number }}</p>
                                <p class="col-6" v-if="scope.row.name"> name : {{ scope.row.name }}</p>
                                <p class="col-6" v-if="scope.row.mother_name"> mother_name : {{ scope.row.mother_name }}
                                </p>
                                <p class="col-6" v-if="scope.row.date_of_birth"> date_of_birth : {{
                                    scope.row.date_of_birth
                                }}</p>
                                <p class="col-6" v-if="scope.row.job"> job : {{ scope.row.job }}</p>
                                <p class="col-6" v-if="scope.row.address"> address : {{ scope.row.address }}</p>
                                <p class="col-6" v-if="scope.row.charges"> charges : {{ scope.row.charges }}</p>
                                <p class="col-6" v-if="scope.row.judgment_date"> judgment_date : {{
                                    scope.row.judgment_date
                                }}</p>
                                <p class="col-6" v-if="scope.row.judgment_operative"> judgment_operative : {{
                                    scope.row.judgment_operative
                                }}</p>
                                <p class="col-6" v-if="scope.row.inserted_way"> inserted_way : {{ scope.row.inserted_way
                                    }}
                                </p>
                            </div>
                        </template>
                    </el-table-column>

                    <!-- <el-table-column prop="date_of_birth" label="المواليد" width="70" /> -->

                    <!-- <el-table-column prop="judgment_operative" label=" منطوق الحكم" /> -->

                    <!-- <el-table-column prop="charges" label="التهم" v-if="$inertia.page.props.auth.user.type != 'user'" /> -->
                    <!-- <el-table-column prop="judgment_date" label="تاريخ القضية" width="110"
                        v-if="$inertia.page.props.auth.user.type != 'user'" /> -->
                    <el-table-column prop="judgment_date" label="#" v-if="$inertia.page.props.auth.user.type != 'user'">
                        <template #default="scope">

                            <button class="btn btn-sm btn-primary "
                                @click="showDialogVisible = true; ShownDate = scope.row, jsonResult = ''; jsonResult = JSON.parse(scope.row.result)">
                                عرض الملف
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
