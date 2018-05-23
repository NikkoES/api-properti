<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

function getConnect(){
    require_once 'include/dbHandler.php';
    $db = new dbHandler();
    return $db;
}

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

//login
$app->post("/login/", function (Request $request, Response $response){

    $login = $request->getParsedBody();

    $sql = "SELECT * FROM tb_user WHERE email=:email AND password=:password";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":email" => $login["email"],
        ":password" => $login["password"]
    ];
    $stmt->execute($data);
    $result = $stmt->fetchAll();

    if($result)
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//register
$app->post("/register/", function (Request $request, Response $response){

    $profil = $request->getParsedBody();

    $sql = "INSERT INTO tb_user (id_user, nama_user, no_hp, email, password, img_profile) VALUE (:id_user, :nama_user, :no_hp, :email, :password, :img_profile)";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":id_user" => $profil["id_user"],
        ":nama_user" => $profil["nama_user"],
        ":no_hp" => $profil["no_hp"],
        ":email" => $profil["email"],
        ":password" => $profil["password"],
        ":img_profile" => $profil["img_profile"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menampilkan profile
$app->get("/profile/{email}", function (Request $request, Response $response, $args){
    $email = $args["email"];
    $sql = "SELECT * FROM tb_user WHERE email=:email";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":email" => $email]);
    $result = $stmt->fetch();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//mengedit profil
$app->put("/profile/{id_user}", function (Request $request, Response $response, $args){
    $id_user = $args["id_user"];
    $profil = $request->getParsedBody();
    $sql = "UPDATE tb_user SET nama_user=:nama_user, no_hp=:no_hp, email=:email, password=:password, img_profile=:img_profile WHERE id_user=:id_user";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_user" => $id_user,
        ":nama_user" => $profil["nama_user"],
        ":no_hp" => $profil["no_hp"],
        ":email" => $profil["email"],
        ":password" => $profil["password"],
        ":img_profile" => $profil["img_profile"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//upload foto profil
$app->post('/profile/image/{id_user}', function(Request $request, Response $response, $args) {
    
    $uploadedFiles = $request->getUploadedFiles();
    
    // handle single input with single file upload
    $uploadedFile = $uploadedFiles['cover'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
        // ubah nama file dengan id buku
        $filename = sprintf('%s.%0.8s', $args["id_user"], $extension);
        
        $directory = $this->get('settings')['upload_directory'];
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        // simpan nama file ke database
        $sql = "UPDATE tb_user SET img_profile=:img_profile WHERE id_user=:id_user";
        $stmt = $this->db->prepare($sql);
        $params = [
            ":id_user" => $args["id_user"],
            ":img_profile" => $filename
        ];
        
        if($stmt->execute($params)){
            // ambil base url dan gabungkan dengan file name untuk membentuk URL file
            $url = $request->getUri()->getBaseUrl()."/uploads/".$filename;
            return $response->withJson(["status" => "success", "data" => $url], 200);
        }
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    }
});


//-------------------- CRUD CICILAN -------------------------

//menampilkan semua data cicilan
$app->get("/cicilan/{id_user}", function (Request $request, Response $response, $args){
    $id_user = $args["id_user"];
    $sql = "SELECT * FROM tb_cicilan WHERE id_user=:id_user";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id_user" => $id_user]);
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//menampilkan data cicilan berdasarkan id
$app->get("/cicilan/{id_user}/{id_cicilan}", function (Request $request, Response $response, $args){
    $id_user = $args["id_user"];
    $id_cicilan = $args["id_cicilan"];
    $sql = "SELECT * FROM tb_cicilan WHERE id_user=:id_user AND id_cicilan=:id_cicilan";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id_user" => $id_user, ":id_cicilan" => $id_cicilan]);
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//menambahkan data cicilan
$app->post("/cicilan/", function (Request $request, Response $response){

    $cicilan = $request->getParsedBody();

    $sql = "INSERT INTO tb_cicilan (id_cicilan, keterangan, tanggal, pinjaman_kpr, bunga_per_tahun, tenor_lama_pinjaman, tenor_bunga_fix, sisa_pokok_pinjaman, bunga_floating_per_tahun, cicilan, cicilan_setelah_floating, id_user) VALUE (:id_cicilan, :keterangan, NOW(), :pinjaman_kpr, :bunga_per_tahun, :tenor_lama_pinjaman, :tenor_bunga_fix, :sisa_pokok_pinjaman, :bunga_floating_per_tahun, :cicilan, :cicilan_setelah_floating, :id_user)";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":id_cicilan" => $cicilan["id_cicilan"],
        ":keterangan" => $cicilan["keterangan"],
        ":pinjaman_kpr" => $cicilan["pinjaman_kpr"],
        ":bunga_per_tahun" => $cicilan["bunga_per_tahun"],
        ":tenor_lama_pinjaman" => $cicilan["tenor_lama_pinjaman"],
        ":tenor_bunga_fix" => $cicilan["tenor_bunga_fix"],
        ":sisa_pokok_pinjaman" => $cicilan["sisa_pokok_pinjaman"],
        ":bunga_floating_per_tahun" => $cicilan["bunga_floating_per_tahun"],
        ":cicilan" => $cicilan["cicilan"],
        ":cicilan_setelah_floating" => $cicilan["cicilan_setelah_floating"],
        ":id_user" => $cicilan["id_user"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data cicilan
$app->put("/cicilan/{id_cicilan}", function (Request $request, Response $response, $args){
    $id_cicilan = $args["id_cicilan"];
    $cicilan = $request->getParsedBody();
    $sql = "UPDATE tb_cicilan SET keterangan=:keterangan, tanggal=NOW(), pinjaman_kpr=:pinjaman_kpr, bunga_per_tahun=:bunga_per_tahun, tenor_lama_pinjaman=:tenor_lama_pinjaman, tenor_bunga_fix=:tenor_bunga_fix, sisa_pokok_pinjaman=:sisa_pokok_pinjaman, bunga_floating_per_tahun=:bunga_floating_per_tahun, cicilan=:cicilan, cicilan_setelah_floating=:cicilan_setelah_floating WHERE id_cicilan=:id_cicilan";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cicilan" => $id_cicilan,
        ":keterangan" => $cicilan["keterangan"],
        ":pinjaman_kpr" => $cicilan["pinjaman_kpr"],
        ":bunga_per_tahun" => $cicilan["bunga_per_tahun"],
        ":tenor_lama_pinjaman" => $cicilan["tenor_lama_pinjaman"],
        ":tenor_bunga_fix" => $cicilan["tenor_bunga_fix"],
        ":sisa_pokok_pinjaman" => $cicilan["sisa_pokok_pinjaman"],
        ":bunga_floating_per_tahun" => $cicilan["bunga_floating_per_tahun"],
        ":cicilan" => $cicilan["cicilan"],
        ":cicilan_setelah_floating" => $cicilan["cicilan_setelah_floating"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menghapus data cicilan
$app->delete("/cicilan/{id_cicilan}", function (Request $request, Response $response, $args){
    $id_cicilan = $args["id_cicilan"];
    $sql = "DELETE FROM tb_cicilan WHERE id_cicilan=:id_cicilan";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cicilan" => $id_cicilan
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


//-------------------- END CRUD CICILAN -------------------------


//-------------------- CRUD NILAI PASAR -------------------------

//menampilkan semua data nilai pasar
$app->get("/nilaipasar/{id_user}", function (Request $request, Response $response, $args){
    $id_user = $args["id_user"];
    $sql = "SELECT * FROM tb_nilai_pasar WHERE id_user=:id_user";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id_user" => $id_user]);
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//menampilkan data nilai pasar berdasarkan id
$app->get("/nilaipasar/{id_user}/{id_nilai_pasar}", function (Request $request, Response $response, $args){
    $id_user = $args["id_user"];
    $id_nilai_pasar = $args["id_nilai_pasar"];

    $sql = "SELECT * FROM tb_nilai_pasar WHERE id_user=:id_user AND id_nilai_pasar=:id_nilai_pasar";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id_user" => $id_user, ":id_nilai_pasar" => $id_nilai_pasar]);
    $result = $stmt->fetch();

    $sql2 = "SELECT * FROM tb_value_nilai_pasar WHERE id_nilai_pasar=:id_nilai_pasar ORDER BY id_properti ASC";
    $stmt2 = $this->db->prepare($sql2);
    $stmt2->execute([":id_nilai_pasar" => $id_nilai_pasar]);
    $result2 = $stmt2->fetchAll();

    return $response->withJson(["status" => "success", "data" => $result, "value_properti" => $result2], 200);
});

//menambahkan data nilai pasar
$app->post("/nilaipasar/data/", function (Request $request, Response $response){

    $nilaipasar = $request->getParsedBody();

    $sql = "INSERT INTO tb_nilai_pasar (id_nilai_pasar, keterangan, tanggal, harga_pasaran_per_meter, perbandingan_properti, catatan_kondisi_bangunan, catatan_survey_lokasi, id_user) VALUE (:id_nilai_pasar, :keterangan, NOW(), :harga_pasaran_per_meter, :perbandingan_properti, :catatan_kondisi_bangunan, :catatan_survey_lokasi, :id_user)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_nilai_pasar" => $nilaipasar["id_nilai_pasar"],
        ":keterangan" => $nilaipasar["keterangan"],
        ":harga_pasaran_per_meter" => $nilaipasar["harga_pasaran_per_meter"],
        ":perbandingan_properti" => $nilaipasar["perbandingan_properti"],
        ":catatan_kondisi_bangunan" => $nilaipasar["catatan_kondisi_bangunan"],
        ":catatan_survey_lokasi" => $nilaipasar["catatan_survey_lokasi"],
        ":id_user" => $nilaipasar["id_user"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data nilai pasar
$app->put("/nilaipasar/data/{id_nilai_pasar}", function (Request $request, Response $response, $args){
    $id_nilai_pasar = $args["id_nilai_pasar"];
    $nilaipasar = $request->getParsedBody();
    $sql = "UPDATE tb_nilai_pasar SET keterangan=:keterangan, tanggal=NOW(), harga_pasaran_per_meter=:harga_pasaran_per_meter, perbandingan_properti=:perbandingan_properti, catatan_kondisi_bangunan=:catatan_kondisi_bangunan, catatan_survey_lokasi=:catatan_survey_lokasi, id_user=:id_user WHERE id_nilai_pasar=:id_nilai_pasar";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_nilai_pasar" => $id_nilai_pasar,
        ":keterangan" => $nilaipasar["keterangan"],
        ":harga_pasaran_per_meter" => $nilaipasar["harga_pasaran_per_meter"],
        ":perbandingan_properti" => $nilaipasar["perbandingan_properti"],
        ":catatan_kondisi_bangunan" => $nilaipasar["catatan_kondisi_bangunan"],
        ":catatan_survey_lokasi" => $nilaipasar["catatan_survey_lokasi"],
        ":id_user" => $nilaipasar["id_user"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menghapus data nilai pasar
$app->delete("/nilaipasar/data/{id_nilai_pasar}", function (Request $request, Response $response, $args){
    $id_nilai_pasar = $args["id_nilai_pasar"];
    $sql = "DELETE FROM tb_nilai_pasar WHERE id_nilai_pasar=:id_nilai_pasar";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_nilai_pasar" => $id_nilai_pasar
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menambahkan data properti nilai pasar
$app->post("/nilaipasar/properti/", function (Request $request, Response $response){

    $nilaipasar = $request->getParsedBody();

    $sql = "INSERT INTO tb_value_nilai_pasar (id_nilai_pasar, id_properti, harga_jual_properti, luas_tanah, luas_bangunan, usia_bangunan, harga_rata_per_meter, harga_bangunan_baru, harga_bangunan_saat_ini, harga_tanah_per_meter) VALUE (:id_nilai_pasar, :id_properti, :harga_jual_properti, :luas_tanah, :luas_bangunan, :usia_bangunan, :harga_rata_per_meter, :harga_bangunan_baru, :harga_bangunan_saat_ini, :harga_tanah_per_meter)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_nilai_pasar" => $nilaipasar["id_nilai_pasar"],
        ":id_properti" => $nilaipasar["id_properti"],
        ":harga_jual_properti" => $nilaipasar["harga_jual_properti"],
        ":luas_tanah" => $nilaipasar["luas_tanah"],
        ":luas_bangunan" => $nilaipasar["luas_bangunan"],
        ":usia_bangunan" => $nilaipasar["usia_bangunan"],
        ":harga_rata_per_meter" => $nilaipasar["harga_rata_per_meter"],
        ":harga_bangunan_baru" => $nilaipasar["harga_bangunan_baru"],
        ":harga_bangunan_saat_ini" => $nilaipasar["harga_bangunan_saat_ini"],
        ":harga_tanah_per_meter" => $nilaipasar["harga_tanah_per_meter"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data properti nilai pasar
$app->put("/nilaipasar/properti/{id_nilai_pasar}/{id_properti}", function (Request $request, Response $response, $args){
    $id_nilai_pasar = $args["id_nilai_pasar"];
    $id_properti = $args["id_properti"];
    $nilaipasar = $request->getParsedBody();
    $sql = "UPDATE tb_value_nilai_pasar SET harga_jual_properti=:harga_jual_properti, luas_tanah=:luas_tanah, luas_bangunan=:luas_bangunan, usia_bangunan=:usia_bangunan, harga_rata_per_meter=:harga_rata_per_meter, harga_bangunan_baru=:harga_bangunan_baru, harga_bangunan_saat_ini=:harga_bangunan_saat_ini, harga_tanah_per_meter=:harga_tanah_per_meter WHERE id_nilai_pasar=:id_nilai_pasar AND id_properti=:id_properti";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_nilai_pasar" => $id_nilai_pasar,
        ":id_properti" => $id_properti,
        ":harga_jual_properti" => $nilaipasar["harga_jual_properti"],
        ":luas_tanah" => $nilaipasar["luas_tanah"],
        ":luas_bangunan" => $nilaipasar["luas_bangunan"],
        ":usia_bangunan" => $nilaipasar["usia_bangunan"],
        ":harga_rata_per_meter" => $nilaipasar["harga_rata_per_meter"],
        ":harga_bangunan_baru" => $nilaipasar["harga_bangunan_baru"],
        ":harga_bangunan_saat_ini" => $nilaipasar["harga_bangunan_saat_ini"],
        ":harga_tanah_per_meter" => $nilaipasar["harga_tanah_per_meter"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menghapus data properti nilai pasar
$app->delete("/nilaipasar/properti/{id_nilai_pasar}", function (Request $request, Response $response, $args){
    $id_nilai_pasar = $args["id_nilai_pasar"];
    $sql = "DELETE FROM tb_value_nilai_pasar WHERE id_nilai_pasar=:id_nilai_pasar";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_nilai_pasar" => $id_nilai_pasar
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


//-------------------- END CRUD NILAI PASAR -------------------------


//-------------------- CRUD CASH FLOW -------------------------

//menampilkan semua data cash flow
$app->get("/cashflow/{id_user}", function (Request $request, Response $response, $args){
    $id_user = $args["id_user"];
    $sql = "SELECT * FROM tb_cash_flow WHERE id_user=:id_user";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id_user" => $id_user]);
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//menampilkan data cash flow berdasarkan id
$app->get("/cashflow/data/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];

    $sql = "SELECT * FROM tb_cash_flow WHERE id_user=:id_user AND id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id_user" => $id_user, ":id_cash_flow" => $id_cash_flow]);
    $result = $stmt->fetch();

    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//menampilkan kamar cash flow berdasarkan id
$app->get("/cashflow/kamar/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];

    $sql2 = "SELECT * FROM tb_kamar WHERE id_cash_flow=:id_cash_flow";
    $stmt2 = $this->db->prepare($sql2);
    $stmt2->execute([":id_cash_flow" => $id_cash_flow]);
    $result2 = $stmt2->fetchAll();

    return $response->withJson(["status" => "success", "kamar" => $result2], 200);
});

//menampilkan pemasukan cash flow berdasarkan id
$app->get("/cashflow/pemasukan/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];

    $sql3 = "SELECT * FROM tb_pemasukan WHERE id_cash_flow=:id_cash_flow";
    $stmt3 = $this->db->prepare($sql3);
    $stmt3->execute([":id_cash_flow" => $id_cash_flow]);
    $result3 = $stmt3->fetchAll();

    return $response->withJson(["status" => "success", "pemasukan" => $result3], 200);
});

//menampilkan pengeluaran cash flow berdasarkan id
$app->get("/cashflow/pengeluaran/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];

    $sql4 = "SELECT * FROM tb_pengeluaran WHERE id_cash_flow=:id_cash_flow";
    $stmt4 = $this->db->prepare($sql4);
    $stmt4->execute([":id_cash_flow" => $id_cash_flow]);
    $result4 = $stmt4->fetchAll();

    return $response->withJson(["status" => "success", "pengeluaran" => $result4], 200);
});

//menampilkan fasilitas cash flow berdasarkan id
$app->get("/cashflow/fasilitas/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];

    $sql5 = "SELECT * FROM tb_fasilitas WHERE id_cash_flow=:id_cash_flow";
    $stmt5 = $this->db->prepare($sql5);
    $stmt5->execute([":id_cash_flow" => $id_cash_flow]);
    $result5 = $stmt5->fetchAll();

    return $response->withJson(["status" => "success", "fasilitas" => $result5], 200);
});

//menampilkan extras cash flow berdasarkan id
$app->get("/cashflow/extras/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];

    $sql6 = "SELECT * FROM tb_extras_cash_flow WHERE id_cash_flow=:id_cash_flow";
    $stmt6 = $this->db->prepare($sql6);
    $stmt6->execute([":id_cash_flow" => $id_cash_flow]);
    $result6 = $stmt6->fetchAll();

    return $response->withJson(["status" => "success", "extras" => $result6], 200);
});

//menambahkan data cash flow
$app->post("/cashflow/data/", function (Request $request, Response $response){

    $cashflow = $request->getParsedBody();

    $sql = "INSERT INTO tb_cash_flow (id_cash_flow, keterangan, tanggal, id_user) VALUE (:id_cash_flow, :keterangan, NOW(), :id_user)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_cash_flow" => $cashflow["id_cash_flow"],
        ":keterangan" => $cashflow["keterangan"],
        ":id_user" => $cashflow["id_user"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menambahkan data kamar
$app->post("/cashflow/kamar/", function (Request $request, Response $response){

    $cashflow = $request->getParsedBody();

    $sql = "INSERT INTO tb_kamar (id_cash_flow, tipe_kamar, jumlah_kamar, harga_kamar) VALUE (:id_cash_flow, :tipe_kamar, :jumlah_kamar, :harga_kamar)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_cash_flow" => $cashflow["id_cash_flow"],
        ":tipe_kamar" => $cashflow["tipe_kamar"],
        ":jumlah_kamar" => $cashflow["jumlah_kamar"],
        ":harga_kamar" => $cashflow["harga_kamar"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menambahkan data pemasukan
$app->post("/cashflow/pemasukan/", function (Request $request, Response $response){

    $cashflow = $request->getParsedBody();

    $sql = "INSERT INTO tb_pemasukan (id_cash_flow, pemasukan, jumlah_pemasukan) VALUE (:id_cash_flow, :pemasukan, :jumlah_pemasukan)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_cash_flow" => $cashflow["id_cash_flow"],
        ":pemasukan" => $cashflow["pemasukan"],
        ":jumlah_pemasukan" => $cashflow["jumlah_pemasukan"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menambahkan data pengeluaran
$app->post("/cashflow/pengeluaran/", function (Request $request, Response $response){

    $cashflow = $request->getParsedBody();

    $sql = "INSERT INTO tb_pengeluaran (id_cash_flow, pengeluaran, jumlah_pengeluaran) VALUE (:id_cash_flow, :pengeluaran, :jumlah_pengeluaran)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_cash_flow" => $cashflow["id_cash_flow"],
        ":pengeluaran" => $cashflow["pengeluaran"],
        ":jumlah_pengeluaran" => $cashflow["jumlah_pengeluaran"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menambahkan data fasilitas
$app->post("/cashflow/fasilitas/", function (Request $request, Response $response){

    $cashflow = $request->getParsedBody();

    $sql = "INSERT INTO tb_fasilitas (id_cash_flow, nama_fasilitas, kenaikan_harga, jumlah_kamar) VALUE (:id_cash_flow, :nama_fasilitas, :kenaikan_harga, :jumlah_kamar)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_cash_flow" => $cashflow["id_cash_flow"],
        ":nama_fasilitas" => $cashflow["nama_fasilitas"],
        ":kenaikan_harga" => $cashflow["kenaikan_harga"],
        ":jumlah_kamar" => $cashflow["jumlah_kamar"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menambahkan extras fasilitas
$app->post("/cashflow/extras/", function (Request $request, Response $response){

    $cashflow = $request->getParsedBody();

    $sql = "INSERT INTO tb_extras_cash_flow (id_cash_flow, occupancy_rate, total_penghasilan, total_pemasukan, total_pengeluaran, net_operating_income, net_operating_income_future) VALUE (:id_cash_flow, :occupancy_rate, :total_penghasilan, :total_pemasukan, :total_pengeluaran, :net_operating_income, :net_operating_income_future)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":id_cash_flow" => $cashflow["id_cash_flow"],
        ":occupancy_rate" => $cashflow["occupancy_rate"],
        ":total_penghasilan" => $cashflow["total_penghasilan"],
        ":total_pemasukan" => $cashflow["total_pemasukan"],
        ":total_pengeluaran" => $cashflow["total_pengeluaran"],
        ":net_operating_income" => $cashflow["net_operating_income"],
        ":net_operating_income_future" => $cashflow["net_operating_income_future"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data cash flow
$app->put("/cashflow/data/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $cashflow = $request->getParsedBody();
    $sql = "UPDATE tb_cash_flow SET id_cash_flow=:id_cash_flow, keterangan=:keterangan, tanggal=NOW(), id_user=:id_user WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow,
        ":keterangan" => $cashflow["keterangan"],
        ":id_user" => $cashflow["id_user"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data kamar
$app->put("/cashflow/kamar/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $cashflow = $request->getParsedBody();
    $sql = "UPDATE tb_kamar SET id_cash_flow=:id_cash_flow, tipe_kamar=:tipe_kamar, jumlah_kamar=:jumlah_kamar, harga_kamar=:harga_kamar WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow,
        ":tipe_kamar" => $cashflow["tipe_kamar"],
        ":jumlah_kamar" => $cashflow["jumlah_kamar"],
        ":harga_kamar" => $cashflow["harga_kamar"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data pemasukan
$app->put("/cashflow/pemasukan/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $cashflow = $request->getParsedBody();
    $sql = "UPDATE tb_pemasukan SET id_cash_flow=:id_cash_flow, pemasukan=:pemasukan, jumlah_pemasukan=:jumlah_pemasukan WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow,
        ":pemasukan" => $cashflow["pemasukan"],
        ":jumlah_pemasukan" => $cashflow["jumlah_pemasukan"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data pengeluaran
$app->put("/cashflow/pengeluaran/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $cashflow = $request->getParsedBody();
    $sql = "UPDATE tb_pengeluaran SET id_cash_flow=:id_cash_flow, pengeluaran=:pengeluaran, jumlah_pengeluaran=:jumlah_pengeluaran WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow,
        ":pengeluaran" => $cashflow["pengeluaran"],
        ":jumlah_pengeluaran" => $cashflow["jumlah_pengeluaran"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit data fasilitas
$app->put("/cashflow/fasilitas/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $cashflow = $request->getParsedBody();
    $sql = "UPDATE tb_fasilitas SET id_cash_flow=:id_cash_flow, nama_fasilitas=:nama_fasilitas, kenaikan_harga=:kenaikan_harga, jumlah_kamar=:jumlah_kamar WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow,
        ":nama_fasilitas" => $cashflow["nama_fasilitas"],
        ":kenaikan_harga" => $cashflow["kenaikan_harga"],
        ":jumlah_kamar" => $cashflow["jumlah_kamar"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//mengedit extras fasilitas
$app->put("/cashflow/extras/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $cashflow = $request->getParsedBody();
    $sql = "UPDATE tb_extras_cash_flow SET id_cash_flow=:id_cash_flow, occupancy_rate=:occupancy_rate, total_penghasilan=:total_penghasilan, total_pemasukan=:total_pemasukan, total_pengeluaran=:total_pengeluaran, net_operating_income=:net_operating_income, net_operating_income_future=:net_operating_income_future WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow,
        ":occupancy_rate" => $cashflow["occupancy_rate"],
        ":total_penghasilan" => $cashflow["total_penghasilan"],
        ":total_pemasukan" => $cashflow["total_pemasukan"],
        ":total_pengeluaran" => $cashflow["total_pengeluaran"],
        ":net_operating_income" => $cashflow["net_operating_income"],
        ":net_operating_income_future" => $cashflow["net_operating_income_future"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//menghapus data cash flow
$app->delete("/cashflow/data/{id_cash_flow}", function (Request $request, Response $response, $args){
    $id_cash_flow = $args["id_cash_flow"];
    $sql = "DELETE FROM tb_cash_flow WHERE id_cash_flow=:id_cash_flow";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id_cash_flow" => $id_cash_flow
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


//-------------------- END CRUD CASH FLOW -------------------------