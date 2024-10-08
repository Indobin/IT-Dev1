<?php 
session_start();

class ProductsController extends Controller{
 
 public function index()
 {
  $data = [
   'judul' => 'Products',
   'prd' => $this->model('Products_Model')->getAllProducts(),
  ];
  $this->view('template/header', $data);
  $this->view('product/index', $data);
  $this->view('template/footer');
 }
 public function form($id = null) {
    $data = [
        'judul' => $id ? 'Products Edit' : 'Products Add',
        'form' => $id ? 'Edit Product' : 'Add Product',
        'method' => $id ? 'PUT' : 'POST',
    ];
    if ($id) {
        // var_dump($id);
        $data['product'] = $this->model('Products_Model')->getProductById($id);
        
    }
    $this->view('template/header', $data);
    $this->view('product/form', $data);
    $this->view('template/footer');
}
public function details($id){
    $data = [
        'judul' => 'Detail Product',
        'prd' => $this->model('Products_Model')->getProductById($id),
        ];
        $this->view('template/header', $data);
        $this->view('product/details', $data);
        $this->view('template/footer');
}
public function save() {
    $id = $_POST['id'] ?? null;
    $img = $_FILES['img'];

    $namaFile = $img['name'];
    $tmpName = $img['tmp_name'];
    $error = $img['error'];
    $size = $img['size'];

    if ($error === 4) {
        $namaFileBaru = $_POST['existing_img'] ?? null; // Gunakan gambar lama jika tidak ada gambar baru
    } else {
        if ($size > 2000000) {
            Messege::setFlash('Upload failed', 'Image size', 'too large', 'error');
            header('Location:' . BASEURL . '/products/form');
            exit;
        }

        $extensiFileValid = ['jpg', 'jpeg', 'png'];
        $extensiFile = explode('.', $namaFile);
        $extensiFile = strtolower(end($extensiFile));

        if (!in_array($extensiFile, $extensiFileValid)) {
            Messege::setFlash('Upload Failed', 'File extension', 'invalid', 'error');
            header('Location:' . BASEURL . '/products');
            exit;
        }

        $namaFileBaru = uniqid() . '.' . $extensiFile;
        move_uploaded_file($tmpName, 'img/products/' . $namaFileBaru);

        if ($id) {
            // Hapus gambar lama jika ada
            $existingProduct = $this->model('Products_Model')->getProductById($id);
            if ($existingProduct['img'] && file_exists('img/products/' . $existingProduct['img'])) {
                unlink('img/products/' . $existingProduct['img']);
            }
        }
    }

    if ($id) {
        // Update produk
        if ($this->model('Products_Model')->updateProduct($id, $_POST, $namaFileBaru) > 0) {
            Messege::setFlash('Product Data', 'successful', 'updated', 'success');
        } else {
            Messege::setFlash('Product Data', 'failed', 'updated', 'error');
        }
    } else {
        // Tambah produk
        if ($this->model('Products_Model')->tambahProducts($_POST, $namaFileBaru) > 0) {
            Messege::setFlash('Product Data', 'successful', 'added', 'success');
        } else {
            Messege::setFlash('Product Data', 'failed', 'added', 'error');
        }
    }

    header('Location:' . BASEURL . '/products');
    exit;
}
public function delete($id){
    
    if ($this->model('Products_Model')->deleteProducts($id)>0) {
        Messege::setFlash('Product Data', 'successful', 'deleted', 'success');
        header('Location:' . BASEURL . '/products');

        // var_dump($_SESSION);
        exit;
    }else {
        Messege::setFlash('Product Data', 'failed', 'deleted', 'error');
        header('Location:' . BASEURL . '/products');
        exit;
    }
}
}