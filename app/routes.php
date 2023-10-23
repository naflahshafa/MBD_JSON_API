<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    // get, get all barang
    $app->get('/barang', function(Request $request, Response $response) {
        // Mendapatkan objek PDO (PHP Data Objects) dari kontainer dependensi (dependency container) yg sdh diatur (impor db dgn PDO (?))
        $db = $this->get(PDO::class);

        // $query berisi query/perintah untuk manipulasi data melalui db
        $query = $db->query('SELECT * FROM barang');
        // $result menjalankan query dan mengambil hasilnya yg dlm bentuk array (associative array) dgn menggunakan metode PDO::FETCH_ASSOC
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        // response berupa hasil data dlm format JSON (hasil data yg sebelumnya berupa array asosiatif dikonversi menjadi format JSON dgn fungsi json_encode).
        $response->getBody()->write(json_encode($results));

        // Mengatur header respons untuk mengindikasikan bahwa respons yang dikirimkan adalah tipe konten JSON (application/json)
        // Mengembalikan response dgn tipe konten JSON (application/json)
        return $response->withHeader("Content-Type", "application/json");
    });

    // get, get barang by id
    $app->get('/barang/{id_barang}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('SELECT * FROM barang WHERE id_barang = ?');
        $query->execute([$args['id_barang']]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    // post, add new barang
    $app->post('/barang', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $id_barang = $parsedBody["id_barang"];
        $jenis_barang = $parsedBody["jenis_barang"];
        $merk_barang = $parsedBody["merk_barang"];
        $harga_perbaikan = $parsedBody ["harga_perbaikan"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('INSERT INTO barang (id_barang, jenis_barang, merk_barang, harga_perbaikan) VALUES (?, ?, ?, ?)');
        $query->execute([$id_barang, $jenis_barang, $merk_barang, $harga_perbaikan]);


        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode(
            [
                'message' => 'Barang telah ditambahkan dengan id ' . $id_barang
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // put, edit harga perbaikan barang
    $app->put('/barang/{id_barang}', function(Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $currentId = $args['id_barang'];
        $harga_perbaikan = $parsedBody ["harga_perbaikan"];
        
        $db = $this->get(PDO::class);

        $query = $db->prepare('UPDATE barang SET harga_perbaikan = ? WHERE id_barang = ?');
        $query->execute([$harga_perbaikan, $currentId]);

        $response->getBody()->write(json_encode(
            [
                'message' => 'Barang dengan id ' . $currentId . ' telah diperbarui dengan harga perbaikan ' . $harga_perbaikan
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // delete, delete barang
    $app->delete('/barang/{id_barang}', function(Request $request, Response $response, $args) {
        $currentId = $args['id_barang'];

        $db = $this->get(PDO::class);

        $query = $db->prepare('DELETE FROM barang WHERE id_barang = ?');
        $query->execute([$currentId]);

        $response->getBody()->write(json_encode(
            [
                'message' => 'Barang dengan id ' . $currentId . ' telah dihapus dari database'
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });


    // PROCEDURE: Barang
    // get, get detail barang
    $app->get('/barangg/{id_barang}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
    
        $barang_id = $args['id_barang'];
        // Memanggil prosedur dengan parameter jika diperlukan
        $query = $db->prepare("CALL detail_item_barang(:barang_id)");
        $query->bindParam(':barang_id', $barang_id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
    
        return $response->withHeader("Content-Type", "application/json");
    });

     // get, get barang berdasarkan merk
    $app->get('/barangg/merk/{merk_barang}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
        
        $barang_merk = $args['merk_barang'];
        $query = $db->prepare("CALL filter_barang_merk(:barang_merk)");
        $query->bindParam(':barang_merk', $barang_merk, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
        
        return $response->withHeader("Content-Type", "application/json");
    });

    // post, add new barang
    $app->post('/barangg', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $new_id_barang = $parsedBody["id_barang"];
        $new_jenis_barang = $parsedBody["jenis_barang"];
        $new_merk_barang = $parsedBody["merk_barang"];
        $new_harga_perbaikan = $parsedBody["harga_perbaikan"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL tambah_barang(:new_id_barang, :new_jenis_barang, :new_merk_barang, :new_harga_perbaikan)');
        $query->bindValue(':new_id_barang', $new_id_barang, PDO::PARAM_INT);
        $query->bindValue(':new_jenis_barang', $new_jenis_barang, PDO::PARAM_STR);
        $query->bindValue(':new_merk_barang', $new_merk_barang, PDO::PARAM_STR);
        $query->bindValue(':new_harga_perbaikan', $new_harga_perbaikan, PDO::PARAM_INT);
        $query->execute();
    
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode(
            [
                'message' => 'Barang telah ditambahkan dengan id ' . $new_id_barang
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // put, update barang
    $app->put('/barangg/{id_barang}', function(Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $barang_id = $args['id_barang'];
        $new_harga_perbaikan = $parsedBody ['harga_perbaikan'];
        
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL ubah_harga_perbaikan_barang(:barang_id, :new_harga_perbaikan)');
        $query->bindValue(':barang_id', $barang_id, PDO::PARAM_INT);
        $query->bindValue(':new_harga_perbaikan', $new_harga_perbaikan, PDO::PARAM_INT);
        $query->execute();

        $response->getBody()->write(json_encode(
            [
                'message' => 'Barang dengan id ' . $barang_id . ' telah diperbarui dengan harga perbaikan ' . $new_harga_perbaikan
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // delete, delete barang from database
    $app->delete('/barangg/{id_barang}', function (Request $request, Response $response, $args) {
        $barang_id = $args['id_barang'];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL hapus_barang(?)');
        $query->bindParam(1, $barang_id, PDO::PARAM_INT);

        $query->execute();

        $response->getBody()->write(json_encode(
            [
                'message' => 'Barang dengan id ' . $barang_id . ' telah dihapus dari database'
            ]
        ));

        return $response->withHeader('Content-Type', 'application/json');
    });


    // PROCEDURE: Pelanggan
    // get, get detail pelanggan
    $app->get('/pelanggan/{id_pelanggan}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
    
        $pelanggan_id = $args['id_pelanggan'];
        $query = $db->prepare("CALL detail_pelanggan(:pelanggan_id)");
        $query->bindParam(':pelanggan_id', $pelanggan_id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    // post, add new pelanggan
    $app->post('/pelanggan', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $new_id_pelanggan = $parsedBody["id_pelanggan"];
        $new_nama_pelanggan = $parsedBody["nama_pelanggan"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL tambah_pelanggan(:new_id_pelanggan, :new_nama_pelanggan)');
        $query->bindValue(':new_id_pelanggan', $new_id_pelanggan, PDO::PARAM_INT);
        $query->bindValue(':new_nama_pelanggan', $new_nama_pelanggan, PDO::PARAM_STR);
        $query->execute();
    
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode(
            ['message' => 'Pelanggan telah ditambahkan dengan id ' . $new_id_pelanggan]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // put, update pelanggan
    $app->put('/pelanggan/{id_pelanggan}', function(Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $pelanggan_id = $args['id_pelanggan'];
        $new_nama_pelanggan = $parsedBody ['nama_pelanggan'];
        
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL ubah_nama_pelanggan(:pelanggan_id, :new_nama_pelanggan)');
        $query->bindValue(':pelanggan_id', $pelanggan_id, PDO::PARAM_INT);
        $query->bindValue(':new_nama_pelanggan', $new_nama_pelanggan, PDO::PARAM_STR);
        $query->execute();

        $response->getBody()->write(json_encode(
            ['message' => 'Pelanggan dengan id ' . $pelanggan_id . ' telah diperbarui dengan nama: ' . $new_nama_pelanggan]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // delete, delete pelanggan from database
    $app->delete('/pelanggan/{id_pelanggan}', function (Request $request, Response $response, $args) {
        $pelanggan_id = $args['id_pelanggan'];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL hapus_pelanggan(?)');
        $query->bindParam(1, $pelanggan_id, PDO::PARAM_INT);

        $query->execute();

        $response->getBody()->write(json_encode(
            ['message' => 'Pelanggan dengan id ' . $pelanggan_id . ' telah dihapus dari database']
        ));

        return $response->withHeader('Content-Type', 'application/json');
    });


    // PROCEDURE: Teknisi
    // get, get detail teknisi
    $app->get('/teknisi/{id_teknisi}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
    
        $teknisi_id = $args['id_teknisi'];
        $query = $db->prepare("CALL detail_teknisi(:teknisi_id)");
        $query->bindParam(':teknisi_id', $teknisi_id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // post, add new teknisi
    $app->post('/teknisi', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $new_id_teknisi = $parsedBody["id_teknisi"];
        $new_nama_teknisi = $parsedBody["nama_teknisi"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL tambah_teknisi(:new_id_teknisi, :new_nama_teknisi)');
        $query->bindValue(':new_id_teknisi', $new_id_teknisi, PDO::PARAM_INT);
        $query->bindValue(':new_nama_teknisi', $new_nama_teknisi, PDO::PARAM_STR);
        $query->execute();
    
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode(
            ['message' => 'Teknisi telah ditambahkan dengan id ' . $new_id_teknisi]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // put, update teknisi
    $app->put('/teknisi/{id_teknisi}', function(Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $teknisi_id = $args['id_teknisi'];
        $new_nama_teknisi = $parsedBody ['nama_teknisi'];
        
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL ubah_nama_teknisi(:teknisi_id, :new_nama_teknisi)');
        $query->bindValue(':teknisi_id', $teknisi_id, PDO::PARAM_INT);
        $query->bindValue(':new_nama_teknisi', $new_nama_teknisi, PDO::PARAM_STR);
        $query->execute();

        $response->getBody()->write(json_encode(
            ['message' => 'Teknisi dengan id ' . $teknisi_id . ' telah diperbarui dengan nama: ' . $new_nama_teknisi]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // delete, delete pelanggan from database
    $app->delete('/teknisi/{id_teknisi}', function (Request $request, Response $response, $args) {
        $teknisi_id = $args['id_teknisi'];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL hapus_teknisi(?)');
        $query->bindParam(1, $teknisi_id, PDO::PARAM_INT);

        $query->execute();

        $response->getBody()->write(json_encode(
            ['message' => 'Teknisi dengan id ' . $teknisi_id . ' telah dihapus dari database']
        ));

        return $response->withHeader('Content-Type', 'application/json');
    });


    // PROCEDURE: Service
    // get, get detail service
    $app->get('/service/{id}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
    
        $service_id = $args['id'];
        $query = $db->prepare("CALL detail_service(:service_id)");
        $query->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // post, add new service
    $app->post('/service', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $new_id = $parsedBody["id"];
        $new_id_pelanggan = $parsedBody["id_pelanggan"];
        $new_id_teknisi = $parsedBody["id_teknisi"];
        $new_id_barang = $parsedBody["id_barang"];
        $new_tgl_service = $parsedBody["tgl_service"];
        $new_lama_perbaikan = $parsedBody["lama_perbaikan"];
        $new_kerusakan = $parsedBody["kerusakan"];
        $new_biaya_tambahan = $parsedBody["biaya_tambahan"];
        $new_totalBiaya_service = $parsedBody["totalBiaya_service"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL tambah_service(:new_id, :new_id_pelanggan, :new_id_teknisi, :new_id_barang, :new_tgl_service, :new_lama_perbaikan, :new_kerusakan, :new_biaya_tambahan, :new_totalBiaya_service)');
        $query->bindValue(':new_id', $new_id, PDO::PARAM_INT);
        $query->bindValue(':new_id_pelanggan', $new_id_pelanggan, PDO::PARAM_INT);
        $query->bindValue(':new_id_teknisi', $new_id_teknisi, PDO::PARAM_INT);
        $query->bindValue(':new_id_barang', $new_id_barang, PDO::PARAM_INT);
        $query->bindValue(':new_tgl_service', $new_tgl_service, PDO::PARAM_STR);
        $query->bindValue(':new_lama_perbaikan', $new_lama_perbaikan, PDO::PARAM_STR);
        $query->bindValue(':new_kerusakan', $new_kerusakan, PDO::PARAM_STR);
        $query->bindValue(':new_biaya_tambahan', $new_biaya_tambahan, PDO::PARAM_INT);
        $query->bindValue(':new_totalBiaya_service', $new_totalBiaya_service, PDO::PARAM_INT);
        $query->execute();
    
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode(
            ['message' => 'Service telah ditambahkan dengan id ' . $new_id]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // put, update service
    $app->put('/service/{id}', function(Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $service_id = $args['id'];
        $new_ket_kerusakan = $parsedBody ['kerusakan'];
        
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL ubah_ket_kerusakan(:service_id, :new_ket_kerusakan)');
        $query->bindValue(':service_id', $service_id, PDO::PARAM_INT);
        $query->bindValue(':new_ket_kerusakan', $new_ket_kerusakan, PDO::PARAM_STR);
        $query->execute();

        $response->getBody()->write(json_encode(
            ['message' => 'Service dengan id ' . $service_id . ' telah diperbarui dengan keterangan: ' . $new_ket_kerusakan]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // delete, delete service from database
    $app->delete('/service/{id}', function (Request $request, Response $response, $args) {
        $service_id = $args['id'];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL hapus_service(?)');
        $query->bindParam(1, $service_id, PDO::PARAM_INT);

        $query->execute();

        $response->getBody()->write(json_encode(
            ['message' => 'Service dengan id ' . $service_id . ' telah dihapus dari database']
        ));

        return $response->withHeader('Content-Type', 'application/json');
    });
  
};
