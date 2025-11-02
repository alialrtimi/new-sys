<template>
    <AppLayout title="إدارة الملاحظات">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة الملاحظات</h2>
        </template>

        <div>
            <h1>إدارة الملاحظات</h1>

            <!-- عرض القائمة -->
            <el-table :data="notes" border>
                <el-table-column prop="name" label="اسم الملاحظة"></el-table-column>
                <el-table-column prop="color_code" label="رمز اللون">
                    <template v-slot="scope">
                        <div :style="{ backgroundColor: scope.row.color_code }" class="color-box"></div>
                    </template>
                </el-table-column>
                <el-table-column label="العمليات">
                    <template v-slot="scope">
                        <el-button @click="editNote(scope.row)" size="small" type="primary">تعديل</el-button>
                        <!-- <el-button @click="deleteNote(scope.row.id)" size="small" type="danger">حذف</el-button> -->
                    </template>
                </el-table-column>
            </el-table>

            <!-- نموذج إضافة/تعديل ملاحظة -->
            <el-dialog v-model="dialogVisible" title="إضافة/تعديل ملاحظة">
                <el-form :model="form" label-width="120px">
                    <el-form-item label="اسم الملاحظة"
                        :rules="[{ required: true, message: 'الاسم مطلوب', trigger: 'blur' }]">
                        <el-input v-model="form.name" placeholder="أدخل اسم الملاحظة"></el-input>
                    </el-form-item>

                    <el-form-item label="اللون" :rules="[{ required: true, message: 'اللون مطلوب', trigger: 'blur' }]">
                        <!-- استخدام ElColorPicker لاختيار اللون -->
                        <el-color-picker class="w-100" v-model="form.color_code" />
                    </el-form-item>
                </el-form>

                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible = false">إلغاء</el-button>
                    <el-button type="primary" @click="submitForm">حفظ</el-button>
                </span>
            </el-dialog>

            <!-- زر إضافة ملاحظة جديدة -->
            <el-button type="success" @click="openDialog" style="margin-top: 20px;">إضافة ملاحظة جديدة</el-button>
        </div>
    </AppLayout>
</template>

<script>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElTable, ElTableColumn, ElButton, ElDialog, ElForm, ElFormItem, ElInput, ElColorPicker, ElMessage } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
    props: {
        notes: Array, // هذه هي الملاحظات المرسلة من الخادم
    },

    components: {
        AppLayout,
        ElColorPicker, // تأكد من أنك قد أضفت ElColorPicker هنا
    },

    setup(props) {
        const dialogVisible = ref(false); // حالة إظهار/إخفاء نافذة الحوار
        const form = useForm({
            name: '',
            color_code: '', // هنا يتم تخزين اللون
        });
        const currentNoteId = ref(null); // معرف الملاحظة الحالية للتعديل

        // فتح نافذة الحوار لإضافة ملاحظة جديدة
        const openDialog = () => {
            form.reset(); // إعادة تعيين النموذج
            currentNoteId.value = null;
            dialogVisible.value = true; // تأكد من أن هذه القيمة تكون true لفتح الـ Dialog
        };

        // إعداد النموذج للتعديل
        const editNote = (note) => {
            form.name = note.name;
            form.color_code = note.color_code;
            currentNoteId.value = note.id;
            dialogVisible.value = true; // فتح الـ Dialog أثناء التعديل
        };

        // إرسال النموذج لإضافة أو تعديل الملاحظة
        const submitForm = () => {
            if (currentNoteId.value) {
                // تعديل الملاحظة
                form.put(`/notes/${currentNoteId.value}`, {
                    onSuccess: () => {
                        dialogVisible.value = false;
                        ElMessage({
                            message: 'تم التعديل بنجاح',
                            type: 'success',
                        });
                    },
                    onError: (errors) => {
                        ElMessage({
                            message: 'حدث خطأ أثناء التعديل',
                            type: 'error',
                        });
                    },
                });
            } else {
                // إضافة ملاحظة جديدة
                form.post('/notes', {
                    onSuccess: () => {
                        dialogVisible.value = false;
                        ElMessage({
                            message: 'تمت إضافة الملاحظة بنجاح',
                            type: 'success',
                        });
                    },
                    onError: (errors) => {
                        ElMessage({
                            message: 'حدث خطأ أثناء الإضافة',
                            type: 'error',
                        });
                    },
                });
            }
        };

        // حذف الملاحظة
        const deleteNote = (id) => {
            if (confirm('هل أنت متأكد أنك تريد حذف هذه الملاحظة؟')) {
                form.delete(`/notes/${id}`, {
                    onSuccess: () => {
                        // تحديث الملاحظات بعد الحذف
                        ElMessage({
                            message: 'تم الحذف بنجاح',
                            type: 'success',
                        });
                    },
                    onError: (errors) => {
                        ElMessage({
                            message: 'حدث خطأ أثناء الحذف',
                            type: 'error',
                        });
                    },
                });
            }
        };

        return {
            dialogVisible,
            form,
            openDialog,
            editNote,
            submitForm,
            deleteNote,
        };
    },
};
</script>

<style scoped>
.color-box {
    width: 30px;
    height: 30px;
    margin: 0 auto;
}
</style>
