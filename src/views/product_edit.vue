<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายการสินค้า</h2>

    <!-- 🔹 ปุ่มเพิ่ม + ตัวเลือกจำนวนแถว -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <button class="btn btn-primary" @click="openAddModal">Add+</button>

      <div class="d-flex align-items-center">
        <label class="me-2">แสดงแถวต่อหน้า:</label>
        <select v-model.number="itemsPerPage" class="form-select w-auto">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
        </select>
      </div>
    </div>

    <!-- ✅ ตารางสินค้า -->
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อสินค้า</th>
          <th>รายละเอียด</th>
          <th>ราคา</th>
          <th>จำนวน</th>
          <th>ประเภทสินค้า</th> <!-- แสดงประเภทสินค้า -->
          <th>รูปภาพ</th>
          <th>การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in paginatedProducts" :key="product.product_id">
          <td>{{ product.product_id }}</td>
          <td>{{ product.product_name }}</td>
          <td>{{ product.description }}</td>
          <td>{{ product.price }}</td>
          <td>{{ product.stock }}</td>
          <td>{{ getCategoryName(product.type_id) }}</td> <!-- แสดงประเภทสินค้า -->
          <td>
            <img
              v-if="product.image"
              :src="'http://localhost/MK_SHOP/php_api/uploads/' + product.image"
              width="100"
            />
          </td>
          <td>
            <button class="btn btn-warning btn-sm me-2" @click="openEditModal(product)">
              <i class="bi bi-pencil-square"></i> แก้ไข
            </button>
            <button class="btn btn-danger btn-sm" @click="deleteProduct(product.product_id)">
              <i class="bi bi-trash3"></i> ลบ
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- ✅ ระบบแบ่งหน้า -->
    <nav v-if="totalPages > 1" class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="prevPage">ก่อนหน้า</button>
        </li>

        <li
          class="page-item"
          v-for="page in totalPages"
          :key="page"
          :class="{ active: currentPage === page }"
        >
          <button class="page-link" @click="goToPage(page)">{{ page }}</button>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="nextPage">ถัดไป</button>
        </li>
      </ul>
    </nav>

    <!-- ✅ Modal ใช้ทั้งเพิ่ม / แก้ไข -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? "แก้ไขสินค้า" : "เพิ่มสินค้าใหม่" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveProduct">
              <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input v-model="editForm.product_name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea v-model="editForm.description" class="form-control"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input v-model="editForm.price" type="number" step="0.01" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">จำนวน</label>
                <input v-model="editForm.stock" type="number" class="form-control" required />
              </div>

              <div class="mb-3">
                <label class="form-label">ประเภทสินค้า</label>
                <select v-model="editForm.type_id" class="form-select" required>
                  <option v-for="category in categories" :key="category.type_id" :value="category.type_id">
                    {{ category.type_name }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input
                  type="file"
                  @change="handleFileUpload"
                  class="form-control"
                  :required="!isEditMode"
                />
                <div v-if="isEditMode && editForm.image">
                  <p class="mt-2">รูปเดิม:</p>
                  <img
                    :src="'http://localhost/MK_SHOP/php_api/uploads/' + editForm.image"
                    width="100"
                  />
                </div>
              </div>

              <button type="submit" class="btn btn-success">
                {{ isEditMode ? "บันทึกการแก้ไข" : "บันทึกสินค้าใหม่" }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";

export default {
  name: "ProductList",
  setup() {
    const products = ref([]);
    const categories = ref([]); // สำหรับเก็บประเภทสินค้า
    const loading = ref(true);
    const error = ref(null);
    const isEditMode = ref(false);
    const editForm = ref({
      product_id: null,
      product_name: "",
      description: "",
      price: "",
      stock: "",
      image: "",
      type_id: null // เพิ่ม field สำหรับประเภทสินค้า
    });
    const newImageFile = ref(null);
    let modalInstance = null;

    // ✅ Pagination
    const currentPage = ref(1);
    const itemsPerPage = ref(5); // ค่าเริ่มต้น 5 แถวต่อหน้า

    const totalPages = computed(() =>
      Math.ceil(products.value.length / itemsPerPage.value)
    );

    const paginatedProducts = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      return products.value.slice(start, start + itemsPerPage.value);
    });

    const goToPage = (page) => {
      currentPage.value = page;
    };

    const nextPage = () => {
      if (currentPage.value < totalPages.value) currentPage.value++;
    };

    const prevPage = () => {
      if (currentPage.value > 1) currentPage.value--;
    };

    // 🧩 รีเซ็ตหน้ากลับไปหน้า 1 เมื่อเปลี่ยนจำนวนแถวต่อหน้า
    watch(itemsPerPage, () => {
      currentPage.value = 1;
    });

    // โหลดข้อมูลประเภทสินค้า
    const fetchCategories = async () => {
      try {
        const res = await fetch("http://localhost/MK_SHOP/php_api/product_type.php");
        const data = await res.json();
        categories.value = data.success ? data.data : [];
      } catch (err) {
        error.value = err.message;
      }
    };

    // โหลดข้อมูลสินค้า
    const fetchProducts = async () => {
      try {
        const res = await fetch("http://localhost/MK_SHOP/php_api/api_product.php");
        const data = await res.json();
        products.value = data.success ? data.data : [];
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    // ดึงชื่อประเภทสินค้าจาก type_id
    const getCategoryName = (typeId) => {
      const category = categories.value.find(c => c.type_id === typeId);
      return category ? category.type_name : "ไม่ระบุ";
    };

    // เปิด Modal เพิ่มสินค้า
    const openAddModal = () => {
      isEditMode.value = false;
      editForm.value = {
        product_id: null,
        product_name: "",
        description: "",
        price: "",
        stock: "",
        image: "",
        type_id: null,
      };
      newImageFile.value = null;
      modalInstance.show();
    };

    // เปิด Modal แก้ไขสินค้า
    const openEditModal = (product) => {
      isEditMode.value = true;
      editForm.value = { ...product }; // เติมข้อมูลสินค้าไปที่ form
      newImageFile.value = null;
      modalInstance.show();
    };

    // ฟังก์ชันเก็บข้อมูลสินค้า (เพิ่ม/แก้ไข)
    const saveProduct = async () => {
      const formData = new FormData();
      formData.append("product_name", editForm.value.product_name);
      formData.append("description", editForm.value.description);
      formData.append("price", editForm.value.price);
      formData.append("stock", editForm.value.stock);
      formData.append("type_id", editForm.value.type_id); // ส่ง type_id
      if (newImageFile.value) {
        formData.append("image", newImageFile.value);
      }

      const apiUrl = isEditMode.value
        ? "http://localhost/MK_SHOP/php_api/api_product.php?action=update"
        : "http://localhost/MK_SHOP/php_api/api_product.php?action=add";

      try {
        const res = await fetch(apiUrl, {
          method: "POST",
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          fetchProducts(); // รีเฟรชข้อมูลสินค้า
          modalInstance.hide(); // ปิด Modal
        } else {
          error.value = data.message || "บันทึกข้อมูลไม่สำเร็จ";
        }
      } catch (err) {
        error.value = err.message;
      }
    };

    // ฟังก์ชันลบสินค้า
    const deleteProduct = async (productId) => {
      const confirmDelete = confirm("คุณต้องการลบสินค้านี้หรือไม่?");
      if (!confirmDelete) return;

      try {
        const res = await fetch(`http://localhost/MK_SHOP/php_api/api_product.php?action=delete&product_id=${productId}`, {
          method: "DELETE"
        });
        const data = await res.json();
        if (data.success) {
          fetchProducts(); // รีเฟรชข้อมูลสินค้า
        } else {
          error.value = data.message || "ลบข้อมูลไม่สำเร็จ";
        }
      } catch (err) {
        error.value = err.message;
      }
    };

    // ฟังก์ชันเลือกไฟล์รูปภาพ
    const handleFileUpload = (event) => {
      newImageFile.value = event.target.files[0];
    };

    // โหลดข้อมูลสินค้าและประเภทสินค้าเมื่อเริ่มต้น
    onMounted(() => {
      fetchProducts();
      fetchCategories();
      modalInstance = new bootstrap.Modal(document.getElementById("editModal"));
    });

    return {
      products,
      categories,
      loading,
      error,
      isEditMode,
      editForm,
      newImageFile,
      modalInstance,
      totalPages,
      paginatedProducts,
      currentPage,
      itemsPerPage,
      goToPage,
      nextPage,
      prevPage,
      openAddModal,
      openEditModal,
      saveProduct,
      deleteProduct,
      getCategoryName,
      handleFileUpload
    };
  }
};
</script>

<style scoped>
.table th, .table td {
  text-align: center;
}
</style>
