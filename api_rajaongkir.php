<?php
// /api_rajaongkir.php (Versi Final dengan Endpoint Komerce & Kecamatan)
header('Content-Type: application/json');
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
        echo json_encode(['error' => 'Akses ditolak']);
            exit();
            }

            $request_type = $_GET['request'] ?? '';
            $api_key = 'qPnHRGDgfe3df6ae71c9ab29Zqoseq4N'; // API Key Anda
            $api_base_url = "https://rajaongkir.komerce.id/api/v1/";

            $url = "";
            $post_fields = [];
            $request_method = "GET";

            switch ($request_type) {
                case 'provinsi':
                        $url = $api_base_url . "destination/province";
                                break;
                                    case 'kota':
                                            $provinsi_id = $_GET['provinsi'] ?? '';
                                                    if (empty($provinsi_id)) { exit(json_encode(['data' => []])); }
                                                            $url = $api_base_url . "destination/city?province_id=" . $provinsi_id;
                                                                    break;
                                                                        case 'kecamatan':
                                                                                $kota_id = $_GET['kota'] ?? '';
                                                                                        if (empty($kota_id)) { exit(json_encode(['data' => []])); }
                                                                                                $url = $api_base_url . "destination/subdistrict?city_id=" . $kota_id;
                                                                                                        break;
                                                                                                            case 'ongkir':
                                                                                                                    $request_method = "POST";
                                                                                                                            $url = $api_base_url . "cost";
                                                                                                                                    $post_fields = [
                                                                                                                                                'origin' => $_POST['origin'] ?? '',
                                                                                                                                                            'destination' => $_POST['destination'] ?? '',
                                                                                                                                                                        'weight' => $_POST['weight'] ?? '',
                                                                                                                                                                                    'courier' => $_POST['courier'] ?? ''
                                                                                                                                                                                            ];
                                                                                                                                                                                                    if (in_array('', $post_fields, true)) { exit(json_encode(['error' => 'Parameter tidak lengkap'])); }
                                                                                                                                                                                                            break;
                                                                                                                                                                                                                default:
                                                                                                                                                                                                                        exit(json_encode(['error' => 'Request tidak valid']));
                                                                                                                                                                                                                        }

                                                                                                                                                                                                                        $curl = curl_init();
                                                                                                                                                                                                                        $curl_options = [
                                                                                                                                                                                                                          CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $request_method,
                                                                                                                                                                                                                            CURLOPT_HTTPHEADER => ["key: " . $api_key],
                                                                                                                                                                                                                            ];
                                                                                                                                                                                                                            if ($request_method === "POST") {
                                                                                                                                                                                                                                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($post_fields);
                                                                                                                                                                                                                                    $curl_options[CURLOPT_HTTPHEADER][] = "content-type: application/x-www-form-urlencoded";
                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                    curl_setopt_array($curl, $curl_options);
                                                                                                                                                                                                                                    $response = curl_exec($curl);
                                                                                                                                                                                                                                    $err = curl_error($curl);
                                                                                                                                                                                                                                    curl_close($curl);

                                                                                                                                                                                                                                    echo $err ? json_encode(['error' => 'cURL Error: ' . $err]) : $response;