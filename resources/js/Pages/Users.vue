<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ElTable, ElTableColumn, ElPagination, ElDialog, ElMessage, ElMessageBox } from 'element-plus';
// import Inertia from '@inertiajs/inertia';
import { ref, watch } from 'vue';
// const form = $inertia;
import { useForm } from '@inertiajs/vue3';

const showDialogVisible = ref(false);
const showPasswordDialogVisible = ref(false);

const addDialogVisible = ref(false);

// Retrieve data from Inertia props
const props = defineProps({
    users: Object,
    errors: Object,
    search: Object,
});

const UserTypeForm = new useForm({
    id: "",
    type: ""

});


const changePasswordForm = new useForm({
    id: "",
    password: "",
    password_confirmation: ""

});
const ShownDate = "";

function formatDate(date) {
    if (!date) return 'N/A';
    const d = new Date(date);
    return d.toISOString().slice(0, 19).replace('T', ' ');
}



function changeUserType(type, id) {
    // Show a confirmation message box
    ElMessageBox.confirm(
        `هل أنت متأكد أنك تريد تغيير نوع المستخدم  ؟`,
        'تأكيد تغيير النوع',
        {
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            type: 'warning',
        }
    )
        .then(() => {
            // If confirmed, send the request
            UserTypeForm.id = id;
            UserTypeForm.type = type;
            UserTypeForm.post('change-user-type',

                {
                    onSuccess: () => {

                        console.log('Data saved successfully.');

                        // document.getElementById('addDialogVisibleButton').click();
                        ElMessage({
                            message: 'تم التغيير بنجاح',
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
        })
        .catch(() => {
            // If canceled, show an info message
            ElMessage({
                message: 'تم إلغاء العملية',
                type: 'info',
                center: true,
            });
        });
}




function changePassword() {
    ElMessageBox.confirm(
        `هل أنت متأكد أنك تريد تغيير كلمة مرور المستخدم   ؟`,
        'تأكيد تغيير ',
        {
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            type: 'warning',
        }
    )
        .then(() => {
            // If confirmed, send the request

            changePasswordForm.post('change-user-password',

                {
                    onSuccess: () => {

                        console.log('Data saved successfully.');

                        // document.getElementById('addDialogVisibleButton').click();
                        ElMessage({
                            message: 'تم التغيير بنجاح',
                            type: 'success',
                            center: true, // يجعل الرسالة في المنتصف
                        });

                        document.getElementById('passwordDialogVisibleButton').click();
                    },
                    onError: (errors) => {
                        console.error('Validation errors:', errors);
                        // alert('Failed to save data. Please check the form and try again.');
                    },
                    onFinish: () => {
                        console.log('Request finished.');
                    }
                });
        })
        .catch(() => {
            // If canceled, show an info message
            ElMessage({
                message: 'تم إلغاء العملية',
                type: 'info',
                center: true,
            });
        });

}



function celarErrors() {
    Object.keys(props.errors).forEach(key => delete props.errors[key]); // Clears the object
}

const newItemForm = new useForm({
    name: "",
    email: "",
    password: "",
    type: "",
    password_confirmation: "",


});


function showDialogVisibleFunction(item) {

    ShownDate = item;
}


function showPasswordDialogVisibleFunction(item) {

    ShownDate = item;
}




function activeUser(id) {
    // alert(id);
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
            newItemForm.delete(`/active-user/${id}`, {
                onSuccess: () => {

                    console.log('Data saved successfully.');

                    // document.getElementById('addDialogVisibleButton').click();
                    ElMessage({
                        message: 'تم التفعيل بنجاح',
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
function delteUser(id) {
    // alert(id);
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
            newItemForm.delete(`/delete-user/${id}`, {
                onSuccess: () => {

                    console.log('Data saved successfully.');

                    // document.getElementById('addDialogVisibleButton').click();
                    ElMessage({
                        message: 'تم  الغاء التفعيل',
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
function addUser() {

    newItemForm.post('/save-user-data', {
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









</script>
<template>
    <AppLayout title="Dashboard">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>
        <div class="py-12 px-4" style="direction: rtl;">


            <div class="col-12 p-0 d-flex justify-content-end">
                <button plain @click="addDialogVisible = true; celarErrors(); newItemForm.reset()"
                    class="btn btn-sm btn-success ">
                    + جديد
                </button>
            </div>
            <el-dialog :close-on-click-modal="false" v-model="addDialogVisible" title="حساب جديد" width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <div class="col-6 column py-2">
                        <label> الإسم</label>
                        <input v-model="newItemForm.name" class="form-control " placeholder="الإسم" />
                        <label class="text-danger" v-if="errors.name">{{ errors.name
                            }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label> البريد الإلكتروني</label>
                        <input v-model="newItemForm.email" class="form-control " placeholder="البريد الإلكتروني ..." />
                        <label class="text-danger" v-if="errors.email">{{ errors.email
                            }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>كلمة المرور</label>
                        <input v-model="newItemForm.password" class="form-control " placeholder="كلمة المرور..." />
                        <label class="text-danger" v-if="errors.password">{{ errors.password }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label> تأكيد كلمة المرور</label>
                        <input v-model="newItemForm.password_confirmation" class="form-control "
                            placeholder=" تأكيد كلمة المرور..." />
                        <label class="text-danger" v-if="errors.password_confirmation">{{ errors.password_confirmation
                            }}</label>
                    </div>


                    <div class="col-6 column py-2">
                        <label> نوع المستخدم</label>
                        <select v-model="newItemForm.type" class="form-control " placeholder=" نوع المستخدم  ...">
                            <option value="user">مستخدم</option>
                            <option value="admin">مشرف</option>
                            <option value="super_admin">مسؤول</option>
                        </select>

                        <label class="text-danger" v-if="errors.type">{{ errors.type
                            }}</label>
                    </div>


                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button id="addDialogVisibleButton" @click="addDialogVisible = false;">الغاء</button>
                        <button @click="addUser" class="btn btn-success">حفظ</button>

                    </div>
                </template>
            </el-dialog>








            <el-dialog v-model="showPasswordDialogVisible" title="نمودج  تغيير كلمة السر" width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <div class="col-6 column py-2">
                        <label>كلمة المرور </label>
                        <input v-model="changePasswordForm.password" class="form-control " />
                        <label class="text-danger" v-if="errors.password">{{ errors.password
                            }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label> تأكيد كلمة المرور</label>
                        <input v-model="changePasswordForm.password_confirmation" class="form-control " />
                        <label class="text-danger" v-if="errors.password_confirmation">{{ errors.password_confirmation
                            }}</label>
                    </div>





                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button id="passwordDialogVisibleButton"
                            @click="showPasswordDialogVisible = false">الغاء</button>
                        <button @click="changePassword">تغيير</button>

                    </div>
                </template>
            </el-dialog>

            <el-dialog v-model="showDialogVisible" title="نمودج ملف جديد" width="800">
                <div class="col-12 row d-flex flex-wrap">
                    <div class="col-6 column py-2">
                        <label>اسم المستخدم </label>
                        <input disabled v-model="ShownDate.name" class="form-control " />
                        <label class="text-danger" v-if="errors.name">{{ errors.name
                            }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label> البريد الإلكتروني</label>
                        <input disabled v-model="ShownDate.email" class="form-control " />
                        <label class="text-danger" v-if="errors.email">{{ errors.email
                            }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label>تاريخ التسجيل </label>
                        <p disabled class="form-control " style="background: var(--bs-secondary-bg)">

                            {{ formatDate(ShownDate.created_at) }}
                        </p>
                        <label class="text-danger" v-if="errors.created_at">{{ errors.created_at }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label> نوع المستخدم</label>
                        <input disabled v-model="ShownDate.type" class="form-control " />
                        <label class="text-danger" v-if="errors.type">{{ errors.type }}</label>
                    </div>
                    <div class="col-6 column py-2">
                        <label> حالة الحساب</label>
                        <p disabled v-if="ShownDate.email_verified_at" class="form-control text-success "
                            style="background: var(--bs-secondary-bg)">
                            مفعل
                        </p>
                        <p disabled v-else class="form-control text-danger " style="background: var(--bs-secondary-bg)">
                            غير مفعل

                        </p>
                    </div>


                </div>
                <template #footer>
                    <div class="dialog-footer d-flex justify-content-between">
                        <button @click="showDialogVisible = false">الغاء</button>

                    </div>
                </template>
            </el-dialog>
            <div class="max-w-7xl mx-auto bg-white shadow-sm rounded-lg p-6">





                <!-- Table -->
                <el-table :data="users" border style="width: 100%">
                    <el-table-column prop="id" label="ID" width="50" />
                    <el-table-column prop="name" label="Name" />
                    <el-table-column prop="email" label="Address" /><el-table-column label="Created At">
                        <template #default="scope">
                            {{ formatDate(scope.row.created_at) }}
                        </template>
                    </el-table-column>

                    <el-table-column prop="email" label="الصلاحية" /><el-table-column label="Created At">
                        <template #default="scope">
                            <select @change="changeUserType(scope.row.type, scope.row.id)" class="form-control"
                                v-model="scope.row.type">

                                <option value="user">user</option>
                                <option value="admin">admin</option>
                                <option value="super_admin">super_admin</option>

                            </select>
                        </template>
                    </el-table-column>

                    <el-table-column prop="created_at" label="created_at">
                        <template #default="scope">

                            <button class="btn btn-sm btn-primary"
                                @click="showDialogVisible = true; ShownDate = scope.row">
                                عرض
                            </button>

                            <button class="btn btn-sm btn-primary"
                                @click="showPasswordDialogVisible = true; changePasswordForm.id = scope.row.id">
                                تغيير كلمة المرور
                            </button>
                            <button v-if="scope.row.email_verified_at" class="btn btn-sm btn-danger"
                                @click="delteUser(scope.row.id)">
                                إلغاء تفعيل
                            </button>
                            <button v-else class="btn btn-sm btn-success" @click="activeUser(scope.row.id)">
                                تفعيل
                            </button>


                        </template>
                    </el-table-column>

                </el-table>

                <!-- Pagination -->

            </div>
        </div>
    </AppLayout>
</template>
