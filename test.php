<?php
// /test_rajaongkir.php

// API Key RajaOngkir yang Anda berikan
$api_key = 'qPnHRGDgfe3df6ae71c9ab29Zqoseq4N';
$api_url = 'https://rajaongkir.komerce.id/api/v1/destination/province';

echo "<h3>Menguji Koneksi ke API RajaOngkir...</h3>";
echo "URL: " . $api_url . "<br>";
echo "API Key: " . $api_key . "<br><hr>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "key: " . $api_key
    ]);

    // Matikan verifikasi SSL untuk menghindari masalah sertifikat pada server lokal
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $output = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    echo "<h4>Hasil Uji:</h4>";

    if ($curl_error) {
        echo "<p style='color: red;'><strong>❌ GAGAL! Terjadi kesalahan cURL:</strong> " . htmlspecialchars($curl_error) . "</p>";
        } else {
            echo "<p style='color: green;'><strong>✅ SUKSES! Kode Status HTTP:</strong> " . $http_code . "</p>";

                $data = json_decode($output, true);

                    if ($data) {
                            echo "<h4>Respons JSON dari API:</h4>";
                                    if ($data['status']['code'] == 200) {
                                                echo "<p style='color: green;'><strong>Status API:</strong> " . $data['status']['description'] . "</p>";
                                                            echo "<p><strong>Jumlah Provinsi Ditemukan:</strong> " . count($data['data']) . "</p>";
                                                                        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>"; // Tampilkan sebagian kecil data
                                                                                } else {
                                                                                            echo "<p style='color: red;'><strong>Status API:</strong> " . htmlspecialchars($data['status']['description']) . "</p>";
                                                                                                        echo "<pre>" . htmlspecialchars($output) . "</pre>";
                                                                                                                }
                                                                                                                    } else {
                                                                                                                            echo "<p style='color: red;'>Gagal menguraikan respons JSON. Respons tidak valid.</p>";
                                                                                                                                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                                                                                                                                        }
                                                                                                                                        }
                                                                                                                                        ?>