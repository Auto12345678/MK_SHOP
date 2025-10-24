<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายการสินค้า</h2>

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

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อสินค้า</th>
          <th>รายละเอียด</th>
          <th>ราคา</th>
          <th>จำนวน</th>
          <th>ประเภทสินค้า</th>
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
          <td>{{ product.type_name || "ไม่ระบุ" }}</td>
          <td>
            <img
              v-if="product.image"
              :src="'http://localhost/MK_SHOP/php_api/uploads/' + product.image"
              width="100"
            />
          </td>
          <td>
            <button
              class="btn btn-warning btn-sm me-2"
              @click="openEditModal(product)"
            >
              <i class="bi bi-pencil-square"></i> แก้ไข
            </button>
            <button
              class="btn btn-danger btn-sm"
              @click="deleteProduct(product.product_id)"
            >
              <i class="bi bi-trash3"></i> ลบ
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

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

    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditMode ? "แก้ไขสินค้า" : "เพิ่มสินค้าใหม่" }}
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveProduct">
              <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input
                  v-model="editForm.product_name"
                  type="text"
                  class="form-control"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea
                  v-model="editForm.description"
                  class="form-control"
                ></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input
                  v-model="editForm.price"
                  type="number"
                  step="0.01"
                  class="form-control"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">จำนวน</label>
                <input
                  v-model="editForm.stock"
                  type="number"
                  class="form-control"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">ประเภทสินค้า</label>
                <select v-model="editForm.type_id" class="form-select" required>
                  <option
                    v-for="category in categories"
                    :key="category.type_id"
                    :value="category.type_id"
                  >
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
                    :src="
                      'http://localhost/MK_SHOP/php_api/uploads/' +
                      editForm.image
                    "
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
// ❗️ ถ้าคุณใช้ ES modules (เช่นใน Vite) ต้อง import Modal
// import { Modal } from "bootstrap";

export default {
  name: "ProductList",
  setup() {
    const products = ref([]);
    const categories = ref([]);
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
      type_id: null,
    });
    const newImageFile = ref(null);
    let modalInstance = null;

    // --- (ส่วน Pagination ถูกต้องแล้ว) ---
    const currentPage = ref(1);
    const itemsPerPage = ref(5);
    const totalPages = computed(() =>
      Math.ceil(products.value.length / itemsPerPage.value)
    );
    const paginatedProducts = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      return products.value.slice(start, start + itemsPerPage.value);
    });
    const goToPage = (page) => { currentPage.value = page; };
    const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
    const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
    watch(itemsPerPage, () => { currentPage.value = 1; });

    // --- (ส่วน API Calls ถูกต้องแล้ว) ---
    const fetchCategories = async () => {
      try {
        const res = await fetch("http://localhost/MK_SHOP/php_api/product_type.php");
        const data = await res.json();
        categories.value = data.success ? data.data : [];
      } catch (err) {
        error.value = "ไม่สามารถโหลดประเภทสินค้า: " + err.message;
      }
    };
    const fetchProducts = async () => {
      try {
        const res = await fetch("http://localhost/MK_SHOP/php_api/api_product.php");
        const data = await res.json();
        products.value = data.success ? data.data : [];
        error.value = null;
      } catch (err) {
        error.value = "ไม่สามารถโหลดสินค้า: " + err.message;
      } finally {
        loading.value = false;
      }
    };

    // --- (ส่วน Modal & Form Handling ถูกต้องแล้ว) ---
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
      // รีเซ็ตฟอร์ม (สำคัญสำหรับ file input)
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = "";
      modalInstance.show();
    };
    const openEditModal = (product) => {
      isEditMode.value = true;
      editForm.value = { ...product };
      newImageFile.value = null;
      // รีเซ็ตฟอร์ม (สำคัญสำหรับ file input)
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = "";
      modalInstance.show();
    };
    const handleFileUpload = (event) => {
      newImageFile.value = event.target.files[0];
    };


    // 📌 [โค้ดฉบับ Debug] ฟังก์ชันเก็บข้อมูลสินค้า (เพิ่ม/แก้ไข)
    const saveProduct = async () => {
      const formData = new FormData();
      const action = isEditMode.value ? "update" : "add";
      
      formData.append("action", action);
      if (isEditMode.value) {
        formData.append("product_id", editForm.value.product_id);
      }
      formData.append("product_name", editForm.value.product_name);
      formData.append("description", editForm.value.description);
      formData.append("price", editForm.value.price);
      formData.append("stock", editForm.value.stock);
      formData.append("type_id", editForm.value.type_id);
      if (newImageFile.value) {
        formData.append("image", newImageFile.value);
      }

      const apiUrl = "http://localhost/MK_SHOP/php_api/api_product.php";
      error.value = null; // เคลียร์ Error เก่า

      try {
        const res = await fetch(apiUrl, {
          method: "POST",
          body: formData,
        });

        // 1. อ่านสิ่งที่ Server ส่งกลับมาเป็น "Text" ก่อน
        const responseText = await res.text();

        try {
          // 2. ลองแปลง Text นั้นเป็น JSON
          const data = JSON.parse(responseText);

          // 3. ถ้าแปลงสำเร็จ (แสดงว่าเป็น JSON ที่ถูกต้อง)
          if (data.message) {
            fetchProducts();
            modalInstance.hide();
          } else {
            // เป็น JSON แต่เป็น Error ที่ PHP ส่งมา
            error.value = data.error || "บันทึกข้อมูลไม่สำเร็จ (Error from PHP)";
          }

        } catch (jsonError) {
          // 4. [สำคัญ!] ถ้าแปลงเป็น JSON ไม่สำเร็จ (แสดงว่ามันคือ HTML Error)
          console.error("JSON Parse Error:", jsonError);
          console.error("Raw Server Response (HTML):", responseText);
          // 5. แสดง HTML Error นั้นใน Alert ให้อ่านง่ายขึ้น
          const cleanError = responseText.replace(/<[^>]*>?/gm, ' ').trim(); // ลบแท็ก HTML ออก
          error.value = `Server Error: ${cleanError}`; // นี่คือ Error ที่แท้จริงจาก PHP
        }

      } catch (err) {
        // Network Error (เช่น fetch ล้มเหลว)
        error.value = `Network Error: ${err.message}`;
      }
    };


    // 📌 [โค้ดฉบับ Debug] ฟังก์ชันลบสินค้า
    const deleteProduct = async (productId) => {
      const confirmDelete = confirm("คุณต้องการลบสินค้านี้หรือไม่?");
      if (!confirmDelete) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("product_id", productId);
      error.value = null; // เคลียร์ Error เก่า

      try {
        const res = await fetch(
          `http://localhost/MK_SHOP/php_api/api_product.php`,
          {
            method: "POST",
            body: formData,
          }
        );

        // 1. อ่านเป็น Text
        const responseText = await res.text();
        try {
          // 2. ลองแปลงเป็น JSON
          const data = JSON.parse(responseText);

          // 3. ถ้าสำเร็จ
          if (data.message) {
            fetchProducts();
          } else {
            error.value = data.error || "ลบข้อมูลไม่สำเร็จ";
          }
        } catch (jsonError) {
          // 4. ถ้าเป็น HTML Error
          console.error("Raw Server Response (HTML):", responseText);
          const cleanError = responseText.replace(/<[^>]*>?/gm, ' ').trim();
          error.value = `Server Error: ${cleanError}`;
        }
        
      } catch (err) {
        error.value = `Network Error: ${err.message}`;
      }
    };

    // --- Lifecycle ---
    onMounted(() => {
      fetchProducts();
      fetchCategories();
      // ❗️ ตรวจสอบว่า bootstrap ถูกโหลดในโปรเจกต์ของคุณ
      // ถ้าใช้ผ่าน <script> ใน index.html โค้ดนี้ถูกต้อง
      modalInstance = new bootstrap.Modal(document.getElementById("editModal"));
      
      // ถ้า import แบบ ES Module ให้ใช้:
      // modalInstance = new Modal(document.getElementById("editModal"));
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
      handleFileUpload,
    };
  },
};
</script>

<style scoped>
.table th,
.table td {
  text-align: center;
  vertical-align: middle; /* จัดให้อยู่กึ่งกลางแนวตั้ง */
}
img {
  max-width: 100px;
  height: auto;
  border-radius: 5px; /* เพิ่มความสวยงาม */
}
</style>